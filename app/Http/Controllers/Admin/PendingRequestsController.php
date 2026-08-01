<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class PendingRequestsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'pending_requests';
        $this->path = 'pending_requests';
    }

    public function getIndex()
    {
        // Mark notifications as read
        if (auth('admin')->check()) {
            auth('admin')->user()->unreadNotifications
                ->where('type', \App\Notifications\NewStudentRegisteredNotification::class)
                ->markAsRead();
        }

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $query = Student::with(['region', 'branch'])->latest();

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            if ($request->status == '0') {
                $query->whereNull('email_verified_at');
            } elseif ($request->status == '1') {
                $query->whereNotNull('email_verified_at');
            }
        }

        $search = $request->input('generalSearch') ?? $request->input('search_value') ?? $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name_ar', 'like', "%{$search}%")
                  ->orWhere('full_name_en', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $datatable = Datatables::of($query);
        
        $datatable->editColumn('full_name_ar', function ($row) {
            $name = app()->getLocale() == 'ar' ? $row->full_name_ar : $row->full_name_en;
            $imgUrl = $row->image ? (str_starts_with($row->image, 'site/') ? asset($row->image) : asset('storage/' . $row->image)) : asset('assets/admin/media/svg/avatars/blank.svg');
            
            return '
            <div class="d-flex align-items-center">
                <div class="symbol symbol-50px me-3">
                    <img src="'.$imgUrl.'" alt="" />
                </div>
                <div class="d-flex flex-column justify-content-center">
                    <div class="text-gray-800 fw-bold text-start">'.$name.'</div>
                    <span class="text-muted fs-7 text-start">'.$row->email.'</span>
                </div>
            </div>';
        });

        $datatable->addColumn('region', function ($row) {
            return $row->region ? (app()->getLocale() == 'ar' ? $row->region->name_ar : $row->region->name_en) : '-';
        });

        $datatable->addColumn('branch', function ($row) {
            return $row->branch ? (app()->getLocale() == 'ar' ? $row->branch->name_ar : $row->branch->name_en) : '-';
        });

        $datatable->editColumn('status', function ($row) {
            $isVerified = !is_null($row->email_verified_at);
            $badge = $isVerified ? 'success' : 'warning';
            $statusText = $isVerified ? 'مفعل (بريد مؤكد)' : 'معلق / بريد غير مؤكد';
            return '<span class="badge badge-light-'.$badge.'">'.$statusText.'</span>';
        });

        $datatable->editColumn('created_at', function ($row) {
            return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-';
        });

        $path = $this->path;
        $datatable->addColumn('actions', function ($row) use ($path) {
            $data['active_menu'] = $path;
            $data['student'] = $row;
            return view('admin.' . $this->path . '.parts.actions', $data)->render();
        });

        $datatable->rawColumns(['full_name_ar', 'status', 'actions']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function postStatus(Request $request)
    {
        $id = $request->input('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
            ]);
        }

        if ($student->email_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'الحساب مفعل بالفعل.'
            ]);
        }

        $student->update(['email_verified_at' => now(), 'status' => true]);
        $student->emailVerificationCodes()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تفعيل الحساب بنجاح.'
        ]);
    }

    public function postDelete(Request $request)
    {
        $id = $request->input('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }

        $info = Student::find($id);

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
            ]);
        }

        $delete = $info->delete();

        if ($delete) {
            return response()->json([
                'status' => 'success',
                'message' => __('app.delete_success')
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }
    }
}
