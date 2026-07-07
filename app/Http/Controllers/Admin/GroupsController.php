<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\GroupRequest;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class GroupsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'groups';
        $this->path = 'groups';
    }

    public function getIndex()
    {
        $groups = Group::with(['subject', 'teacher'])->latest()->paginate(15);

        return view('admin.groups.view', self::$data + ['groups' => $groups]);
    }

    public function getAdd()
    {
        return view('admin.groups.add', self::$data + [
            'info' => null,
            'subjects' => Subject::active()->orderBy('order')->get(),
            'teachers' => Teacher::active()->orderBy('name')->get()]);
    }

    public function postAdd(GroupRequest $request)
    {
        Group::create($request->validated() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('groups.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $group = Group::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('groups.view')->with('danger', __('app.not_found'));
        }

        return view('admin.groups.add', self::$data + [
            'info' => $group,
            'subjects' => Subject::active()->orderBy('order')->get(),
            'teachers' => Teacher::active()->orderBy('name')->get()]);
    }

    public function postEdit(GroupRequest $request, $id)
    {
        try {
            $group = Group::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('groups.view')->with('danger', __('app.not_found'));
        }

        $group->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('groups.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $group = Group::findOrFail(Crypt::decrypt($request->id));
            $group->update(['is_active' => ! $group->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $group = Group::findOrFail(Crypt::decrypt($request->id));

            if ($group->registrations()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $group->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
