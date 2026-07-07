<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SubjectResourceRequest;
use App\Models\Subject;
use App\Models\SubjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SubjectResourcesController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'subjects';
        $this->path = 'subject_resources';
    }

    public function getIndex(Subject $subject)
    {
        $resources = $subject->resources()->orderBy('sort_order')->get();

        return view('admin.subject_resources.view', self::$data + ['subject' => $subject, 'resources' => $resources]);
    }

    public function getAdd(Subject $subject)
    {
        return view('admin.subject_resources.add', self::$data + ['subject' => $subject, 'info' => null]);
    }

    public function postAdd(SubjectResourceRequest $request, Subject $subject)
    {
        $subject->resources()->create($request->validated() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('subject_resources.view', $subject)->with('success', __('app.insert_success'));
    }

    public function getEdit(Subject $subject, $id)
    {
        try {
            $resource = SubjectResource::where('subject_id', $subject->id)->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subject_resources.view', $subject)->with('danger', __('app.not_found'));
        }

        return view('admin.subject_resources.add', self::$data + ['subject' => $subject, 'info' => $resource]);
    }

    public function postEdit(SubjectResourceRequest $request, Subject $subject, $id)
    {
        try {
            $resource = SubjectResource::where('subject_id', $subject->id)->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subject_resources.view', $subject)->with('danger', __('app.not_found'));
        }

        $resource->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('subject_resources.view', $subject)->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request, Subject $subject)
    {
        try {
            $resource = SubjectResource::where('subject_id', $subject->id)->findOrFail(Crypt::decrypt($request->id));
            $resource->update(['is_active' => ! $resource->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request, Subject $subject)
    {
        try {
            $resource = SubjectResource::where('subject_id', $subject->id)->findOrFail(Crypt::decrypt($request->id));
            $resource->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
