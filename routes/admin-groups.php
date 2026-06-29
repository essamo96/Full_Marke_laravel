<?php

use App\Http\Controllers\Admin\GroupsController;
use Illuminate\Support\Facades\Route;

Route::get('groups', [GroupsController::class, 'getIndex'])->name('groups.view')->middleware('permission:admin.groups.view');
Route::get('groups/add', [GroupsController::class, 'getAdd'])->name('groups.add')->middleware('permission:admin.groups.add');
Route::post('groups/add', [GroupsController::class, 'postAdd'])->name('groups.add.submit')->middleware('permission:admin.groups.add');
Route::get('groups/edit/{id}', [GroupsController::class, 'getEdit'])->name('groups.edit')->middleware('permission:admin.groups.edit');
Route::post('groups/edit/{id}', [GroupsController::class, 'postEdit'])->name('groups.edit.submit')->middleware('permission:admin.groups.edit');
Route::post('groups/status', [GroupsController::class, 'postStatus'])->name('groups.status')->middleware('permission:admin.groups.status');
Route::post('groups/delete', [GroupsController::class, 'postDelete'])->name('groups.delete')->middleware('permission:admin.groups.delete');
