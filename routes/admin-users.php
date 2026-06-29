<?php

use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('users', [UsersController::class, 'getIndex'])->name('users.view')->middleware('permission:admin.users.view');
Route::get('users/add', [UsersController::class, 'getAdd'])->name('users.add')->middleware('permission:admin.users.add');
Route::post('users/add', [UsersController::class, 'postAdd'])->name('users.add.submit')->middleware('permission:admin.users.add');
Route::get('users/edit/{id}', [UsersController::class, 'getEdit'])->name('users.edit')->middleware('permission:admin.users.edit');
Route::post('users/edit/{id}', [UsersController::class, 'postEdit'])->name('users.edit.submit')->middleware('permission:admin.users.edit');
Route::post('users/status', [UsersController::class, 'postStatus'])->name('users.status')->middleware('permission:admin.users.status');
Route::post('users/delete', [UsersController::class, 'postDelete'])->name('users.delete')->middleware('permission:admin.users.delete');
