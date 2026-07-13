<?php

use App\Http\Controllers\Admin\SubjectsController;
use Illuminate\Support\Facades\Route;

// Global routes for the sidebar
Route::get('subjects', [SubjectsController::class, 'getIndex'])->name('subjects.view')->middleware('permission:admin.subjects.view');
Route::get('subjects/list', [SubjectsController::class, 'getList'])->name('subjects.list')->middleware('permission:admin.subjects.view');
Route::get('subjects/add', [SubjectsController::class, 'getAdd'])->name('subjects.add.global')->middleware('permission:admin.subjects.add');
Route::post('subjects/add', [SubjectsController::class, 'postAdd'])->name('subjects.add.global.submit')->middleware('permission:admin.subjects.add');

Route::prefix('programs/{program}')->group(function () {
    Route::get('subjects', [SubjectsController::class, 'getIndex'])->name('programs.subjects.view')->middleware('permission:admin.subjects.view');
    Route::get('subjects/list', [SubjectsController::class, 'getList'])->name('programs.subjects.list')->middleware('permission:admin.subjects.view');
    Route::get('subjects/add', [SubjectsController::class, 'getAdd'])->name('subjects.add')->middleware('permission:admin.subjects.add');
    Route::post('subjects/add', [SubjectsController::class, 'postAdd'])->name('subjects.add.submit')->middleware('permission:admin.subjects.add');
    Route::get('subjects/edit/{id}', [SubjectsController::class, 'getEdit'])->name('subjects.edit')->middleware('permission:admin.subjects.edit');
    Route::post('subjects/edit/{id}', [SubjectsController::class, 'postEdit'])->name('subjects.edit.submit')->middleware('permission:admin.subjects.edit');
});

Route::post('subjects/status', [SubjectsController::class, 'postStatus'])->name('subjects.status')->middleware('permission:admin.subjects.status');
Route::post('subjects/delete', [SubjectsController::class, 'postDelete'])->name('subjects.delete')->middleware('permission:admin.subjects.delete');
