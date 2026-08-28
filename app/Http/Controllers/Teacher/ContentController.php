<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Subject;
use App\Models\SubjectResource;
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

        $units = EducationalUnit::whereHas('stage', function ($q) use ($subject) {
                $q->where('subject_id', $subject->id);
            })
            ->forGroup($selectedGroupId)
            ->where('is_active', true)
            ->with(['lessons' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }, 'lessons.resources' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        $processingResources = SubjectResource::where('subject_id', $subject->id)
            ->where('processing_status', 'processing')
            ->get()
            ->map(fn ($resource) => $resource->getRouteKey())
            ->toArray();

        return view('teacher.content.manage', compact('subject', 'units', 'processingResources', 'groups', 'selectedGroupId'));
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
            'group_id' => 'nullable|integer',
        ]);

        if (! empty($data['group_id'])) {
            abort_unless($this->canAccessGroup((int) $data['group_id']), 403);
            $group = Group::find($data['group_id']);
            abort_unless($group && $group->subject_id === $subject->id, 422);
        }

        $stage = $this->stageFor($subject);
        $unit = $stage->units()->create([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
            'group_id' => $data['group_id'] ?? null,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'id' => $unit->id]);
    }

    public function destroyUnit(EducationalUnit $unit)
    {
        $subject = $unit->stage?->subject;
        abort_unless($subject && $this->canAccessSubject($subject->id), 403);

        $unit->delete();

        return response()->json(['success' => true]);
    }

    public function storeLesson(Request $request, EducationalUnit $unit)
    {
        $subject = $unit->stage?->subject;
        abort_unless($subject && $this->canAccessSubject($subject->id), 403);

        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $lesson = $unit->lessons()->create($data + ['is_active' => true]);

        return response()->json(['success' => true, 'id' => $lesson->id]);
    }

    public function destroyLesson(EducationalLesson $lesson)
    {
        $this->authorizeLesson($lesson);

        $lesson->delete();

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
            'group_ids' => !empty($data['group_ids']) ? array_map('intval', $data['group_ids']) : null,
        ]);

        return response()->json(['success' => true, 'id' => $resource->getRouteKey()]);
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

    public function destroyResource(SubjectResource $resource)
    {
        $this->authorizeSubjectResource($resource);

        $resource->delete();

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
