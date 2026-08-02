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

        $processingResources = \App\Models\SubjectResource::where('subject_id', $subject->id)
            ->where('processing_status', 'processing')
            ->get()
            ->map(function ($resource) {
                return $resource->getRouteKey();
            })
            ->toArray();

        return view('admin.subject_content.manage', self::$data + compact('subject', 'units', 'processingResources'));
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
            'type' => 'required|in:video,document,image,link,zoom',
            'url' => 'nullable|required_without_all:uploaded_path,file|string|max:500',
            // Small, non-chunked fallback upload (kept for quick document/image attachments).
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,gif',
            // Path produced by the resumable chunk-upload endpoint, relative to the protected_videos disk.
            'uploaded_path' => 'nullable|string|starts_with:incoming/',
            'original_filename' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'allow_download' => 'nullable|boolean',
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

        // Uploaded videos are served directly (protected MP4 streaming with a
        // session token + Referer lock — see VideoStreamController), so there's
        // no HLS transcode/encryption step to wait on: a video is ready the
        // moment its bytes are on disk, same as every other resource type.
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
        ]);

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

        // No HLS transcode/encryption step — see storeResource() above.
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

        return response()->json(['success' => true, 'id' => $resource->getRouteKey(), 'processing_status' => $processingStatus]);
    }

    public function viewResourceFile(SubjectResource $resource)
    {
        abort_if($resource->isVideo() || $resource->isExternalLink() || ! $resource->url, 404);
        abort_unless(Storage::disk('protected_videos')->exists($resource->url), 404);

        return Storage::disk('protected_videos')->response($resource->url, $resource->original_filename);
    }

    public function destroyResource(SubjectResource $resource)
    {
        if ($resource->isExternalLink()) {
            $resource->delete();

            return response()->json(['success' => true]);
        }

        // Remove the whole per-resource directory: source file (if still processing),
        // HLS playlists, segments and rotating encryption keys all live under it.
        Storage::disk('protected_videos')->deleteDirectory("resources/{$resource->id}");

        if ($resource->url && Storage::disk('protected_videos')->exists($resource->url)) {
            Storage::disk('protected_videos')->delete($resource->url);
        }

        $resource->delete();

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
