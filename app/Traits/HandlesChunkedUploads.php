<?php

namespace App\Traits;

use App\Support\Library\UploadLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

trait HandlesChunkedUploads
{
    /**
     * Receives one chunk of a resumable.js upload. When the last chunk
     * arrives, the package merges every part into the full file and we move
     * it into the private "incoming" area, ready to be attached to a lesson
     * resource.
     */
    public function receiveChunkedUpload(Request $request)
    {
        $maxBytes = UploadLimits::maxFileBytes();

        // refuse a too-big file on its first chunk, not after gigabytes went through
        if ((int) $request->input('resumableTotalSize') > $maxBytes) {
            throw ValidationException::withMessages([
                'file' => 'حجم الملف أكبر من الحد المسموح ('.UploadLimits::human($maxBytes).').',
            ]);
        }

        // Note: this validates one chunk at a time, not the whole file — a mid-file
        // chunk's raw bytes won't carry the format's magic header, so we check the
        // (client-supplied) extension only and never content-sniff with `mimes`.
        $request->validate([
            'file' => 'required|file|extensions:pdf,doc,docx,ppt,pptx,xls,xlsx,mp4,mov,avi,webm,mkv',
        ], [
            // PHP drops a part bigger than upload_max_filesize: the file then "failed to upload"
            'file.required' => 'لم يصل جزء الملف إلى السيرفر؛ حجم الجزء أكبر من حدود السيرفر ('.UploadLimits::human(UploadLimits::serverRequestBytes()).'). حدّث الصفحة وأعد الرفع.',
            'file.uploaded' => 'لم يصل جزء الملف إلى السيرفر؛ حجم الجزء أكبر من حدود السيرفر ('.UploadLimits::human(UploadLimits::serverRequestBytes()).'). حدّث الصفحة وأعد الرفع.',
            'file.extensions' => 'نوع الملف غير مدعوم.',
        ]);

        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if (! $receiver->isUploaded()) {
            throw new UploadMissingFileException;
        }

        $save = $receiver->receive();

        if (! $save->isFinished()) {
            $handler = $save->handler();

            return response()->json([
                'done' => $handler->getPercentageDone(),
            ]);
        }

        $uploadedFile = $save->getFile();

        $originalName = $request->input('resumableFilename', $uploadedFile->getClientOriginalName());
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION));
        $uploadId = (string) Str::uuid();
        $finalName = $uploadId.'.'.$extension;

        $directory = rtrim((string) config('resource_library.incoming_directory', 'incoming'), '/');
        $targetDir = Storage::disk(config('resource_library.disk', 'protected_videos'))->path($directory);
        FileFacade::ensureDirectoryExists($targetDir);

        $uploadedFile->move($targetDir, $finalName);
        @unlink($uploadedFile->getPathname());

        return response()->json([
            'done' => 100,
            'upload_id' => $uploadId,
            'path' => $directory.'/'.$finalName,
            'original_filename' => $originalName,
        ]);
    }
}
