<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class StudentsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'students';
    }

    public function getIndex()
    {
        $students = Student::with('branch')->withCount('registrations')->latest()->paginate(15);

        return view('admin.students.view', self::$data + ['students' => $students]);
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
            $student = Student::findOrFail(Crypt::decrypt($request->id));
            $student->update(['status' => ! $student->status]);

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
}
