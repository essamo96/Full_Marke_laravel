<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Traits\HandlesChunkedUploads;
use Illuminate\Http\Request;

class VideoChunkUploadController extends Controller
{
    use HandlesChunkedUploads;

    public function upload(Request $request)
    {
        return $this->receiveChunkedUpload($request);
    }
}
