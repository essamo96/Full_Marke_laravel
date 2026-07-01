<?php

use App\Http\Controllers\Admin\StudyBranchesController;
use Illuminate\Support\Facades\Route;

Route::get('study-branches', [StudyBranchesController::class, 'getIndex'])->name('study_branches.view')->middleware('permission:admin.study_branches.view');
Route::get('study-branches/list', [StudyBranchesController::class, 'getList'])->name('study_branches.list')->middleware('permission:admin.study_branches.view');
Route::get('study-branches/add', [StudyBranchesController::class, 'getAdd'])->name('study_branches.add')->middleware('permission:admin.study_branches.add');
Route::post('study-branches/add', [StudyBranchesController::class, 'postAdd'])->name('study_branches.add.submit')->middleware('permission:admin.study_branches.add');
Route::get('study-branches/edit/{id}', [StudyBranchesController::class, 'getEdit'])->name('study_branches.edit')->middleware('permission:admin.study_branches.edit');
Route::post('study-branches/edit/{id}', [StudyBranchesController::class, 'postEdit'])->name('study_branches.edit.submit')->middleware('permission:admin.study_branches.edit');
Route::post('study-branches/status', [StudyBranchesController::class, 'postStatus'])->name('study_branches.status')->middleware('permission:admin.study_branches.status');
Route::post('study-branches/delete', [StudyBranchesController::class, 'postDelete'])->name('study_branches.delete')->middleware('permission:admin.study_branches.delete');
