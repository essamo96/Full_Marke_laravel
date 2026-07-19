<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subject;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\EducationalLesson;
use App\Models\SubjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class SubjectContentController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'subject_content';
    }

    public function index()
    {
        $subjects = Subject::with('program')->get();
        return view('admin.subject_content.index', self::$data + compact('subjects'));
    }

    public function manage($id)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subject_content.view')->with('danger', __('app.not_found'));
        }

        $units = EducationalUnit::whereHas('stage', function ($q) use ($subject) {
                $q->where('subject_id', $subject->id);
            })
            ->where('is_active', true)
            ->with(['lessons' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }, 'lessons.resources' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('admin.subject_content.manage', self::$data + compact('subject', 'units'));
    }

    private function stageFor(Subject $subject): EducationalStage
    {
        return EducationalStage::firstOrCreate(
            ['subject_id' => $subject->id],
            ['name_ar' => $subject->name_ar, 'name_en' => $subject->name_en, 'is_active' => true]
        );
    }

    public function storeUnit(Request $request, $id)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('app.not_found')], 404);
        }

        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $stage = $this->stageFor($subject);
        $unit = $stage->units()->create($data + ['is_active' => true]);

        return response()->json(['success' => true, 'id' => $unit->id]);
    }

    public function destroyUnit(EducationalUnit $unit)
    {
        $unit->delete();
        return response()->json(['success' => true]);
    }

    public function storeLesson(Request $request, EducationalUnit $unit)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $lesson = $unit->lessons()->create($data + ['is_active' => true]);

        return response()->json(['success' => true, 'id' => $lesson->id]);
    }

    public function destroyLesson(EducationalLesson $lesson)
    {
        $lesson->delete();
        return response()->json(['success' => true]);
    }

    public function storeResource(Request $request, EducationalLesson $lesson)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,document,link,zoom',
            'url' => 'nullable|required_without:file|string|max:500',
            'file' => 'nullable|required_without:url|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp4,mov,avi,webm',
            'description' => 'nullable|string|max:1000',
        ]);

        $unit = $lesson->unit;
        $subjectId = $unit?->stage?->subject_id;

        if (! $subjectId) {
            return response()->json(['success' => false, 'message' => __('app.not_found')], 422);
        }

        if ($request->hasFile('file')) {
            $data['url'] = $request->file('file')->store('subject_resources', 'public');
        }

        $resource = SubjectResource::create([
            'subject_id' => $subjectId,
            'educational_lesson_id' => $lesson->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'category' => $data['type'],
            'url' => $data['url'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'id' => $resource->id]);
    }

    public function destroyResource(SubjectResource $resource)
    {
        if ($resource->url && ! preg_match('#^https?://#i', $resource->url)) {
            Storage::disk('public')->delete($resource->url);
        }

        $resource->delete();

        return response()->json(['success' => true]);
    }
}
