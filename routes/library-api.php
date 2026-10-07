<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Resource library JSON API
|--------------------------------------------------------------------------
|
| ONE set of routes, registered twice: under /admin/library for admins and
| /teacher/library for teachers. Both are served by the same abstract
| controller (App\Http\Controllers\Library\LibraryApiController), so there is
| nothing an admin can do that a teacher cannot do inside their own groups.
|
| The caller passes the concrete controller class:
|
|     (require __DIR__.'/library-api.php')(\App\Http\Controllers\Teacher\LibraryApiController::class);
|
| {type} is resources | units | lessons; {key} is the encrypted route key of the
| row, {subject} the encrypted key of the subject.
|
*/

return function (string $controller): void {
    $types = 'resources|units|lessons';
    $containers = 'units|lessons';

    // ---- session keep-alive / fresh CSRF token (long chunked uploads)
    Route::get('ping', [$controller, 'ping'])->name('ping');

    // ---- reading
    Route::get('rows', [$controller, 'rows'])->name('rows');
    Route::get('subjects/{subject}/tree', [$controller, 'tree'])->name('tree');
    Route::get('subjects/{subject}/students', [$controller, 'subjectStudents'])->name('subject.students');
    Route::get('subjects/{subject}/trash', [$controller, 'trash'])->name('trash');
    Route::get('subjects/{subject}/activity', [$controller, 'activity'])->name('activity');
    Route::get('subjects/{subject}/preview', [$controller, 'preview'])->name('preview');
    Route::get('subjects/{subject}/health', [$controller, 'health'])->name('health');
    Route::post('subjects/{subject}/health/fix', [$controller, 'fixHealth'])->name('health.fix');
    Route::get('explain', [$controller, 'explain'])->name('explain');

    // ---- bulk / ordering
    Route::post('subjects/{subject}/bulk/active', [$controller, 'bulkActive'])->name('bulk.active');
    Route::post('subjects/{subject}/reorder/resources', [$controller, 'reorderResources'])->name('reorder.resources');
    Route::post('subjects/{subject}/reorder/{type}', [$controller, 'reorderContainers'])->where('type', $containers)->name('reorder.containers');

    // ---- undo
    Route::post('undo', [$controller, 'undo'])->name('undo');

    // ---- creating & editing (one upload, shown anywhere)
    Route::post('subjects/{subject}/units', [$controller, 'storeUnit'])->name('units.store');
    Route::post('subjects/{subject}/resources', [$controller, 'storeResource'])->name('resources.store');
    Route::post('units/{key}/lessons', [$controller, 'storeLesson'])->name('lessons.store');
    Route::post('upload-chunk', [$controller, 'uploadChunk'])->name('upload-chunk');
    Route::delete('upload-chunk', [$controller, 'discardUpload'])->name('upload-chunk.discard');

    // ---- uploads parked in incoming/ that no resource uses (admin: all; teacher: their own)
    Route::get('uploads/orphans', [$controller, 'orphanUploads'])->name('uploads.orphans');
    Route::get('uploads/orphans/file', [$controller, 'orphanUploadFile'])->name('uploads.orphans.file');
    Route::post('uploads/orphans/relink', [$controller, 'relinkOrphanUpload'])->name('uploads.orphans.relink');
    Route::delete('uploads/orphans', [$controller, 'discardOrphanUpload'])->name('uploads.orphans.discard');
    Route::get('resources/{key}/file', [$controller, 'file'])->name('resources.file');
    Route::put('{type}/{key}', [$controller, 'update'])->where('type', $types)->name('update');

    // ---- resources only
    Route::post('resources/{key}/shared', [$controller, 'setShared'])->name('resources.shared');
    Route::post('resources/{key}/groups/{group}/pause', [$controller, 'pauseGroup'])->whereNumber('group')->name('resources.group.pause');
    Route::post('resources/{key}/placements', [$controller, 'attachPlacements'])->name('resources.placements.attach');
    Route::delete('resources/{key}/placements', [$controller, 'detachPlacements'])->name('resources.placements.detach');
    Route::post('resources/{key}/placements/active', [$controller, 'setPlacementActive'])->name('resources.placements.active');
    Route::delete('resources/{key}/force', [$controller, 'forceDestroy'])->name('resources.force');

    // ---- resources, units and lessons
    Route::get('{type}/{key}/scope', [$controller, 'scope'])->where('type', $types)->name('scope');
    Route::get('{type}/{key}/students', [$controller, 'students'])->where('type', $types)->name('students');
    Route::post('{type}/{key}/exclusions', [$controller, 'setExclusion'])->where('type', $types)->name('exclusions');
    Route::post('{type}/{key}/active', [$controller, 'setActive'])->where('type', $types)->name('active');
    Route::post('{type}/{key}/groups', [$controller, 'attachGroups'])->where('type', $types)->name('groups.attach');
    Route::delete('{type}/{key}/groups', [$controller, 'detachGroups'])->where('type', $types)->name('groups.detach');
    Route::post('{type}/{key}/restore', [$controller, 'restore'])->where('type', $types)->name('restore');
    Route::delete('{type}/{key}', [$controller, 'destroy'])->where('type', $types)->name('destroy');
};
