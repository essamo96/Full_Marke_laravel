<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SubjectRequest;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SubjectsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'subjects';
        $this->path = 'subjects';
    }

    public function getIndex()
    {
        $subjects = Subject::with('program')->withCount('groups')->orderBy('order')->paginate(15);

        return view('admin.subjects.view', self::$data + ['subjects' => $subjects]);
    }

    public function getAdd()
    {
        return view('admin.subjects.add', self::$data + [
            'info' => null,
            'programs' => Program::orderBy('order')->get(),
            'teachers' => Teacher::active()->orderBy('name')->get()]);
    }

    public function postAdd(SubjectRequest $request)
    {
        $subject = Subject::create($request->safe()->except(['image', 'teacher_ids']) + [
            'is_active' => $request->boolean('is_active', true)]);

        if ($request->hasFile('image')) {
            $subject->update(['image' => $request->file('image')->store('subjects', 'public')]);
        }

        $subject->teachers()->sync($request->input('teacher_ids', []));

        return redirect()->route('subjects.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $subject = Subject::with('teachers')->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subjects.view')->with('danger', __('app.not_found'));
        }

        return view('admin.subjects.add', self::$data + [
            'info' => $subject,
            'programs' => Program::orderBy('order')->get(),
            'teachers' => Teacher::active()->orderBy('name')->get()]);
    }

    public function postEdit(SubjectRequest $request, $id)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subjects.view')->with('danger', __('app.not_found'));
        }

        $data = $request->safe()->except(['image', 'teacher_ids']) + [
            'is_active' => $request->boolean('is_active', true)];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('subjects', 'public');
        }

        $subject->update($data);
        $subject->teachers()->sync($request->input('teacher_ids', []));

        return redirect()->route('subjects.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($request->id));
            $subject->update(['is_active' => ! $subject->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($request->id));

            if ($subject->registrations()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $subject->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
