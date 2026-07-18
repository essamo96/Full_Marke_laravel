<?php
use App\Http\Controllers\Admin\EnrolmentController;
use Illuminate\Support\Facades\Route;

Route::get('/enrolments', [EnrolmentController::class, 'index'])->name('enrolments.index');
Route::get('/enrolments/students', [EnrolmentController::class, 'getStudents'])->name('enrolments.students');
Route::post('/enrolments/enroll', [EnrolmentController::class, 'enroll'])->name('enrolments.enroll');
