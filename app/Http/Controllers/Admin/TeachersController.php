<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TeacherRequest;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class TeachersController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'teachers';
        $this->path = 'teachers';
    }

    public function getIndex()
    {
        $teachers = Teacher::withCount('subjects')->latest()->paginate(15);

        return view('admin.teachers.view', self::$data + ['teachers' => $teachers]);
    }

    public function getAdd()
    {
        return view('admin.teachers.add', self::$data + [
            'info' => null,
            'subjects' => Subject::active()->orderBy('order')->get(),
        ]);
    }

    public function postAdd(TeacherRequest $request)
    {
        $teacher = Teacher::create($request->safe()->except(['photo', 'subject_ids', 'password']) + [
            'password' => Hash::make($request->password),
            'status' => $request->boolean('status', true),
        ]);

        if ($request->hasFile('photo')) {
            $teacher->update(['photo' => $request->file('photo')->store('teachers', 'public')]);
        }

        $teacher->subjects()->sync($request->input('subject_ids', []));

        return redirect()->route('teachers.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $teacher = Teacher::with('subjects')->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('teachers.view')->with('danger', __('app.not_found'));
        }

        return view('admin.teachers.add', self::$data + [
            'info' => $teacher,
            'subjects' => Subject::active()->orderBy('order')->get(),
        ]);
    }

    public function postEdit(TeacherRequest $request, $id)
    {
        try {
            $teacher = Teacher::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('teachers.view')->with('danger', __('app.not_found'));
        }

        $data = $request->safe()->except(['photo', 'subject_ids', 'password']) + [
            'status' => $request->boolean('status', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('teachers', 'public');
        }

        $teacher->update($data);
        $teacher->subjects()->sync($request->input('subject_ids', []));

        return redirect()->route('teachers.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $teacher = Teacher::findOrFail(Crypt::decrypt($request->id));
            $teacher->update(['status' => ! $teacher->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $teacher = Teacher::findOrFail(Crypt::decrypt($request->id));

            if ($teacher->groups()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $teacher->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
