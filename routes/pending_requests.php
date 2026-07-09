<?php

use App\Http\Controllers\Admin\PendingRequestsController;
use Illuminate\Support\Facades\Route;

Route::get('pending_requests', [PendingRequestsController::class, 'getIndex'])->name('pending_requests.view')->middleware('permission:admin.pending_requests.view');
Route::get('pending_requests/list', [PendingRequestsController::class, 'getList'])->name('pending_requests.list')->middleware('permission:admin.pending_requests.view');
Route::post('pending_requests/status', [PendingRequestsController::class, 'postStatus'])->name('pending_requests.status')->middleware('permission:admin.pending_requests.status');
Route::post('pending_requests/delete', [PendingRequestsController::class, 'postDelete'])->name('pending_requests.delete')->middleware('permission:admin.pending_requests.delete');
