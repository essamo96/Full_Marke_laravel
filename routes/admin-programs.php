<?php

use App\Http\Controllers\Admin\ProgramsController;
use Illuminate\Support\Facades\Route;

Route::get('programs', [ProgramsController::class, 'getIndex'])->name('programs.view')->middleware('permission:admin.programs.view');
Route::get('programs/add', [ProgramsController::class, 'getAdd'])->name('programs.add')->middleware('permission:admin.programs.add');
Route::post('programs/add', [ProgramsController::class, 'postAdd'])->name('programs.add.submit')->middleware('permission:admin.programs.add');
Route::get('programs/edit/{id}', [ProgramsController::class, 'getEdit'])->name('programs.edit')->middleware('permission:admin.programs.edit');
Route::post('programs/edit/{id}', [ProgramsController::class, 'postEdit'])->name('programs.edit.submit')->middleware('permission:admin.programs.edit');
Route::post('programs/status', [ProgramsController::class, 'postStatus'])->name('programs.status')->middleware('permission:admin.programs.status');
Route::post('programs/delete', [ProgramsController::class, 'postDelete'])->name('programs.delete')->middleware('permission:admin.programs.delete');
