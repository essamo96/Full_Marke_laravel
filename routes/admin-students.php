<?php

use App\Http\Controllers\Admin\StudentsController;
use Illuminate\Support\Facades\Route;

Route::get('students', [StudentsController::class, 'getIndex'])->name('students.view')->middleware('permission:admin.students.view');
Route::get('students/{id}', [StudentsController::class, 'getView'])->name('students.show')->middleware('permission:admin.students.view');
Route::post('students/status', [StudentsController::class, 'postStatus'])->name('students.status')->middleware('permission:admin.students.status');
Route::post('students/delete', [StudentsController::class, 'postDelete'])->name('students.delete')->middleware('permission:admin.students.delete');
