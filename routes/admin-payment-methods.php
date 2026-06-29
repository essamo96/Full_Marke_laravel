<?php

use App\Http\Controllers\Admin\PaymentMethodsController;
use Illuminate\Support\Facades\Route;

Route::get('payment-methods', [PaymentMethodsController::class, 'getIndex'])->name('payment_methods.view')->middleware('permission:admin.payment_methods.view');
Route::get('payment-methods/add', [PaymentMethodsController::class, 'getAdd'])->name('payment_methods.add')->middleware('permission:admin.payment_methods.add');
Route::post('payment-methods/add', [PaymentMethodsController::class, 'postAdd'])->name('payment_methods.add.submit')->middleware('permission:admin.payment_methods.add');
Route::get('payment-methods/edit/{id}', [PaymentMethodsController::class, 'getEdit'])->name('payment_methods.edit')->middleware('permission:admin.payment_methods.edit');
Route::post('payment-methods/edit/{id}', [PaymentMethodsController::class, 'postEdit'])->name('payment_methods.edit.submit')->middleware('permission:admin.payment_methods.edit');
Route::post('payment-methods/status', [PaymentMethodsController::class, 'postStatus'])->name('payment_methods.status')->middleware('permission:admin.payment_methods.status');
Route::post('payment-methods/delete', [PaymentMethodsController::class, 'postDelete'])->name('payment_methods.delete')->middleware('permission:admin.payment_methods.delete');
