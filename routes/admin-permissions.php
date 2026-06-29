<?php

use App\Http\Controllers\Admin\PermissionsController;
use Illuminate\Support\Facades\Route;

Route::get('permissions', [PermissionsController::class, 'getIndex'])->name('permissions.view')->middleware('permission:admin.permissions.view');
Route::get('permissions/add', [PermissionsController::class, 'getAdd'])->name('permissions.add')->middleware('permission:admin.permissions.add');
Route::post('permissions/add', [PermissionsController::class, 'postAdd'])->name('permissions.add.submit')->middleware('permission:admin.permissions.add');
Route::get('permissions/edit/{id}', [PermissionsController::class, 'getEdit'])->name('permissions.edit')->middleware('permission:admin.permissions.edit');
Route::post('permissions/edit/{id}', [PermissionsController::class, 'postEdit'])->name('permissions.edit.submit')->middleware('permission:admin.permissions.edit');
Route::post('permissions/delete', [PermissionsController::class, 'postDelete'])->name('permissions.delete')->middleware('permission:admin.permissions.delete');
