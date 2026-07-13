<?php

use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Admin\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('payments', [PaymentsController::class, 'getIndex'])->name('payments.view')->middleware('permission:admin.payments.view');

Route::get('payments/{payment}/receipt', [ReceiptController::class, 'show'])
    ->name('payments.receipt')
    ->middleware('signed');
