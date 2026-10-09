<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExamController;

Route::prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [ExamController::class, 'index'])->name('view');
    Route::get('/create', [ExamController::class, 'create'])->name('create');
    Route::post('/', [ExamController::class, 'store'])->name('store');
    Route::get('/{exam}/edit', [ExamController::class, 'edit'])->name('edit');
    Route::put('/{exam}', [ExamController::class, 'update'])->name('update');
    Route::get('/{exam}/preview', [ExamController::class, 'preview'])->name('preview');
    Route::get('/{exam}/blank-pdf', [ExamController::class, 'blankPdf'])->name('blank-pdf');
    Route::delete('/{exam}', [ExamController::class, 'destroy'])->name('destroy');
    Route::get('/{exam}/results', [ExamController::class, 'results'])->name('results');
    Route::post('/{exam}/regrade', [ExamController::class, 'regrade'])->name('regrade');
    Route::get('/grades/{grade}/answers', [ExamController::class, 'gradeAnswers'])->name('grades.answers');
    Route::post('/grades/{grade}/approve', [ExamController::class, 'approveGrade'])->name('grades.approve');
    Route::post('/{exam}/reorder-questions', [ExamController::class, 'reorderQuestions'])->name('reorder-questions');
    Route::get('/ajax/subject/{subject}/groups', [ExamController::class, 'getSubjectGroups'])->name('ajax.groups');
    Route::get('/ajax/group/{groupId}/students', [ExamController::class, 'getGroupStudents'])->name('ajax.students');
    Route::get('/ajax/groups/students', [ExamController::class, 'getGroupsStudents'])->name('ajax.groups-students');
});
