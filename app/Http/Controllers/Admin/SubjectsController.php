<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SubjectRequest;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class SubjectsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'subjects';
        $this->path = 'subjects';
    }

    public function getIndex($programId = null)
    {
        $program = $programId ? Program::findOrFail(Crypt::decrypt($programId)) : null;
        $programs = Program::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.subjects.view', self::$data + ['program' => $program, 'programs' => $programs]);
    }

    public function getList(Request $request, $programId = null)
    {
        $program = $programId ? Program::findOrFail(Crypt::decrypt($programId)) : null;
        $search = $request->get('search_value');

        $subjects = Subject::when($program, fn($q) => $q->where('program_id', $program->id))
            ->when(!$program && $request->filled('program_id'), fn($q) => $q->where('program_id', $request->program_id))
            ->when($request->filled('is_active'), fn($q) => $q->where('is_active', $request->is_active))
            ->withCount('groups')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")))
            ->orderBy('sort_order');

        return DataTables::of($subjects)
            ->editColumn('image', fn ($subject) => view('admin.subjects.parts.image', ['subject' => $subject])->render())
            ->addColumn('name', fn ($subject) => view('admin.subjects.parts.name', ['subject' => $subject])->render())
            ->addColumn('groups_count', fn ($subject) => $subject->groups_count)
            ->addColumn('fee', fn ($subject) => $subject->fee)
            ->addColumn('status', fn ($subject) => view('admin.subjects.parts.status', ['subject' => $subject])->render())
            ->addColumn('actions', fn ($subject) => view('admin.subjects.parts.actions', ['subject' => $subject, 'program' => $program])->render())
            ->rawColumns(['image', 'name', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd($programId = null)
    {
        $program = $programId ? Program::findOrFail(Crypt::decrypt($programId)) : null;
        $programs = $program ? collect([$program]) : Program::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.subjects.add', self::$data + [
            'info' => null,
            'program' => $program,
            'programs' => $programs,
            'teachers' => Teacher::active()->orderBy('name')->get()]);
    }

    public function postAdd(SubjectRequest $request, $programId = null)
    {
        $progId = $programId ? Crypt::decrypt($programId) : $request->input('program_id');
        $program = Program::findOrFail($progId);
        
        $subject = Subject::create($request->safe()->except(['image', 'teacher_ids']) + [
            'program_id' => $program->id,
            'is_active' => $request->boolean('is_active', true)]);

        if ($request->hasFile('image')) {
            $subject->update(['image' => $request->file('image')->store('subjects', 'public')]);
        }

        $subject->teachers()->sync($request->input('teacher_ids', []));

        return redirect()->route('programs.subjects.view', Crypt::encrypt($program->id))->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $programId, $id)
    {
        try {
            $program = Program::findOrFail(Crypt::decrypt($programId));
            $subject = Subject::with('teachers')->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('programs.view')->with('danger', __('app.not_found'));
        }

        return view('admin.subjects.add', self::$data + [
            'info' => $subject,
            'program' => $program,
            'teachers' => Teacher::active()->orderBy('name')->get()]);
    }

    public function postEdit(SubjectRequest $request, $programId, $id)
    {
        try {
            $program = Program::findOrFail(Crypt::decrypt($programId));
            $subject = Subject::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('programs.view')->with('danger', __('app.not_found'));
        }

        $data = $request->safe()->except(['image', 'teacher_ids']) + [
            'is_active' => $request->boolean('is_active', true)];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('subjects', 'public');
        }

        $subject->update($data);
        $subject->teachers()->sync($request->input('teacher_ids', []));

        return redirect()->route('programs.subjects.view', Crypt::encrypt($program->id))->with('success', __('app.update_success'));
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
