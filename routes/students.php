<?php

use App\Http\Controllers\Admin\StudentsController;
use Illuminate\Support\Facades\Route;

Route::get('students', [StudentsController::class, 'getIndex'])->name('students.view')->middleware('permission:admin.students.view');
Route::get('students/list', [StudentsController::class, 'getList'])->name('students.list')->middleware('permission:admin.students.view');
Route::get('students/add', [StudentsController::class, 'getAdd'])->name('students.add')->middleware('permission:admin.students.add');
Route::post('students/add', [StudentsController::class, 'postAdd'])->name('students.add.submit')->middleware('permission:admin.students.add');
Route::get('students/edit/{id}', [StudentsController::class, 'getEdit'])->name('students.edit')->middleware('permission:admin.students.edit');
Route::post('students/edit/{id}', [StudentsController::class, 'postEdit'])->name('students.edit.submit')->middleware('permission:admin.students.edit');
Route::get('students/show/{id}', [StudentsController::class, 'getView'])->name('students.show')->middleware('permission:admin.students.view');
Route::get('students/results/{id}', [StudentsController::class, 'getResults'])->name('students.results')->middleware('permission:admin.students.results');
Route::post('students/status', [StudentsController::class, 'postStatus'])->name('students.status')->middleware('permission:admin.students.status');
Route::post('students/delete', [StudentsController::class, 'postDelete'])->name('students.delete')->middleware('permission:admin.students.delete');
Route::get('students/export', [StudentsController::class, 'exportExcel'])->name('students.export')->middleware('permission:admin.students.view');
Route::get('students/{id}/invoices', [StudentsController::class, 'getInvoices'])->name('students.invoices')->middleware('permission:admin.students.view');
