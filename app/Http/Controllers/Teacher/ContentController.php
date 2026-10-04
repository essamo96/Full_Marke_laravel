<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Support\EducationalContentVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    private function teacher()
    {
        return Auth::guard('teacher')->user();
    }

    /**
     * Subject IDs this teacher may manage content for.
     *
     * A teacher reaches a subject two different ways: directly through the
     * subject_teacher pivot, or by being assigned to a group that belongs to
     * the subject. Checking only the pivot made "إدارة الموارد" 403 for any
     * teacher who was assigned a group without also being attached to the
     * subject itself (the link on the group page passes $group->subject).
     */
    private function allowedSubjectIds(): array
    {
        $teacher = $this->teacher();

        return once(fn () => $teacher->subjects()->pluck('subjects.id')
            ->merge(Group::where('teacher_id', $teacher->id)->pluck('subject_id'))
            ->filter()
            ->unique()
            ->values()
            ->all());
    }

    private function canAccessSubject(?int $subjectId): bool
    {
        return $subjectId !== null && in_array($subjectId, $this->allowedSubjectIds(), true);
    }

    /** Group IDs this teacher owns. */
    private function allowedGroupIds(): array
    {
        $teacher = $this->teacher();

        return once(fn () => Group::where('teacher_id', $teacher->id)->pluck('id')->all());
    }

    private function canAccessGroup(?int $groupId): bool
    {
        return $groupId !== null && in_array($groupId, $this->allowedGroupIds(), true);
    }

    private function authorizeSubject(Subject $subject): void
    {
        abort_unless($this->canAccessSubject($subject->id), 403);
    }

    private function authorizeLesson(EducationalLesson $lesson): Subject
    {
        $subject = $lesson->unit?->stage?->subject;
        abort_unless($subject && $this->canAccessSubject($subject->id), 404);

        return $subject;
    }

    private function authorizeSubjectResource(SubjectResource $resource): void
    {
        abort_unless($this->canAccessSubject($resource->subject_id), 403);
    }

    /** Every subject the teacher may manage, however they are linked to it. */
    private function accessibleSubjects()
    {
        return Subject::whereIn('id', $this->allowedSubjectIds())
            ->with('program')
            ->orderBy('name_ar')
            ->get();
    }

    public function index()
    {
        $subjects = $this->accessibleSubjects();

        return view('teacher.content.index', compact('subjects'));
    }

    public function hub()
    {
        $teacher = $this->teacher();

        $subjects = $this->accessibleSubjects();
        $groups = Group::where('teacher_id', $teacher->id)
            ->with(['subject.program'])
            ->withCount(['registrations as students_count' => function ($q) {
                $q->whereIn('status', ['pending', 'partially_paid', 'fully_paid']);
            }])
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.content.hub', compact('subjects', 'groups'));
    }

    public function manage(Request $request, Subject $subject)
    {
        $this->authorizeSubject($subject);

        $teacher = $this->teacher();
        $groups = Group::where('subject_id', $subject->id)->where('teacher_id', $teacher->id)->orderBy('name')->get();

        $selectedGroupId = $request->query('group') ? (int) $request->query('group') : null;
        if ($selectedGroupId && ! $groups->contains('id', $selectedGroupId)) {
            $selectedGroupId = null;
        }

        $myGroupIds = $groups->pluck('id')->all();

        $units = EducationalUnit::whereHas('stage', function ($q) use ($subject) {
                $q->where('subject_id', $subject->id);
            })
            ->forManagement($selectedGroupId, $myGroupIds)
            ->with(['groups', 'contentExclusions', 'lessons' => function ($q) use ($selectedGroupId, $myGroupIds) {
                $q->forManagement($selectedGroupId, $myGroupIds)->with(['groups', 'contentExclusions'])->orderBy('sort_order');
            }, 'lessons.resources' => function ($q) use ($selectedGroupId, $myGroupIds) {
                $q->forManagement($selectedGroupId, $myGroupIds)->with(['groups', 'contentExclusions'])->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        $generalResources = SubjectResource::where('subject_id', $subject->id)
            ->whereNull('educational_lesson_id')
            ->forManagement($selectedGroupId, $myGroupIds)
            ->with(['groups', 'contentExclusions'])
            ->orderBy('sort_order')
            ->get();

        $subjectStudents = \App\Models\Registration::query()
            ->where('subject_id', $subject->id)
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->whereIn('group_id', $groups->pluck('id'))
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

        $processingResources = SubjectResource::where('subject_id', $subject->id)
            ->where('processing_status', 'processing')
            ->get()
            ->map(fn ($resource) => $resource->getRouteKey())
            ->toArray();

        return view('teacher.content.manage', compact(
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

    public function storeUnit(Request $request, Subject $subject)
    {
        $this->authorizeSubject($subject);

        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer',
            'is_shared' => 'nullable|boolean',
        ]);

        if (! empty($data['group_ids'])) {
            foreach ($data['group_ids'] as $gid) {
                abort_unless($this->canAccessGroup((int) $gid), 403);
                $group = Group::find($gid);
                abort_unless($group && $group->subject_id === $subject->id, 422);
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
        abort_unless($subject && $this->canAccessSubject($subject->id), 403);

        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer',
            'is_shared' => 'nullable|boolean',
        ]);

        if (! empty($data['group_ids'])) {
            foreach ($data['group_ids'] as $gid) {
                abort_unless($this->canAccessGroup((int) $gid), 403);
                $group = Group::find($gid);
                abort_unless($group && $group->subject_id === $subject->id, 422);
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
        $subject = $unit->stage?->subject;
        abort_unless($subject && $this->canAccessSubject($subject->id), 403);

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
        $subject = $unit->stage?->subject;
        abort_unless($subject && $this->canAccessSubject($subject->id), 403);

        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer',
            'is_shared' => 'nullable|boolean',
        ]);

        if (! empty($data['group_ids'])) {
            foreach ($data['group_ids'] as $gid) {
                abort_unless($this->canAccessGroup((int) $gid), 403);
            }
        }

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
        $this->authorizeLesson($lesson);

        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer',
            'is_shared' => 'nullable|boolean',
        ]);

        if (! empty($data['group_ids'])) {
            foreach ($data['group_ids'] as $gid) {
                abort_unless($this->canAccessGroup((int) $gid), 403);
            }
        }

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
        $this->authorizeLesson($lesson);

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
        $subject = $this->authorizeLesson($lesson);

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

        $storedPath = null;

        if (! empty($data['uploaded_path']) && Storage::disk('protected_videos')->exists($data['uploaded_path'])) {
            $extension = pathinfo($data['uploaded_path'], PATHINFO_EXTENSION);
            if (!$extension) $extension = $data['type'] === 'video' ? 'mp4' : 'bin';
            $storedPath = 'resources/'.Str::uuid().'.'.$extension;
            Storage::disk('protected_videos')->move($data['uploaded_path'], $storedPath);
        } elseif ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('resources', 'protected_videos');
        } else {
            $storedPath = $data['url'] ?? null;
        }

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        $resource = SubjectResource::create([
            'subject_id' => $subject->id,
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
        $this->syncTeacherExclusions($resource, $subject->id, $data['excluded_student_ids'] ?? []);

        return response()->json(['success' => true, 'id' => $resource->getRouteKey()]);
    }

    public function storeGeneralResource(Request $request, Subject $subject)
    {
        $this->authorizeSubject($subject);

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
                abort_unless($this->canAccessGroup((int) $gid), 403);
            }
        }

        $storedPath = null;
        if (! empty($data['uploaded_path']) && Storage::disk('protected_videos')->exists($data['uploaded_path'])) {
            $extension = pathinfo($data['uploaded_path'], PATHINFO_EXTENSION) ?: ($data['type'] === 'video' ? 'mp4' : 'bin');
            $storedPath = 'resources/'.Str::uuid().'.'.$extension;
            Storage::disk('protected_videos')->move($data['uploaded_path'], $storedPath);
        } elseif ($request->hasFile('file')) {
            $storedPath = $request->file('file')->store('resources', 'protected_videos');
        } else {
            $storedPath = $data['url'] ?? null;
        }

        abort_unless($storedPath, 422, 'يرجى إرفاق ملف أو رابط صحيح');

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        $nextSort = ((int) SubjectResource::where('subject_id', $subject->id)->whereNull('educational_lesson_id')->max('sort_order')) + 1;

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
        $this->syncTeacherExclusions($resource, $subject->id, $data['excluded_student_ids'] ?? []);

        return response()->json(['success' => true, 'id' => $resource->getRouteKey()]);
    }

    public function updateResource(Request $request, SubjectResource $resource)
    {
        $this->authorizeSubjectResource($resource);

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
            if (Storage::disk('protected_videos')->exists($data['uploaded_path'])) {
                $extension = pathinfo($data['uploaded_path'], PATHINFO_EXTENSION);
                if (!$extension) $extension = $data['type'] === 'video' ? 'mp4' : 'bin';
                $storedPath = 'resources/'.Str::uuid().'.'.$extension;
                Storage::disk('protected_videos')->move($data['uploaded_path'], $storedPath);
                $originalFilename = $data['original_filename'] ?? null;
                $fileChanged = true;
            }
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
        }

        $resource->update([
            'title' => $data['title'],
            'type' => $data['type'],
            'category' => $data['type'],
            'url' => $storedPath,
            'original_filename' => $originalFilename,
            'description' => $data['description'] ?? null,
            'allow_download' => $data['allow_download'] ?? false,
        ]);

        $isShared = EducationalContentVisibility::resolveIsShared($request, $data['group_ids'] ?? null);
        EducationalContentVisibility::apply($resource, $isShared, $data['group_ids'] ?? []);
        $this->syncTeacherExclusions($resource, (int) $resource->subject_id, $data['excluded_student_ids'] ?? []);

        return response()->json(['success' => true]);
    }

    /**
     * Teachers only manage exclusions for students in their own groups: exclusions
     * of anyone outside that pool (e.g. set by an admin) are preserved untouched.
     */
    private function syncTeacherExclusions($resource, int $subjectId, array $submittedIds): void
    {
        $pool = \App\Models\Registration::query()
            ->where('subject_id', $subjectId)
            ->whereIn('group_id', $this->allowedGroupIds())
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->unique();

        $kept = $resource->contentExclusions()->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $pool->contains($id));

        $mine = collect($submittedIds)->map(fn ($id) => (int) $id)->filter(fn ($id) => $pool->contains($id));

        EducationalContentVisibility::syncExclusions($resource, $subjectId, $kept->merge($mine)->unique()->values()->all());
    }

    public function updateExclusions(Request $request, SubjectResource $resource)
    {
        $this->authorizeSubjectResource($resource);

        return $this->saveExclusions($request, $resource, (int) $resource->subject_id);
    }

    public function updateUnitExclusions(Request $request, EducationalUnit $unit)
    {
        $subject = $unit->stage?->subject;
        abort_unless($subject && $this->canAccessSubject($subject->id), 404);

        return $this->saveExclusions($request, $unit, $subject->id);
    }

    public function updateLessonExclusions(Request $request, EducationalLesson $lesson)
    {
        $this->authorizeLesson($lesson);

        return $this->saveExclusions($request, $lesson, (int) $lesson->unit->stage->subject_id);
    }

    private function saveExclusions(Request $request, $model, int $subjectId)
    {
        $data = $request->validate([
            'excluded_student_ids' => 'nullable|array',
            'excluded_student_ids.*' => 'integer|exists:students,id',
        ]);

        $this->syncTeacherExclusions($model, $subjectId, $data['excluded_student_ids'] ?? []);

        return response()->json(['success' => true, 'message' => 'تم تحديث الاستثناءات.']);
    }

    public function unshareUnit(Request $request, EducationalUnit $unit)
    {
        $subject = $unit->stage?->subject;
        abort_unless($subject && $this->canAccessSubject($subject->id), 404);

        return $this->unshareContent($request, $unit, $subject->id);
    }

    public function unshareLesson(Request $request, EducationalLesson $lesson)
    {
        $this->authorizeLesson($lesson);

        return $this->unshareContent($request, $lesson, (int) $lesson->unit->stage->subject_id);
    }

    public function unshareResource(Request $request, SubjectResource $resource)
    {
        $this->authorizeSubjectResource($resource);

        return $this->unshareContent($request, $resource, (int) $resource->subject_id);
    }

    /**
     * Stop showing a unit / lesson / resource to one of the teacher's groups.
     * Content shared with "all groups" is first converted to an explicit list
     * of every group of the subject (so other teachers' groups keep access),
     * minus the removed one. Children follow, mirroring how sharing cascades.
     */
    private function unshareContent(Request $request, $model, int $subjectId)
    {
        abort_unless(in_array($subjectId, $this->allowedSubjectIds(), true), 403);

        $data = $request->validate(['group_id' => 'required|integer|exists:groups,id']);
        $groupId = (int) $data['group_id'];

        abort_unless(in_array($groupId, $this->allowedGroupIds(), true), 403);
        abort_unless(Group::where('id', $groupId)->where('subject_id', $subjectId)->exists(), 422);

        $allSubjectGroups = Group::where('subject_id', $subjectId)->pluck('id')->all();

        $detach = function ($m) use ($groupId, $allSubjectGroups) {
            if ($m->is_shared) {
                $m->forceFill(['is_shared' => false])->save();
                $m->groups()->sync(array_values(array_diff($allSubjectGroups, [$groupId])));
            } else {
                $m->groups()->detach($groupId);
            }
        };

        $detach($model);

        if ($model instanceof EducationalUnit) {
            $model->loadMissing('lessons.resources');
            foreach ($model->lessons as $lesson) {
                $detach($lesson);
                foreach ($lesson->resources as $resource) {
                    $detach($resource);
                }
            }
        } elseif ($model instanceof EducationalLesson) {
            $model->loadMissing('resources');
            foreach ($model->resources as $resource) {
                $detach($resource);
            }
        }

        $model->refresh();

        return response()->json([
            'success' => true,
            'is_shared' => (bool) $model->is_shared,
            'group_ids' => $model->groups()->pluck('groups.id'),
        ]);
    }

    public function shareUnit(Request $request, EducationalUnit $unit)
    {
        $subject = $unit->stage?->subject;
        abort_unless($subject && $this->canAccessSubject($subject->id), 404);

        return $this->shareContent($request, $unit, $subject->id);
    }

    public function shareLesson(Request $request, EducationalLesson $lesson)
    {
        $this->authorizeLesson($lesson);

        return $this->shareContent($request, $lesson, $lesson->unit->stage->subject_id);
    }

    public function shareResource(Request $request, SubjectResource $resource)
    {
        $this->authorizeSubjectResource($resource);

        return $this->shareContent($request, $resource, $resource->subject_id);
    }

    private function shareContent(Request $request, $model, int $subjectId)
    {
        abort_unless(in_array($subjectId, $this->allowedSubjectIds(), true), 403);

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
        $this->authorizeSubjectResource($resource);

        abort_if($resource->isExternalLink() || ! $resource->url, 404);
        abort_if($resource->isVideo() && ! $resource->isReady(), 409, 'الفيديو غير جاهز للعرض بعد.');
        abort_unless(Storage::disk('protected_videos')->exists($resource->url), 404);

        $path = Storage::disk('protected_videos')->path($resource->url);

        $headers = [
            'Content-Disposition' => 'inline; filename="' . ($resource->original_filename ?: basename($path)) . '"',
            // The viewer renders these in-page; keep them out of caches and
            // out of other sites' frames so the URL is not a reusable handle.
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'same-origin',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ];

        // response()->file() already supports Range requests (needed for video
        // seeking), it just needs the right Content-Type — the student-side
        // video stream endpoint hardcodes video/mp4 for the same reason
        // rather than relying on MIME sniffing.
        if ($resource->isVideo()) {
            $headers['Content-Type'] = 'video/mp4';
            $headers['Accept-Ranges'] = 'bytes';
        }

        return response()->file($path, $headers);
    }

    public function destroyResource(Request $request, SubjectResource $resource)
    {
        $this->authorizeSubjectResource($resource);

        $detachGroupId = $request->query('detach_group_id') ? (int) $request->query('detach_group_id') : null;
        $result = EducationalContentVisibility::destroyForGroup($resource, $detachGroupId);

        if ($result === 'blocked_shared') {
            if ($request->boolean('confirm_shared_delete')) {
                $resource->delete();

                return response()->json(['success' => true, 'scope' => 'soft_deleted']);
            }

            return response()->json([
                'success' => false,
                'code' => 'shared_content',
                'message' => 'هذا مرفق مشترك يظهر لكل المجموعات. الحذف سيزيله من الجميع (يمكن استرجاعه من الأرشيف).',
            ], 409);
        }

        return response()->json(['success' => true, 'scope' => $result]);
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
        $this->authorizeSubjectResource($resource);

        $percentage = \Illuminate\Support\Facades\Cache::get("video_progress_{$resource->id}", 0);

        return response()->json([
            'status' => $resource->processing_status,
            'percentage' => $percentage,
        ]);
    }
}
