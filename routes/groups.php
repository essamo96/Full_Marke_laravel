<?php

use App\Http\Controllers\Admin\GroupsController;
use Illuminate\Support\Facades\Route;

// Global routes for the sidebar
Route::get('groups', [GroupsController::class, 'getIndex'])->name('groups.view')->middleware('permission:admin.groups.view');
Route::get('groups/list', [GroupsController::class, 'getList'])->name('groups.list')->middleware('permission:admin.groups.view');
Route::get('groups/add', [GroupsController::class, 'getAdd'])->name('groups.add.global')->middleware('permission:admin.groups.add');
Route::post('groups/add', [GroupsController::class, 'postAdd'])->name('groups.add.global.submit')->middleware('permission:admin.groups.add');

Route::prefix('subjects/{subject}')->group(function () {
    Route::get('groups', [GroupsController::class, 'getIndex'])->name('subjects.groups.view')->middleware('permission:admin.groups.view');
    Route::get('groups/list', [GroupsController::class, 'getList'])->name('subjects.groups.list')->middleware('permission:admin.groups.view');
    Route::get('groups/add', [GroupsController::class, 'getAdd'])->name('groups.add')->middleware('permission:admin.groups.add');
    Route::post('groups/add', [GroupsController::class, 'postAdd'])->name('groups.add.submit')->middleware('permission:admin.groups.add');
    Route::get('groups/edit/{id}', [GroupsController::class, 'getEdit'])->name('groups.edit')->middleware('permission:admin.groups.edit');
    Route::post('groups/edit/{id}', [GroupsController::class, 'postEdit'])->name('groups.edit.submit')->middleware('permission:admin.groups.edit');
});

Route::post('groups/status', [GroupsController::class, 'postStatus'])->name('groups.status')->middleware('permission:admin.groups.status');
Route::post('groups/delete', [GroupsController::class, 'postDelete'])->name('groups.delete')->middleware('permission:admin.groups.delete');

Route::get('groups/{id}/students', [GroupsController::class, 'getStudents'])->name('groups.students')->middleware('permission:admin.groups.view');
Route::get('groups/{id}/students/list', [GroupsController::class, 'getStudentsList'])->name('groups.students.list')->middleware('permission:admin.groups.view');

Route::get('groups/{id}/details', [GroupsController::class, 'getDetails'])->name('groups.details')->middleware('permission:admin.groups.view');
Route::post('groups/generate-code', [GroupsController::class, 'postGenerateCode'])->name('groups.generate_code')->middleware('permission:admin.groups.generate_code');

Route::get('groups/students/transfer-options/{registrationId}', [GroupsController::class, 'getTransferOptions'])->name('groups.students.transfer-options')->middleware('permission:admin.groups.view');
Route::post('groups/students/transfer', [GroupsController::class, 'postTransferStudent'])->name('groups.students.transfer')->middleware('permission:admin.groups.edit');
Route::post('groups/students/remove', [GroupsController::class, 'postRemoveFromGroup'])->name('groups.students.remove')->middleware('permission:admin.groups.edit');
