<?php

use App\Http\Controllers\Admin\SidebarController;
use Illuminate\Support\Facades\Route;

Route::get('sidebar', [SidebarController::class, 'getIndex'])->name('sidebar.view')->middleware('permission:admin.sidebar.view');
Route::get('sidebar/add', [SidebarController::class, 'getAdd'])->name('sidebar.add')->middleware('permission:admin.sidebar.add');
Route::post('sidebar/add', [SidebarController::class, 'postAdd'])->name('sidebar.add.submit')->middleware('permission:admin.sidebar.add');
Route::get('sidebar/edit/{id}', [SidebarController::class, 'getEdit'])->name('sidebar.edit')->middleware('permission:admin.sidebar.edit');
Route::post('sidebar/edit/{id}', [SidebarController::class, 'postEdit'])->name('sidebar.edit.submit')->middleware('permission:admin.sidebar.edit');
Route::post('sidebar/status', [SidebarController::class, 'postStatus'])->name('sidebar.status')->middleware('permission:admin.sidebar.status');
Route::post('sidebar/delete', [SidebarController::class, 'postDelete'])->name('sidebar.delete')->middleware('permission:admin.sidebar.delete');
