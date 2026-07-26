<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StudentRequest;
use App\Mail\AccountStatusMail;
use App\Models\Student;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Guardian;
use App\Notifications\StudentFeeDuesNotification;
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
}
