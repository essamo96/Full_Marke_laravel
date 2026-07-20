<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Str;
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
        // Note: this validates one chunk at a time, not the whole file — a mid-file
        // chunk's raw bytes won't carry the format's magic header, so we check the
        // (client-supplied) extension only and never content-sniff with `mimes`.
        $request->validate([
            'file' => 'required|file|extensions:pdf,doc,docx,ppt,pptx,xls,xlsx,mp4,mov,avi,webm,mkv',
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

        $targetDir = storage_path('app/private/protected_videos/incoming');
        FileFacade::ensureDirectoryExists($targetDir);

        $uploadedFile->move($targetDir, $finalName);
        @unlink($uploadedFile->getPathname());

        return response()->json([
            'done' => 100,
            'upload_id' => $uploadId,
            'path' => 'incoming/'.$finalName,
            'original_filename' => $originalName,
        ]);
    }
}
