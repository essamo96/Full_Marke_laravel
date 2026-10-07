<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Undo
    |--------------------------------------------------------------------------
    |
    | Every change made through the resource library is journaled with a snapshot of
    | what it touched. The toast shows the "Undo" button for `toast_seconds`; the
    | server keeps accepting the undo token for `window_seconds`, so a slow
    | connection or a late click does not turn a harmless mistake into data loss.
    |
    */
    'undo' => [
        'toast_seconds' => 5,
        'window_seconds' => 900,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bulk actions
    |--------------------------------------------------------------------------
    |
    | Upper bound of resources one bulk action (e.g. "stop every video") may touch.
    |
    */
    'max_bulk' => 500,

    /*
    |--------------------------------------------------------------------------
    | Resource types
    |--------------------------------------------------------------------------
    */
    'types' => [
        'video' => 'فيديو',
        'document' => 'ملف / PDF',
        'image' => 'صورة',
        'link' => 'رابط',
        'zoom' => 'Zoom',
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Files live on a private disk and are only ever streamed through the
    | authorised student/teacher/admin endpoints.
    |
    */
    'disk' => 'protected_videos',
    'directory' => 'resources',
    'incoming_directory' => 'incoming',

    /*
    |--------------------------------------------------------------------------
    | Chunked uploads
    |--------------------------------------------------------------------------
    |
    | Videos are sent in chunks of `chunk_bytes`. The size the browser really uses
    | is the smaller of this and what PHP accepts in one request (upload_max_filesize,
    | post_max_size) minus a safety margin - see App\Support\Library\UploadLimits.
    | The web server must accept a request body of at least one chunk too
    | (nginx: client_max_body_size).
    |
    | An upload that was never attached to a resource is kept `orphan_grace_hours`
    | so someone can still link or adopt it, then `library:cleanup-incoming` deletes it.
    |
    */
    'upload' => [
        'chunk_bytes' => (int) env('LIBRARY_UPLOAD_CHUNK_BYTES', 5 * 1024 * 1024),
        'max_video_bytes' => (int) env('LIBRARY_MAX_VIDEO_BYTES', 4 * 1024 * 1024 * 1024),
        'orphan_grace_hours' => (int) env('LIBRARY_ORPHAN_GRACE_HOURS', 168),
        'keepalive_seconds' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fingerprint
    |--------------------------------------------------------------------------
    |
    | Duplicate detection hashes the first and last `fingerprint_bytes` of a file plus
    | its size. Cheap even for multi-GB videos, and a collision only ever results in a
    | "possible duplicate" suggestion that a human confirms.
    |
    */
    'fingerprint_bytes' => 1048576,
];
