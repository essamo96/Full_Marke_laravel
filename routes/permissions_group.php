<?php

use App\Http\Controllers\Admin\SidebarController;
use Illuminate\Support\Facades\Route;

Route::get('permissions_group', [SidebarController::class, 'getIndex'])->name('permissions_group.view')->middleware('permission:admin.permissions_group.view');
Route::get('permissions_group/list', [SidebarController::class, 'getList'])->name('permissions_group.list')->middleware('permission:admin.permissions_group.view');
Route::get('permissions_group/add', [SidebarController::class, 'getAdd'])->name('permissions_group.add')->middleware('permission:admin.permissions_group.add');
Route::post('permissions_group/add', [SidebarController::class, 'postAdd'])->name('permissions_group.add.submit')->middleware('permission:admin.permissions_group.add');
Route::get('permissions_group/edit/{id}', [SidebarController::class, 'getEdit'])->name('permissions_group.edit')->middleware('permission:admin.permissions_group.edit');
Route::post('permissions_group/edit/{id}', [SidebarController::class, 'postEdit'])->name('permissions_group.edit.submit')->middleware('permission:admin.permissions_group.edit');
Route::post('permissions_group/status', [SidebarController::class, 'postStatus'])->name('permissions_group.status')->middleware('permission:admin.permissions_group.status');
Route::post('permissions_group/delete', [SidebarController::class, 'postDelete'])->name('permissions_group.delete')->middleware('permission:admin.permissions_group.delete');

// Dummy routes for missing methods
Route::post('permissions_group/reorder', [SidebarController::class, 'postReorder'])->name('permissions_group.reorder')->middleware('permission:admin.permissions_group.edit');
Route::post('permissions_group/update-color', [SidebarController::class, 'postUpdateColor'])->name('permissions_group.update_color')->middleware('permission:admin.permissions_group.edit');
Route::post('permissions_group/bulk-color', [SidebarController::class, 'postBulkColor'])->name('permissions_group.bulk_color')->middleware('permission:admin.permissions_group.edit');
