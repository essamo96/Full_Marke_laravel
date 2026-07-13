<?php

use App\Http\Controllers\Admin\ApprovalsController;
use Illuminate\Support\Facades\Route;

Route::get('approvals', [ApprovalsController::class, 'getIndex'])->name('approvals.view')->middleware('permission:admin.approvals.view');
Route::post('approvals/confirm', [ApprovalsController::class, 'postConfirm'])->name('approvals.confirm')->middleware('permission:admin.approvals.edit');
Route::post('approvals/reject', [ApprovalsController::class, 'postReject'])->name('approvals.reject')->middleware('permission:admin.approvals.edit');
