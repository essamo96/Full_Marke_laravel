<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EducationalMaterialController extends Controller
{
    public function index()
    {
        $subjects = \App\Models\Subject::with(['educationalStages.units.lessons.resources'])->get();
        return view('admin.educational_materials.index', self::$data + ['subjects' => $subjects]);
    }

    public function storeStage(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name_ar' => 'required|string',
            'name_en' => 'nullable|string',
        ]);
        \App\Models\EducationalStage::create($data);
        return response()->json(['success' => true]);
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'educational_stage_id' => 'required|exists:educational_stages,id',
            'name_ar' => 'required|string',
            'name_en' => 'nullable|string',
        ]);
        \App\Models\EducationalUnit::create($data);
        return response()->json(['success' => true]);
    }

    public function storeLesson(Request $request)
    {
        $data = $request->validate([
            'educational_unit_id' => 'required|exists:educational_units,id',
            'name_ar' => 'required|string',
            'name_en' => 'nullable|string',
        ]);
        \App\Models\EducationalLesson::create($data);
        return response()->json(['success' => true]);
    }

    public function storeResource(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'educational_lesson_id' => 'required|exists:educational_lessons,id',
            'title' => 'required|string',
            'type' => 'required|string', // file, url
            'category' => 'nullable|string',
            'url' => 'required|string',
        ]);
        \App\Models\SubjectResource::create($data);
        return response()->json(['success' => true]);
    }

    public function destroyStage(\App\Models\EducationalStage $stage)
    {
        $stage->delete();
        return response()->json(['success' => true]);
    }

    public function destroyUnit(\App\Models\EducationalUnit $unit)
    {
        $unit->delete();
        return response()->json(['success' => true]);
    }

    public function destroyLesson(\App\Models\EducationalLesson $lesson)
    {
        $lesson->delete();
        return response()->json(['success' => true]);
    }

    public function destroyResource(\App\Models\SubjectResource $resource)
    {
        $resource->delete();
        return response()->json(['success' => true]);
    }
}
