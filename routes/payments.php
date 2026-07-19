<?php

use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('payments', [PaymentsController::class, 'getIndex'])->name('payments.view')->middleware('permission:admin.payments.view');
Route::get('payments/list', [PaymentsController::class, 'getList'])->name('payments.list')->middleware('permission:admin.payments.view');
Route::get('payments/export', [PaymentsController::class, 'exportExcel'])->name('payments.export')->middleware('permission:admin.payments.view');
Route::post('payments/delete', [PaymentsController::class, 'delete'])->name('payments.delete')->middleware('permission:admin.payments.delete');
Route::post('payments/status', [PaymentsController::class, 'status'])->name('payments.status')->middleware('permission:admin.payments.edit');

Route::get('payments/{payment}/invoice', [PaymentsController::class, 'invoice'])->name('payments.invoice');

Route::get('payments/{payment}/receipt', [ReceiptController::class, 'show'])
    ->name('payments.receipt')
    ->middleware('signed');
