<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'file_manager';
    }

    private function normalize(?string $path): string
    {
        $path = trim((string) $path, '/');
        // Guard against path traversal outside the disk root.
        $path = str_replace(['..\\', '../'], '', $path);

        return $path;
    }

    public function index(Request $request)
    {
        $currentFolder = $this->normalize($request->get('folder', ''));

        $disk = Storage::disk('public');
        if ($currentFolder !== '' && ! $disk->exists($currentFolder)) {
            $currentFolder = '';
        }

        $directories = collect($disk->directories($currentFolder))->map(function ($dir) use ($disk) {
            return [
                'name' => basename($dir),
                'path' => $dir,
                'count' => count($disk->files($dir)) + count($disk->directories($dir)),
                'last_modified' => date('Y-m-d H:i', $disk->lastModified($dir)),
            ];
        })->sortBy('name')->values();

        $files = collect($disk->files($currentFolder))->map(function ($file) use ($disk) {
            return [
                'name' => basename($file),
                'path' => $file,
                'url' => asset('storage/' . $file),
                'size' => $this->formatSizeUnits($disk->size($file)),
                'last_modified' => date('Y-m-d H:i', $disk->lastModified($file)),
                'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
            ];
        })->sortBy('name')->values();

        $breadcrumbs = [['name' => 'الجذر (root)', 'path' => '']];
        $path = '';
        foreach (array_filter(explode('/', $currentFolder)) as $part) {
            $path .= ($path === '' ? '' : '/') . $part;
            $breadcrumbs[] = ['name' => $part, 'path' => $path];
        }

        return view('admin.file_manager.index', self::$data + compact('directories', 'files', 'currentFolder', 'breadcrumbs'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB
            'folder' => 'nullable|string',
        ]);

        $folder = $this->normalize($request->post('folder'));
        $file = $request->file('file');

        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'mp3', 'mp4', 'wav'];
        if (! in_array($extension, $allowed)) {
            return response()->json(['error' => 'نوع الملف غير مسموح به.'], 422);
        }

        $cleanName = preg_replace('/[\/\\\\?%*:|"<>]/', '_', $file->getClientOriginalName());
        $name = time() . '_' . $cleanName;
        $path = $file->storeAs($folder, $name, 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
            'path' => $path,
            'name' => $name,
        ]);
    }

    public function createFolder(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $folder = $this->normalize($request->post('folder'));
        $cleanName = preg_replace('/[\/\\\\?%*:|"<>]/', '_', $request->post('name'));

        Storage::disk('public')->makeDirectory(trim($folder . '/' . $cleanName, '/'));

        return response()->json(['success' => true, 'message' => 'تم إنشاء المجلد بنجاح.']);
    }

    public function rename(Request $request)
    {
        $request->validate(['path' => 'required|string', 'name' => 'required|string|max:255', 'type' => 'required|in:file,folder']);

        $path = $this->normalize($request->post('path'));
        $cleanName = preg_replace('/[\/\\\\?%*:|"<>]/', '_', $request->post('name'));
        $directory = dirname($path);
        $directory = $directory === '.' ? '' : $directory;

        if ($request->post('type') === 'file') {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if ($ext && ! str_ends_with($cleanName, '.' . $ext)) {
                $cleanName .= '.' . $ext;
            }
        }

        Storage::disk('public')->move($path, trim($directory . '/' . $cleanName, '/'));

        return response()->json(['success' => true, 'message' => 'تمت إعادة التسمية بنجاح.']);
    }

    public function delete(Request $request)
    {
        $request->validate(['path' => 'required|string', 'type' => 'required|in:file,folder']);

        $path = $this->normalize($request->post('path'));
        $disk = Storage::disk('public');

        if ($request->post('type') === 'folder') {
            $disk->deleteDirectory($path);
        } else {
            $disk->delete($path);
        }

        return response()->json(['success' => true, 'message' => 'تم الحذف بنجاح.']);
    }

    private function formatSizeUnits(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
