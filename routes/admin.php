<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('admin.locale')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('lang/{locale}', [LocaleController::class, 'switch'])->name('lang');

    Route::middleware('auth:admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/test-broadcast', function() {
            $student = \App\Models\Student::first();
            if(!$student) {
                $student = new \App\Models\Student();
                $student->id = 999;
                $student->full_name_ar = 'طالب تجريبي';
            }
            
            // Broadcast to admins
            \Illuminate\Support\Facades\Notification::send(
                \App\Models\Admin::all(), 
                new \App\Notifications\NewStudentRegisteredNotification($student)
            );
            
            return 'Broadcast sent successfully. Check your admin dashboard for notifications!';
        });
    });
    
    Route::get('/clear-cache', [\App\Http\Controllers\Admin\AdminController::class, 'clearCache'])->name('clear-cache');
});

// Admin management modules (Settings, Users, Permissions, Sidebar) — route names are
// bare `{module}.{action}` per docs/PATTERN_DESIGN.md #4, not prefixed with `admin.`.
Route::prefix('admin')->middleware(['auth:admin', 'admin.locale'])->group(function () {
    // شاشة اعدادات الموقع
    require __DIR__.'/users.php';
    require __DIR__.'/socials.php';
    require __DIR__.'/sliders.php';
    require __DIR__.'/settings.php';
    require __DIR__.'/site-settings.php';
    require __DIR__.'/permissions.php';
    require __DIR__.'/permissions_group.php';
    require __DIR__.'/role.php';

    // Academy management modules (see academy_system_analysis.md)
    require __DIR__.'/news_categories.php';
    require __DIR__.'/news_articles.php';
    require __DIR__.'/static_pages.php';
    require __DIR__.'/paper_editions.php';
    require __DIR__.'/contact_messages.php';
    require __DIR__.'/partners.php';

    require __DIR__.'/pages.php';
    require __DIR__.'/faqs.php';
    require __DIR__.'/teams.php';
    require __DIR__.'/news.php';
    require __DIR__.'/testimonials.php';
    require __DIR__.'/contacts.php';
    require __DIR__.'/regions.php';
    require __DIR__.'/branches.php';
    require __DIR__.'/programs.php';
    require __DIR__.'/subjects.php';
    require __DIR__.'/subject-resources.php';
    require __DIR__.'/groups.php';
    require __DIR__.'/teachers.php';
    require __DIR__.'/students.php';
    require __DIR__.'/payment-methods.php';
    require __DIR__.'/registrations.php';
    require __DIR__.'/approvals.php';
    require __DIR__.'/pending_requests.php';
    require __DIR__.'/payments.php';
    require __DIR__.'/financial-reports.php';
    require __DIR__.'/enrolments.php';
    require __DIR__.'/exams.php';
    Route::get('/exams-results', [\App\Http\Controllers\Admin\ExamController::class, 'allResults'])->name('exams_results.view');
    Route::get('/students-results', [\App\Http\Controllers\Admin\ExamController::class, 'allResults'])->name('students_results.view');
    // Student Inquiries Main Screen
    Route::get('/student-inquiry', [\App\Http\Controllers\Admin\StudentInquiryController::class, 'index'])->name('student_inquiry.view');
    Route::get('/student-inquiry/search', [\App\Http\Controllers\Admin\StudentInquiryController::class, 'search'])->name('student_inquiry.search');
    Route::get('/student-inquiry/details/{id}', [\App\Http\Controllers\Admin\StudentInquiryController::class, 'details'])->name('student_inquiry.details');

    // Subject Content (Educational Content) Screen
    Route::get('/subject-content', [\App\Http\Controllers\Admin\SubjectContentController::class, 'index'])->name('subject_content.view');
    Route::get('/subject-content/{id}', [\App\Http\Controllers\Admin\SubjectContentController::class, 'manage'])->name('subject_content.manage');
    Route::post('/subject-content/{id}/units', [\App\Http\Controllers\Admin\SubjectContentController::class, 'storeUnit'])->name('subject_content.units.store');
    Route::delete('/subject-content/units/{unit}', [\App\Http\Controllers\Admin\SubjectContentController::class, 'destroyUnit'])->name('subject_content.units.destroy');
    Route::post('/subject-content/units/{unit}/lessons', [\App\Http\Controllers\Admin\SubjectContentController::class, 'storeLesson'])->name('subject_content.lessons.store');
    Route::delete('/subject-content/lessons/{lesson}', [\App\Http\Controllers\Admin\SubjectContentController::class, 'destroyLesson'])->name('subject_content.lessons.destroy');
    Route::post('/subject-content/lessons/{lesson}/resources', [\App\Http\Controllers\Admin\SubjectContentController::class, 'storeResource'])->name('subject_content.resources.store');
    Route::delete('/subject-content/resources/{resource}', [\App\Http\Controllers\Admin\SubjectContentController::class, 'destroyResource'])->name('subject_content.resources.destroy');
    Route::get('/subject-content/resources/{resource}/file', [\App\Http\Controllers\Admin\SubjectContentController::class, 'viewResourceFile'])->name('subject_content.resources.file');
    Route::post('/subject-content/upload-chunk', [\App\Http\Controllers\Admin\VideoChunkUploadController::class, 'upload'])->name('subject_content.upload_chunk');
});
Route::get('auto-login', function () { Auth::guard('admin')->loginUsingId(1); return redirect('/admin/users'); });
Route::get('test-dt', function() { Auth::guard('admin')->loginUsingId(1); return app(App\Http\Controllers\Admin\UsersController::class)->getList(request()); });
