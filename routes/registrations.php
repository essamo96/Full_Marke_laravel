<?php

use App\Http\Controllers\Admin\RegistrationsController;
use Illuminate\Support\Facades\Route;

Route::get('registrations', [RegistrationsController::class, 'getIndex'])->name('registrations.view')->middleware('permission:admin.registrations.view');
Route::get('registrations/list', [RegistrationsController::class, 'getList'])->name('registrations.list')->middleware('permission:admin.registrations.view');
Route::get('registrations/export', [RegistrationsController::class, 'exportExcel'])->name('registrations.export')->middleware('permission:admin.registrations.view');
Route::post('registrations/delete', [RegistrationsController::class, 'delete'])->name('registrations.delete')->middleware('permission:admin.registrations.delete');
Route::post('registrations/status', [RegistrationsController::class, 'status'])->name('registrations.status')->middleware('permission:admin.registrations.edit');
