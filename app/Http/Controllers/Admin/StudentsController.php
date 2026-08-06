<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StudentRequest;
use App\Mail\AccountStatusMail;
use App\Models\Student;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Guardian;
use App\Notifications\StudentFeeDuesNotification;
use App\Notifications\StudentForceLogoutNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\Facades\DataTables;

class StudentsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'students';
    }

    public function getIndex()
    {
        $branches = Branch::where('status', 1)->get();
        $regions = Region::where('status', 1)->get();

        return view('admin.students.view', self::$data + compact('branches', 'regions'));
    }

    public function getList(Request $request)
    {
        $query = Student::with(['branch', 'registrations.subject'])->withCount('registrations');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('is_child')) {
            $query->where('is_child', $request->is_child);
        }
        if ($request->filled('name')) {
            $search = $request->name;
            $query->where(function($q) use ($search) {
                $q->where('full_name_ar', 'like', "%{$search}%")
                  ->orWhere('full_name_en', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        $datatable = DataTables::of($query);

        $datatable->addColumn('name', function ($row) {
            return app()->getLocale() == 'ar' ? $row->full_name_ar : $row->full_name_en;
        });

        $datatable->addColumn('branch', function ($row) {
            return $row->branch ? (app()->getLocale() == 'ar' ? $row->branch->name_ar : $row->branch->name_en) : '-';
        });

        $datatable->editColumn('status', function ($row) {
            return view('admin.students.parts.status', ['student' => $row])->render();
        });

        $datatable->editColumn('registrations_count', function ($row) {
            $count = $row->registrations_count;
            if ($count == 0) return '<span class="badge badge-light-danger">0 تسجيلات</span>';
            
            $subjects = $row->registrations->map(function($reg) {
                return '<span class="badge badge-light-primary mb-1">'.($reg->subject->name ?? '-').'</span>';
            })->implode(' ');

            return '<div class="d-flex flex-column"><span class="fw-bold fs-6 mb-1">' . $count . ' مادة/مجموعة</span><div class="d-flex flex-wrap gap-1">' . $subjects . '</div></div>';
        });

        $datatable->addColumn('actions', function ($row) {
            return view('admin.students.parts.actions', ['student' => $row])->render();
        });

        $datatable->rawColumns(['status', 'actions', 'registrations_count']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function getAdd()
    {
        $branches = Branch::where('status', 1)->get();
        $regions = Region::where('status', 1)->get();
        $guardians = Guardian::where('is_active', 1)->get();

        return view('admin.students.add', self::$data + [
            'info' => null,
            'branches' => $branches,
            'regions' => $regions,
            'guardians' => $guardians,
        ]);
    }

    public function postAdd(StudentRequest $request)
    {
        $data = $request->safe()->except(['image', 'password']) + [
            'password' => Hash::make($request->password),
            'status' => $request->boolean('status', true)
        ];

        $data['image'] = $request->get('image');

        $student = Student::create($data);

        return redirect()->route('students.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $student = Student::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('students.view')->with('danger', __('app.not_found'));
        }

        $branches = Branch::where('status', 1)->get();
        $regions = Region::where('status', 1)->get();
        $guardians = Guardian::where('is_active', 1)->get();

        return view('admin.students.add', self::$data + [
            'info' => $student,
            'branches' => $branches,
            'regions' => $regions,
            'guardians' => $guardians,
        ]);
    }

    public function postEdit(StudentRequest $request, $id)
    {
        try {
            $student = Student::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('students.view')->with('danger', __('app.not_found'));
        }

        $data = $request->safe()->except(['image', 'password']) + [
            'status' => $request->boolean('status', true)
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $data['image'] = $request->filled('image') ? $request->get('image') : $student->image;

        $student->update($data);

        return redirect()->route('students.view')->with('success', __('app.update_success'));
    }

    public function getView(Request $request, $id)
    {
        try {
            $student = Student::with(['branch', 'guardian', 'registrations.subject', 'payments'])
                ->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('students.view')->with('danger', __('app.not_found'));
        }

        return view('admin.students.show', self::$data + ['student' => $student]);
    }

    public function postStatus(Request $request)
    {
        try {
            $student = Student::with('registrations')->findOrFail(Crypt::decrypt($request->id));
            $wasVerified = $student->isEmailVerified();
            $newStatus = ! $student->status;
            $student->update(['status' => $newStatus]);

            // Only notify for verified accounts being suspended/reactivated for fees —
            // a freshly-registered, never-verified account toggling status isn't a fees event.
            if ($wasVerified && $student->email) {
                $totalDue = $student->total_due;
                Mail::to($student->email)->send(new AccountStatusMail($student, ! $newStatus, $totalDue));

                $message = __($newStatus ? 'app.notification_account_reactivated' : 'app.notification_account_suspended', [
                    'amount' => number_format($totalDue, 2),
                ]);
                Notification::send($student, new StudentFeeDuesNotification($student->id, $message));
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $student = Student::findOrFail(Crypt::decrypt($request->id));

            if ($student->registrations()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $student->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function exportExcel()
    {
        $students = Student::with('branch')->get();
        $fileName = 'students_' . date('Y_m_d_H_i_s') . '.xls';
        
        $headers = array(
            "Content-type"        => "application/vnd.ms-excel; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        
        $callback = function() use($students) {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"></head>';
            echo '<body dir="rtl">';
            echo '<table border="1" style="font-family: \'Segoe UI\', Tahoma, Arial, sans-serif; text-align: center; vertical-align: middle; border-collapse: collapse;">';
            echo '<thead>';
            echo '<tr style="height: 45px;">';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 100px; text-align: center; vertical-align: middle;">المسلسل</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 350px; text-align: center; vertical-align: middle;">اسم الطالب كامل</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 200px; text-align: center; vertical-align: middle;">رقم الجوال</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 250px; text-align: center; vertical-align: middle;">الايميل</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 250px; text-align: center; vertical-align: middle;">اسم الفرع</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            $i = 1;
            foreach ($students as $student) {
                echo '<tr style="height: 35px;">';
                echo '<td style="font-size: 13px; text-align: center; vertical-align: middle;">' . $i++ . '</td>';
                echo '<td style="font-size: 13px; text-align: center; vertical-align: middle;">' . $student->full_name_ar . '</td>';
                // Force phone number to be text by styling
                echo '<td style="font-size: 13px; text-align: center; vertical-align: middle; mso-number-format:\'@\';">' . $student->phone . '</td>';
                echo '<td style="font-size: 13px; text-align: center; vertical-align: middle;">' . $student->email . '</td>';
                $branchName = $student->branch ? $student->branch->name_ar : '';
                echo '<td style="font-size: 13px; text-align: center; vertical-align: middle;">' . $branchName . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function getInvoices($id)
    {
        $student = Student::with(['registrations.subject', 'registrations.group'])->findOrFail(Crypt::decrypt($id));
        return view('admin.students.parts.invoices_modal', compact('student'));
    }

    public function getResults($id)
    {
        $student = Student::findOrFail(Crypt::decrypt($id));
        
        $grades = \App\Models\Grade::with(['exam', 'group.subject'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.students.results', self::$data + compact('student', 'grades'));
    }

    /**
     * Live/periodic panel of students who currently have their account open
     * (a heartbeat within the last 5 minutes — see EnforceStudentDeviceLock)
     * plus the device each one is locked to (a persistent per-browser cookie,
     * not the IP — see StudentDeviceLock). locked_ip is shown only as extra
     * info (last known network), it no longer gates login.
     */
    public function getActiveDevices()
    {
        // This page bakes the admin's current CSRF token into the HTML; if a
        // host-level page cache (e.g. LiteSpeed cache, common on shared
        // hosting) ever served that cached copy to a different admin/session,
        // every write on it would fail with a CSRF mismatch. no-store keeps
        // this admin-only, session-specific page out of any such cache.
        return response()
            ->view('admin.students.active_devices', array_merge(self::$data, ['active_menu' => 'students_active_devices']))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    }

    public function getActiveDevicesList(Request $request)
    {
        $query = Student::whereNotNull('last_seen_at')
            ->orderByDesc('last_seen_at');

        if ($request->boolean('online_only', true)) {
            $query->where('last_seen_at', '>=', now()->subMinutes(5));
        }

        $students = $query->get(['id', 'full_name_ar', 'full_name_en', 'email', 'image', 'locked_ip', 'locked_device_id', 'locked_device_id_set_at', 'last_seen_at', 'max_devices']);

        $data = $students->map(function ($student) {
            $deviceCount = count($student->locked_device_ids);

            return [
                'id' => Crypt::encrypt($student->id),
                'name' => $student->full_name_ar ?: $student->full_name_en,
                'email' => $student->email,
                'image' => $student->image ? asset('storage/' . $student->image) : asset('assets/admin/media/avatars/blank.png'),
                'locked_ip' => $student->locked_ip,
                'is_locked' => $deviceCount > 0,
                'device_count' => $deviceCount,
                'max_devices' => $student->max_devices,
                'locked_device_id_set_at' => $student->locked_device_id_set_at?->format('Y-m-d H:i'),
                'last_seen_at' => $student->last_seen_at?->diffForHumans(),
                'is_online' => $student->is_online,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * "Delete device" — clears the device lock so the student's next login is
     * accepted from whatever device they use (a new phone, browser, etc.),
     * and force-signs-out any session still open on the old device.
     */
    public function postClearIp(Request $request)
    {
        $id = $request->input('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('app.execution_error')]);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => __('app.not_found')]);
        }

        $student->update([
            'locked_device_id' => null,
            'locked_device_id_set_at' => null,
            'force_logout_after' => now(),
        ]);

        // Instant kick if their tab is open and connected (push); the
        // force_logout_after timestamp above is the guaranteed fallback that
        // catches it on their very next request either way.
        Notification::send($student, new StudentForceLogoutNotification($student->id));

        return response()->json(['success' => true, 'message' => 'تم حذف الجهاز المرتبط بالحساب، يمكن للطالب الآن الدخول من جهاز جديد.']);
    }

    /**
     * Raises/lowers how many distinct devices a student may keep locked at
     * once (e.g. 2 for "phone + laptop"). Lowering it below the number of
     * devices already locked does NOT kick any of them out retroactively —
     * it only blocks new devices beyond the existing ones until the admin
     * also clears the device list.
     */
    public function postUpdateMaxDevices(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'max_devices' => 'required|integer|min:1|max:5',
        ]);

        try {
            $id = Crypt::decrypt($request->input('id'));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('app.execution_error')]);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => __('app.not_found')]);
        }

        $student->update(['max_devices' => $request->integer('max_devices')]);

        return response()->json(['success' => true, 'message' => 'تم تحديث الحد الأقصى لعدد الأجهزة المسموح بها لهذا الطالب.']);
    }
}
