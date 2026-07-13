<?php

use App\Http\Controllers\Admin\RolesController;
use Illuminate\Support\Facades\Route;

Route::get('role', [RolesController::class, 'getIndex'])->name('role.view')->middleware('permission:admin.role.view');
Route::get('role/list', [RolesController::class, 'getList'])->name('role.list')->middleware('permission:admin.role.view');
Route::get('role/add', [RolesController::class, 'getAdd'])->name('role.add')->middleware('permission:admin.role.add');
Route::post('role/add', [RolesController::class, 'postAdd'])->name('role.add.submit')->middleware('permission:admin.role.add');
Route::get('role/edit/{id}', [RolesController::class, 'getEdit'])->name('role.edit')->middleware('permission:admin.role.edit');
Route::post('role/edit/{id}', [RolesController::class, 'postEdit'])->name('role.edit.submit')->middleware('permission:admin.role.edit');
Route::post('role/status', [RolesController::class, 'postStatus'])->name('role.status')->middleware('permission:admin.role.status');
Route::post('role/delete', [RolesController::class, 'postDelete'])->name('role.delete')->middleware('permission:admin.role.delete');
Route::get('role/{id}/permissions', [RolesController::class, 'getPermissions'])->name('role.permissions')->middleware('permission:admin.role.permissions');
Route::post('role/{id}/permissions', [RolesController::class, 'postPermissions'])->name('role.permissions.submit')->middleware('permission:admin.role.permissions');
