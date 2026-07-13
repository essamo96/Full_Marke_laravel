<?php

use App\Http\Controllers\Admin\SubjectResourcesController;
use Illuminate\Support\Facades\Route;

Route::get('subjects/{subject}/resources', [SubjectResourcesController::class, 'getIndex'])->name('subject_resources.view')->middleware('permission:admin.subjects.view');
Route::get('subjects/{subject}/resources/add', [SubjectResourcesController::class, 'getAdd'])->name('subject_resources.add')->middleware('permission:admin.subjects.edit');
Route::post('subjects/{subject}/resources/add', [SubjectResourcesController::class, 'postAdd'])->name('subject_resources.add.submit')->middleware('permission:admin.subjects.edit');
Route::get('subjects/{subject}/resources/edit/{id}', [SubjectResourcesController::class, 'getEdit'])->name('subject_resources.edit')->middleware('permission:admin.subjects.edit');
Route::post('subjects/{subject}/resources/edit/{id}', [SubjectResourcesController::class, 'postEdit'])->name('subject_resources.edit.submit')->middleware('permission:admin.subjects.edit');
Route::post('subjects/{subject}/resources/status', [SubjectResourcesController::class, 'postStatus'])->name('subject_resources.status')->middleware('permission:admin.subjects.edit');
Route::post('subjects/{subject}/resources/delete', [SubjectResourcesController::class, 'postDelete'])->name('subject_resources.delete')->middleware('permission:admin.subjects.edit');
