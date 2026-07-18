<?php

use App\Http\Controllers\Admin\TeachersController;
use Illuminate\Support\Facades\Route;

Route::get('teachers', [TeachersController::class, 'getIndex'])->name('teachers.view')->middleware('permission:admin.teachers.view');
Route::get('teachers/list', [TeachersController::class, 'getList'])->name('teachers.list')->middleware('permission:admin.teachers.view');
Route::get('teachers/add', [TeachersController::class, 'getAdd'])->name('teachers.add')->middleware('permission:admin.teachers.add');
Route::post('teachers/add', [TeachersController::class, 'postAdd'])->name('teachers.add.submit')->middleware('permission:admin.teachers.add');
Route::get('teachers/edit/{id}', [TeachersController::class, 'getEdit'])->name('teachers.edit')->middleware('permission:admin.teachers.edit');
Route::post('teachers/edit/{id}', [TeachersController::class, 'postEdit'])->name('teachers.edit.submit')->middleware('permission:admin.teachers.edit');
Route::post('teachers/status', [TeachersController::class, 'postStatus'])->name('teachers.status')->middleware('permission:admin.teachers.status');
Route::post('teachers/delete', [TeachersController::class, 'postDelete'])->name('teachers.delete')->middleware('permission:admin.teachers.delete');
Route::post('teachers/change-password', [TeachersController::class, 'postChangePassword'])->name('teachers.change_password')->middleware('permission:admin.teachers.change_password');
