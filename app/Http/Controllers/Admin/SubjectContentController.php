<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subject;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\EducationalLesson;
use App\Models\Group;
use App\Models\SubjectResource;
use App\Support\EducationalContentVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function manage(Request $request, $id)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subject_content.view')->with('danger', __('app.not_found'));
        }

        $groups = Group::where('subject_id', $subject->id)->orderBy('name')->get();

        $selectedGroupId = $request->query('group') ? (int) $request->query('group') : null;
        if ($selectedGroupId && ! $groups->contains('id', $selectedGroupId)) {
            $selectedGroupId = null;
        }

        $units = EducationalUnit::whereHas('stage', function ($q) use ($subject) {
                $q->where('subject_id', $subject->id);
            })
            ->forManagement($selectedGroupId)
            ->with(['groups', 'lessons' => function ($q) use ($selectedGroupId) {
                $q->forManagement($selectedGroupId)->with('groups')->orderBy('sort_order');
            }, 'lessons.resources' => function ($q) use ($selectedGroupId) {
                $q->forManagement($selectedGroupId)->with(['groups', 'contentExclusions'])->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        $generalResources = SubjectResource::where('subject_id', $subject->id)
            ->whereNull('educational_lesson_id')
            ->forManagement($selectedGroupId)
            ->with(['groups', 'contentExclusions'])
            ->orderBy('sort_order')
            ->get();

        $subjectStudents = \App\Models\Registration::query()
            ->where('subject_id', $subject->id)
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->with('student:id,full_name_ar,full_name_en')
            ->get()
            ->groupBy('student_id')
            ->map(function ($regs) {
                $student = $regs->first()->student;
                if (! $student) {
                    return null;
                }

                $groupIds = $regs->pluck('group_id')->filter()->unique()->values()->all();

                return [
                    'id' => $student->id,
                    'name' => $student->full_name_ar ?: $student->full_name_en,
                    'group_id' => $groupIds[0] ?? null,
                    'group_ids' => $groupIds,
                ];
            })
            ->filter()
            ->values();

        $processingResources = \App\Models\SubjectResource::where('subject_id', $subject->id)
            ->where('processing_status', 'processing')
            ->get()
            ->map(function ($resource) {
                return $resource->getRouteKey();
            })
            ->toArray();

        return view('admin.subject_content.manage', self::$data + compact(
            'subject', 'units', 'generalResources', 'subjectStudents', 'processingResources', 'groups', 'selectedGroupId'
        ));
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
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:groups,id',
            'is_shared' => 'nullable|boolean',
        ]);

        if (! empty($data['group_ids'])) {
            foreach ($data['group_ids'] as $gid) {
                $group = Group::where('id', $gid)->where('subject_id', $subject->id)->first();
                abort_unless($group, 422, __('app.not_found'));
            }
        }

        $stage = $this->stageFor($subject);
        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        $unit = $stage->units()->create([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
            'is_active' => true,
            'is_shared' => $isShared,
            'sort_order' => EducationalContentVisibility::nextSortOrder(new EducationalUnit, 'educational_stage_id', $stage->id),
        ]);

        EducationalContentVisibility::apply($unit, $isShared, $data['group_ids'] ?? []);

        return response()->json(['success' => true, 'id' => $unit->id]);
    }

    public function updateUnit(Request $request, EducationalUnit $unit)
    {
        $subject = $unit->stage?->subject;

        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:groups,id',
            'is_shared' => 'nullable|boolean',
        ]);

        if (! empty($data['group_ids'])) {
            foreach ($data['group_ids'] as $gid) {
                $group = Group::where('id', $gid)->where('subject_id', $subject->id)->first();
                abort_unless($group, 422, __('app.not_found'));
            }
        }

        $unit->update([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
        ]);

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        EducationalContentVisibility::apply($unit, $isShared, $data['group_ids'] ?? []);

        return response()->json(['success' => true]);
    }

    public function destroyUnit(Request $request, EducationalUnit $unit)
    {
        $detachGroupId = $request->query('detach_group_id') ? (int) $request->query('detach_group_id') : null;
        $result = EducationalContentVisibility::destroyForGroup($unit, $detachGroupId);

        if ($result === 'blocked_shared') {
            if ($request->boolean('confirm_shared_delete')) {
                $unit->delete();

                return response()->json(['success' => true, 'scope' => 'global']);
            }

            return response()->json([
                'success' => false,
                'code' => 'shared_content',
                'message' => 'هذا محتوى مشترك يظهر لكل المجموعات. الحذف سيزيله من الجميع.',
            ], 409);
        }

        return response()->json(['success' => true, 'scope' => $result]);
    }

    public function reorderUnits(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);
        foreach ($data['order'] as $index => $id) {
            EducationalUnit::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['success' => true]);
    }

    public function storeLesson(Request $request, EducationalUnit $unit)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:groups,id',
            'is_shared' => 'nullable|boolean',
        ]);

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        $lesson = $unit->lessons()->create([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
            'is_active' => true,
            'is_shared' => $isShared,
            'sort_order' => EducationalContentVisibility::nextSortOrder(new EducationalLesson, 'educational_unit_id', $unit->id),
        ]);

        EducationalContentVisibility::apply($lesson, $isShared, $data['group_ids'] ?? []);

        return response()->json(['success' => true, 'id' => $lesson->id]);
    }

    public function updateLesson(Request $request, EducationalLesson $lesson)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:groups,id',
            'is_shared' => 'nullable|boolean',
        ]);

        $lesson->update([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
        ]);

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        EducationalContentVisibility::apply($lesson, $isShared, $data['group_ids'] ?? []);

        return response()->json(['success' => true]);
    }

    public function destroyLesson(Request $request, EducationalLesson $lesson)
    {
        $detachGroupId = $request->query('detach_group_id') ? (int) $request->query('detach_group_id') : null;
        $result = EducationalContentVisibility::destroyForGroup($lesson, $detachGroupId);

        if ($result === 'blocked_shared') {
            if ($request->boolean('confirm_shared_delete')) {
                $lesson->delete();

                return response()->json(['success' => true, 'scope' => 'global']);
            }

            return response()->json([
                'success' => false,
                'code' => 'shared_content',
                'message' => 'هذا محتوى مشترك يظهر لكل المجموعات. الحذف سيزيله من الجميع.',
            ], 409);
        }

        return response()->json(['success' => true, 'scope' => $result]);
    }

    public function reorderLessons(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);
        foreach ($data['order'] as $index => $id) {
            EducationalLesson::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['success' => true]);
    }

    public function storeResource(Request $request, EducationalLesson $lesson)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,document,image,link,zoom',
            'url' => 'nullable|required_without_all:uploaded_path,file|string|max:500',
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,gif',
            'uploaded_path' => 'nullable|string|starts_with:incoming/',
            'original_filename' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'allow_download' => 'nullable|boolean',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:groups,id',
            'is_shared' => 'nullable|boolean',
            'excluded_student_ids' => 'nullable|array',
            'excluded_student_ids.*' => 'integer|exists:students,id',
        ]);

        $unit = $lesson->unit;
        $subjectId = $unit?->stage?->subject_id;

        if (! $subjectId) {
            return response()->json(['success' => false, 'message' => __('app.not_found')], 422);
        }

        if (! empty($data['uploaded_path'])) {
            if (! Storage::disk('protected_videos')->exists($data['uploaded_path'])) {
                return response()->json(['success' => false, 'message' => 'الملف المرفوع غير موجود، يرجى إعادة الرفع'], 422);
            }
            $extension = pathinfo($data['uploaded_path'], PATHINFO_EXTENSION);
            if (!$extension) $extension = $data['type'] === 'video' ? 'mp4' : 'bin';
            $storedPath = 'resources/'.Str::uuid().'.'.$extension;
            Storage::disk('protected_videos')->move($data['uploaded_path'], $storedPath);
        } elseif ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('resources', 'protected_videos');
        } else {
            $storedPath = $data['url'] ?? null;
        }

        if (! $storedPath) {
            return response()->json(['success' => false, 'message' => 'يرجى إرفاق ملف أو رابط صحيح'], 422);
        }

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        $resource = SubjectResource::create([
            'subject_id' => $subjectId,
            'educational_lesson_id' => $lesson->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'category' => $data['type'],
            'url' => $storedPath,
            'original_filename' => $data['original_filename'] ?? null,
            'processing_status' => 'ready',
            'description' => $data['description'] ?? null,
            'allow_download' => $data['allow_download'] ?? false,
            'is_active' => true,
            'is_shared' => $isShared,
            'sort_order' => EducationalContentVisibility::nextSortOrder(new SubjectResource, 'educational_lesson_id', $lesson->id),
        ]);

        EducationalContentVisibility::apply($resource, $isShared, $data['group_ids'] ?? []);
        EducationalContentVisibility::syncExclusions($resource, $subjectId, $data['excluded_student_ids'] ?? []);

        return response()->json(['success' => true, 'id' => $resource->getRouteKey(), 'processing_status' => $resource->processing_status]);
    }

    public function storeGeneralResource(Request $request, $id)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('app.not_found')], 404);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,document,image,link,zoom',
            'url' => 'nullable|required_without_all:uploaded_path,file|string|max:500',
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,gif',
            'uploaded_path' => 'nullable|string|starts_with:incoming/',
            'original_filename' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'allow_download' => 'nullable|boolean',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:groups,id',
            'is_shared' => 'nullable|boolean',
            'excluded_student_ids' => 'nullable|array',
            'excluded_student_ids.*' => 'integer|exists:students,id',
        ]);

        if (! empty($data['group_ids'])) {
            foreach ($data['group_ids'] as $gid) {
                abort_unless(
                    Group::where('id', $gid)->where('subject_id', $subject->id)->exists(),
                    422,
                    'إحدى المجموعات لا تتبع نفس المادة.'
                );
            }
        }

        if (! empty($data['uploaded_path'])) {
            if (! Storage::disk('protected_videos')->exists($data['uploaded_path'])) {
                return response()->json(['success' => false, 'message' => 'الملف المرفوع غير موجود، يرجى إعادة الرفع'], 422);
            }
            $extension = pathinfo($data['uploaded_path'], PATHINFO_EXTENSION);
            if (! $extension) {
                $extension = $data['type'] === 'video' ? 'mp4' : 'bin';
            }
            $storedPath = 'resources/'.Str::uuid().'.'.$extension;
            Storage::disk('protected_videos')->move($data['uploaded_path'], $storedPath);
        } elseif ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('resources', 'protected_videos');
        } else {
            $storedPath = $data['url'] ?? null;
        }

        if (! $storedPath) {
            return response()->json(['success' => false, 'message' => 'يرجى إرفاق ملف أو رابط صحيح'], 422);
        }

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        $nextSort = ((int) SubjectResource::where('subject_id', $subject->id)
            ->whereNull('educational_lesson_id')
            ->max('sort_order')) + 1;

        $resource = SubjectResource::create([
            'subject_id' => $subject->id,
            'educational_lesson_id' => null,
            'title' => $data['title'],
            'type' => $data['type'],
            'category' => $data['type'],
            'url' => $storedPath,
            'original_filename' => $data['original_filename'] ?? null,
            'processing_status' => 'ready',
            'description' => $data['description'] ?? null,
            'allow_download' => $data['allow_download'] ?? false,
            'is_active' => true,
            'is_shared' => $isShared,
            'sort_order' => $nextSort,
        ]);

        EducationalContentVisibility::apply($resource, $isShared, $data['group_ids'] ?? []);
        EducationalContentVisibility::syncExclusions($resource, $subject->id, $data['excluded_student_ids'] ?? []);

        return response()->json(['success' => true, 'id' => $resource->getRouteKey(), 'processing_status' => $resource->processing_status]);
    }

    public function updateResource(Request $request, SubjectResource $resource)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,document,image,link,zoom',
            'url' => 'nullable|string|max:500',
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,gif',
            'uploaded_path' => 'nullable|string|starts_with:incoming/',
            'original_filename' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'allow_download' => 'nullable|boolean',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:groups,id',
            'is_shared' => 'nullable|boolean',
            'excluded_student_ids' => 'nullable|array',
            'excluded_student_ids.*' => 'integer|exists:students,id',
        ]);

        $storedPath = $resource->url;
        $originalFilename = $resource->original_filename;
        $fileChanged = false;

        if (! empty($data['uploaded_path'])) {
            if (! Storage::disk('protected_videos')->exists($data['uploaded_path'])) {
                return response()->json(['success' => false, 'message' => 'الملف المرفوع غير موجود، يرجى إعادة الرفع'], 422);
            }
            $extension = pathinfo($data['uploaded_path'], PATHINFO_EXTENSION);
            if (!$extension) $extension = $data['type'] === 'video' ? 'mp4' : 'bin';
            $storedPath = 'resources/'.Str::uuid().'.'.$extension;
            Storage::disk('protected_videos')->move($data['uploaded_path'], $storedPath);
            $originalFilename = $data['original_filename'] ?? null;
            $fileChanged = true;
        } elseif ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('resources', 'protected_videos');
            $originalFilename = $request->file('file')->getClientOriginalName();
            $fileChanged = true;
        } elseif (!empty($data['url']) && in_array($data['type'], ['link', 'zoom'])) {
            if ($data['url'] !== $resource->url) {
                $storedPath = $data['url'];
                $fileChanged = true;
            }
        }

        if ($fileChanged && !preg_match('#^https?://#i', (string) $resource->url)) {
            Storage::disk('protected_videos')->delete($resource->url);
            Storage::disk('protected_videos')->deleteDirectory("resources/{$resource->id}");
        }

        $processingStatus = 'ready';

        $resource->update([
            'title' => $data['title'],
            'type' => $data['type'],
            'category' => $data['type'],
            'url' => $storedPath,
            'original_filename' => $originalFilename,
            'processing_status' => $processingStatus,
            'description' => $data['description'] ?? null,
            'allow_download' => $data['allow_download'] ?? false,
        ]);

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        EducationalContentVisibility::apply($resource, $isShared, $data['group_ids'] ?? []);
        EducationalContentVisibility::syncExclusions($resource, (int) $resource->subject_id, $data['excluded_student_ids'] ?? []);

        return response()->json(['success' => true, 'id' => $resource->getRouteKey(), 'processing_status' => $processingStatus]);
    }

    public function shareUnit(Request $request, EducationalUnit $unit)
    {
        return $this->shareContent($request, $unit, $unit->stage->subject_id);
    }

    public function shareLesson(Request $request, EducationalLesson $lesson)
    {
        return $this->shareContent($request, $lesson, $lesson->unit->stage->subject_id);
    }

    public function shareResource(Request $request, SubjectResource $resource)
    {
        return $this->shareContent($request, $resource, $resource->subject_id);
    }

    private function shareContent(Request $request, $model, int $subjectId)
    {
        $data = $request->validate([
            'group_ids' => 'required|array|min:1',
            'group_ids.*' => 'integer|exists:groups,id',
        ]);

        foreach ($data['group_ids'] as $gid) {
            abort_unless(
                Group::where('id', $gid)->where('subject_id', $subjectId)->exists(),
                422,
                'إحدى المجموعات لا تتبع نفس المادة.'
            );
        }

        if ((bool) $model->is_shared) {
            return response()->json([
                'success' => true,
                'message' => 'هذا المحتوى مشترك بالفعل مع كل مجموعات المادة.',
            ]);
        }

        EducationalContentVisibility::shareWithGroups($model, $data['group_ids']);

        return response()->json([
            'success' => true,
            'message' => 'تمت مشاركة المحتوى مع المجموعات المحددة دون إعادة رفع.',
            'group_ids' => $model->groups()->pluck('groups.id'),
        ]);
    }

    public function viewResourceFile(SubjectResource $resource)
    {
        abort_if($resource->isExternalLink() || ! $resource->url, 404);
        abort_unless(Storage::disk('protected_videos')->exists($resource->url), 404);

        return Storage::disk('protected_videos')->response($resource->url, $resource->original_filename);
    }

    public function destroyResource(Request $request, SubjectResource $resource)
    {
        $detachGroupId = $request->query('detach_group_id') ? (int) $request->query('detach_group_id') : null;

        if ($detachGroupId && (bool) $resource->is_shared && ! $request->boolean('confirm_shared_delete')) {
            return response()->json([
                'success' => false,
                'code' => 'shared_content',
                'message' => 'هذا مرفق مشترك يظهر لكل المجموعات. الحذف سيزيله من الجميع (يمكن استرجاعه من الأرشيف).',
            ], 409);
        }

        if ($detachGroupId && ! (bool) $resource->is_shared) {
            $resource->groups()->detach($detachGroupId);
            if ($resource->groups()->count() === 0) {
                $resource->update(['deleted_by' => auth('admin')->id()]);
                $resource->delete();
            }

            return response()->json(['success' => true]);
        }

        $resource->update(['deleted_by' => auth('admin')->id()]);
        $resource->delete();

        return response()->json(['success' => true]);
    }

    public function reorderResources(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);
        foreach ($data['order'] as $index => $id) {
            SubjectResource::where('id', $id)->update(['sort_order' => $index + 1]);
        }
        return response()->json(['success' => true]);
    }

    public function progress(SubjectResource $resource)
    {
        $percentage = \Illuminate\Support\Facades\Cache::get("video_progress_{$resource->id}", 0);
        
        return response()->json([
            'status' => $resource->processing_status, // 'processing', 'ready', 'failed'
            'percentage' => $percentage
        ]);
    }
}
