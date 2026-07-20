<?php

use App\Http\Controllers\Teacher\Auth\LoginController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\ContentController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\ExamController;
use App\Http\Controllers\Teacher\GradingController;
use App\Http\Controllers\Teacher\GroupNoteController;
use App\Http\Controllers\Teacher\GroupsController;
use App\Http\Controllers\Teacher\NotificationsController;
use App\Http\Controllers\Teacher\ProfileController;
use App\Http\Controllers\Teacher\SubjectsController;
use App\Http\Controllers\Teacher\VideoChunkUploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('teacher')->name('teacher.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware('auth:teacher')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('subjects', [SubjectsController::class, 'index'])->name('subjects.index');
        Route::get('subjects/{subject}', [SubjectsController::class, 'show'])->name('subjects.show');

        Route::get('groups', [GroupsController::class, 'index'])->name('groups.index');
        Route::get('groups/{group}', [GroupsController::class, 'show'])->name('groups.show');
        Route::post('groups/{group}/notes', [GroupNoteController::class, 'store'])->name('group-notes.store');

        Route::get('attendance/{group}', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::post('attendance/{group}', [AttendanceController::class, 'store'])->name('attendance.store');

        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::put('exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
        Route::post('exams/{exam}/reorder-questions', [ExamController::class, 'reorderQuestions'])->name('exams.reorder-questions');
        Route::get('exams/ajax/subject/{subject}/groups', [ExamController::class, 'getSubjectGroups'])->name('exams.ajax.subject-groups');
        Route::get('exams/ajax/group/{groupId}/students', [ExamController::class, 'getGroupStudents'])->name('exams.ajax.group-students');

        Route::get('grading', [GradingController::class, 'index'])->name('grading.index');
        Route::get('grading/exams/{exam}', [GradingController::class, 'exam'])->name('grading.exam');
        Route::get('grading/{grade}', [GradingController::class, 'show'])->name('grading.show');
        Route::post('grading/answers/{answer}', [GradingController::class, 'gradeEssay'])->name('grading.grade-essay');

        Route::get('content', [ContentController::class, 'index'])->name('content.index');
        Route::get('content/{subject}', [ContentController::class, 'manage'])->name('content.manage');
        Route::post('content/{subject}/units', [ContentController::class, 'storeUnit'])->name('content.store-unit');
        Route::delete('content/units/{unit}', [ContentController::class, 'destroyUnit'])->name('content.destroy-unit');
        Route::post('content/units/{unit}/lessons', [ContentController::class, 'storeLesson'])->name('content.store-lesson');
        Route::delete('content/lessons/{lesson}', [ContentController::class, 'destroyLesson'])->name('content.destroy-lesson');
        Route::post('content/lessons/{lesson}/resources', [ContentController::class, 'storeResource'])->name('content.store-resource');
        Route::delete('content/resources/{resource}', [ContentController::class, 'destroyResource'])->name('content.destroy-resource');
        Route::get('content/resources/{resource}/file', [ContentController::class, 'viewResourceFile'])->name('content.view-file');
        Route::get('content/resources/{resource}/progress', [ContentController::class, 'progress'])->name('content.progress');
        Route::post('content/upload-chunk', [VideoChunkUploadController::class, 'upload'])->name('content.upload-chunk');

        Route::post('notifications/{id}/read', [NotificationsController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationsController::class, 'markAllRead'])->name('notifications.read-all');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});
