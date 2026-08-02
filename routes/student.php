<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Student\Auth\ForgotPasswordController;
use App\Http\Controllers\Student\Auth\LoginController;
use App\Http\Controllers\Student\Auth\RegisterController;
use App\Http\Controllers\Student\Auth\VerifyEmailController;
use App\Http\Controllers\Student\CartController;
use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\GroupsController;
use App\Http\Controllers\Student\NotificationsController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\ProgramsController;
use App\Http\Controllers\Student\RegistrationsController;
use App\Http\Controllers\Student\ResourcesController;
use App\Http\Controllers\Student\VideoStreamController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.submit')->middleware('throttle:8,1');
    Route::post('register/check-email', [RegisterController::class, 'checkEmail'])->name('register.check-email')->middleware('throttle:15,1');

    Route::get('password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/forgot', [ForgotPasswordController::class, 'sendNewPassword'])->name('password.email')->middleware('throttle:3,1');

    Route::post('verify', [VerifyEmailController::class, 'verify'])->name('verify.submit')->middleware('throttle:10,1');
    Route::post('verify/resend', [VerifyEmailController::class, 'resend'])->name('verify.resend')->middleware('throttle:1,1');

    Route::middleware(['auth:student', 'student.verified', 'student.device-lock'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('cart', [CartController::class, 'index'])->name('cart');
        Route::post('cart', [CartController::class, 'store'])->name('cart.store');
        Route::post('cart/sync', [CartController::class, 'sync'])->name('cart.sync');
        Route::delete('cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

        Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('registrations', [RegistrationsController::class, 'index'])->name('registrations');
        Route::get('registrations/{registration}', [RegistrationsController::class, 'show'])->name('registrations.show');
        Route::post('registrations/{registration}/pay-remaining', [RegistrationsController::class, 'payRemaining'])->name('registrations.pay-remaining');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('programs', [ProgramsController::class, 'index'])->name('programs');
        Route::get('groups', [GroupsController::class, 'index'])->name('groups');
        Route::get('groups/{group}', [GroupsController::class, 'show'])->name('groups.show');
        Route::post('groups/join-by-code', [GroupsController::class, 'joinByCode'])->name('groups.join-by-code');
        Route::get('schedule', [\App\Http\Controllers\Student\ScheduleController::class, 'index'])->name('schedule');
        Route::get('resources', [ResourcesController::class, 'index'])->name('resources');
        Route::get('secure-embed/{resource}', [\App\Http\Controllers\Student\SecureResourceController::class, 'embed'])->name('resources.embed');
        Route::post('videos/{resource}/start', [VideoStreamController::class, 'startSession'])->name('video.start');
        Route::get('videos/{resource}/stream', [VideoStreamController::class, 'stream'])->name('video.stream');
        Route::get('resources/{resource}/file', [VideoStreamController::class, 'file'])->name('resources.file');
        Route::get('resources/{resource}/link', [VideoStreamController::class, 'link'])->name('resources.link');
        Route::get('resources/{resource}/download', [VideoStreamController::class, 'download'])->name('resources.download');

        Route::get('exams', [\App\Http\Controllers\Student\ExamsController::class, 'index'])->name('exams.index');
        Route::get('exams/{exam}/take', [\App\Http\Controllers\Student\ExamsController::class, 'take'])->name('exams.take');
        Route::post('exams/{exam}/submit', [\App\Http\Controllers\Student\ExamsController::class, 'submit'])->name('exams.submit');
        Route::post('exams/{exam}/violation', [\App\Http\Controllers\Student\ExamsController::class, 'recordViolation'])->name('exams.violation');
        Route::get('results', [\App\Http\Controllers\Student\ResultsController::class, 'index'])->name('results.index');
        Route::get('results/{grade}', [\App\Http\Controllers\Student\ResultsController::class, 'show'])->name('results.show');
        Route::get('attendance', [\App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');

        Route::post('notifications/{id}/read', [NotificationsController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationsController::class, 'markAllRead'])->name('notifications.read-all');
    });
});
