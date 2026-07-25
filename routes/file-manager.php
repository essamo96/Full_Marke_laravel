<?php

use App\Http\Controllers\Admin\FileManagerController;
use Illuminate\Support\Facades\Route;

Route::get('file-manager', [FileManagerController::class, 'index'])->name('file_manager.view')->middleware('permission:admin.file_manager.view');
Route::post('file-manager/upload', [FileManagerController::class, 'upload'])->name('file_manager.upload')->middleware('permission:admin.file_manager.add');
Route::post('file-manager/create-folder', [FileManagerController::class, 'createFolder'])->name('file_manager.create_folder')->middleware('permission:admin.file_manager.add');
Route::post('file-manager/rename', [FileManagerController::class, 'rename'])->name('file_manager.rename')->middleware('permission:admin.file_manager.edit');
Route::post('file-manager/delete', [FileManagerController::class, 'delete'])->name('file_manager.delete')->middleware('permission:admin.file_manager.delete');
