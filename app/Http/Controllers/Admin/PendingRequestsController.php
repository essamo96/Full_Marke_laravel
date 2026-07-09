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

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $search = $request->get('generalSearch') ?? $request->get('search_value') ?? $request->input('search.value');
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
            return '<div class="fw-bold">'.$name.'</div><div class="text-muted small">'.$row->email.'</div>';
        });

        $datatable->addColumn('region', function ($row) {
            return $row->region ? $row->region->name : '-';
        });

        $datatable->addColumn('branch', function ($row) {
            return $row->branch ? $row->branch->name : '-';
        });

        $datatable->editColumn('status', function ($row) {
            // Status: 0 = Pending/Inactive, 1 = Active
            $badge = $row->status == 1 ? 'success' : 'warning';
            $statusText = $row->status == 1 ? 'مفعل' : 'معلق / غير مفعل';
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

        $datatable->escapeColumns(['*']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function postDelete(Request $request)
    {
        $id = $request->get('id');

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
