<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class StudentInquiryController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'student_inquiry';
    }

    public function index()
    {
        return view('admin.student_inquiry.index', self::$data);
    }

    public function details($id)
    {
        $student = Student::with([
            'branch', 
            'region', 
            'registrations.subject', 
            'registrations.group.teachers', 
            'payments.paymentMethod',
            'payments' // To handle any direct relations
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'html' => view('admin.student_inquiry.details', self::$data + compact('student'))->render()
        ]);
    }
    public function search(Request $request)
    {
        $query = Student::with(['registrations.subject', 'registrations.group']);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('full_name_ar', 'LIKE', "%$keyword%")
                  ->orWhere('phone', 'LIKE', "%$keyword%")
                  ->orWhere('national_id', 'LIKE', "%$keyword%");
            });
        }

        if ($request->filled('program_id')) {
            $query->whereHas('registrations.subject', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('registrations', function($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($request->filled('group_id')) {
            $query->whereHas('registrations', function($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }

        return response()->json($query->take(20)->get());
    }
}
