<?php

namespace App\Http\Controllers\Admin;

use App\Models\Program;
use App\Models\Registration;
use App\Models\Subject;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RegistrationsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'registrations';
        $this->path = 'registrations';
    }

    public function getIndex(Request $request)
    {
        return view('admin.registrations.view', self::$data + [
            'programs' => Program::orderBy('sort_order')->get(),
            'subjects' => Subject::orderBy('sort_order')->get(),
            'groups' => \App\Models\Group::all()
        ]);
    }

    private function getFilteredQuery(Request $request)
    {
        $query = Registration::with(['student', 'subject.program', 'group']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('program_id')) {
            $query->whereHas('subject', function ($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('name')) {
            $search = $request->name;
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('full_name_ar', 'like', "%{$search}%")
                            ->orWhere('full_name_en', 'like', "%{$search}%")
                            ->orWhere('national_id', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    public function getList(Request $request)
    {
        $query = $this->getFilteredQuery($request);

        return DataTables::of($query)
            ->addColumn('registration_number', function ($row) {
                return '<span class="text-dark fw-bold">' . $row->registration_number . '</span>';
            })
            ->addColumn('student_info', function ($row) {
                if (!$row->student) return '-';
                $name = app()->getLocale() == 'ar' ? $row->student->full_name_ar : $row->student->full_name_en;
                $email = $row->student->email ?? '';
                
                $imageUrl = $row->student->image 
                    ? asset('storage/' . $row->student->image) 
                    : asset('assets/admin/media/avatars/blank.png');
                    
                return '
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-45px me-5">
                        <img src="' . $imageUrl . '" alt=""/>
                    </div>
                    <div class="d-flex justify-content-start flex-column">
                        <span class="text-dark fw-bold mb-1 fs-6">' . $name . '</span>
                        <span class="text-muted fw-semibold d-block fs-7">' . $email . '</span>
                    </div>
                </div>';
            })
            ->addColumn('subject_name', function ($row) {
                $name = app()->getLocale() == 'ar' ? ($row->subject->name_ar ?? '') : ($row->subject->name_en ?? '');
                return $name ? '<span class="badge badge-light-primary fs-7 fw-bold">' . $name . '</span>' : '-';
            })
            ->addColumn('group_name', function ($row) {
                $name = $row->group->name ?? '-';
                return $name !== '-' ? '<span class="badge badge-light-info fs-7 fw-bold">' . $name . '</span>' : '-';
            })
            ->addColumn('remaining_amount', function ($row) {
                $amount = number_format($row->remaining_amount, 2);
                if ($row->remaining_amount > 0) {
                    return '<span class="text-danger fw-bold fs-6">' . $amount . '</span>';
                }
                return '<span class="text-success fw-bold fs-6">' . $amount . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status === 'fully_paid') {
                    return '<span class="badge badge-light-success fs-7 fw-bold">مدفوع بالكامل</span>';
                } elseif ($row->status === 'partially_paid') {
                    return '<span class="badge badge-light-warning fs-7 fw-bold">مدفوع جزئياً</span>';
                } else {
                    return '<span class="badge badge-light-secondary fs-7 fw-bold">قيد الانتظار</span>';
                }
            })
            ->addColumn('group_status_select', function ($row) {
                if (!$row->group_id) {
                    return '<span class="text-muted fs-8">-</span>';
                }
                $options = '';
                foreach (\App\Models\Registration::GROUP_STATUSES as $value) {
                    $selected = $row->group_status === $value ? 'selected' : '';
                    $label = \App\Models\Registration::groupStatusLabel($value);
                    $options .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
                }
                return '<select class="form-select form-select-sm group-status-select" data-id="' . $row->id . '" style="min-width: 150px;">' . $options . '</select>';
            })
            ->addColumn('created_date', function ($row) {
                return '<span class="text-muted fw-semibold">' . $row->created_at->format('Y-m-d H:i') . '</span>';
            })
            ->rawColumns(['registration_number', 'student_info', 'subject_name', 'group_name', 'remaining_amount', 'status_badge', 'group_status_select', 'created_date'])
            ->make(true);
    }

    public function exportExcel(Request $request)
    {
        $registrations = $this->getFilteredQuery($request)->get();
        $fileName = 'registrations_' . date('Y_m_d_H_i_s') . '.xls';
        
        $headers = array(
            "Content-type"        => "application/vnd.ms-excel; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        
        $callback = function() use($registrations) {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"></head>';
            echo '<body dir="rtl">';
            echo '<table border="1" style="font-family: \'Segoe UI\', Tahoma, Arial, sans-serif; text-align: center; vertical-align: middle; border-collapse: collapse;">';
            echo '<thead>';
            echo '<tr style="height: 45px;">';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 150px; text-align: center; vertical-align: middle;">' . __('app.registration_number') . '</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 250px; text-align: center; vertical-align: middle;">' . __('app.student') . '</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 150px; text-align: center; vertical-align: middle;">' . __('app.subject') . '</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 150px; text-align: center; vertical-align: middle;">' . __('app.group') . '</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 120px; text-align: center; vertical-align: middle;">' . __('app.remaining_amount') . '</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 150px; text-align: center; vertical-align: middle;">' . __('app.status') . '</th>';
            echo '<th style="background-color: #000000; color: #ffffff; font-weight: bold; font-size: 14px; width: 150px; text-align: center; vertical-align: middle;">' . __('app.date') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($registrations as $reg) {
                $studentName = app()->getLocale() == 'ar' ? ($reg->student->full_name_ar ?? '') : ($reg->student->full_name_en ?? '');
                $subjectName = app()->getLocale() == 'ar' ? ($reg->subject->name_ar ?? '') : ($reg->subject->name_en ?? '');
                $groupName = $reg->group->name ?? '-';
                
                $statusText = '';
                if ($reg->status === 'fully_paid') $statusText = 'مدفوع بالكامل';
                elseif ($reg->status === 'partially_paid') $statusText = 'مدفوع جزئياً';
                else $statusText = 'قيد الانتظار';
                
                echo '<tr style="height: 35px;">';
                echo '<td style="vertical-align: middle;">' . $reg->registration_number . '</td>';
                echo '<td style="vertical-align: middle;">' . $studentName . '</td>';
                echo '<td style="vertical-align: middle;">' . $subjectName . '</td>';
                echo '<td style="vertical-align: middle;">' . $groupName . '</td>';
                echo '<td style="vertical-align: middle;">' . number_format($reg->remaining_amount, 2) . '</td>';
                echo '<td style="vertical-align: middle;">' . $statusText . '</td>';
                echo '<td style="vertical-align: middle;">' . $reg->created_at->format('Y-m-d H:i') . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        };

        return response()->stream($callback, 200, $headers);
    }

    public function delete(Request $request)
    {
        return response()->json([
            'status' => false,
            'message' => 'Not implemented yet.'
        ]);
    }

    public function status(Request $request)
    {
        return response()->json([
            'status' => false,
            'message' => 'Not implemented yet.'
        ]);
    }

    public function updateGroupStatus(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:registrations,id',
            'group_status' => 'required|in:' . implode(',', Registration::GROUP_STATUSES),
        ]);

        $registration = Registration::findOrFail($data['id']);
        $registration->update(['group_status' => $data['group_status']]);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث حالة الطالب في المجموعة بنجاح.',
        ]);
    }
}
