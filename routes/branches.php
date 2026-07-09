<?php

use App\Http\Controllers\Admin\BranchesController;
use Illuminate\Support\Facades\Route;

Route::get('branches', [BranchesController::class, 'getIndex'])->name('branches.view')->middleware('permission:admin.branches.view');
Route::get('branches/list', [BranchesController::class, 'getList'])->name('branches.list')->middleware('permission:admin.branches.view');
Route::get('branches/add', [BranchesController::class, 'getAdd'])->name('branches.add')->middleware('permission:admin.branches.add');
Route::post('branches/add', [BranchesController::class, 'postAdd'])->name('branches.add.submit')->middleware('permission:admin.branches.add');
Route::get('branches/edit/{id}', [BranchesController::class, 'getEdit'])->name('branches.edit')->middleware('permission:admin.branches.edit');
Route::post('branches/edit/{id}', [BranchesController::class, 'postEdit'])->name('branches.edit.submit')->middleware('permission:admin.branches.edit');
Route::post('branches/status', [BranchesController::class, 'postStatus'])->name('branches.status')->middleware('permission:admin.branches.status');
Route::post('branches/delete', [BranchesController::class, 'postDelete'])->name('branches.delete')->middleware('permission:admin.branches.delete');
