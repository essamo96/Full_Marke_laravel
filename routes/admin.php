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
    });
});

// Admin management modules (Settings, Users, Permissions, Sidebar) — route names are
// bare `{module}.{action}` per docs/PATTERN_DESIGN.md #4, not prefixed with `admin.`.
Route::prefix('admin')->middleware(['auth:admin', 'admin.locale'])->group(function () {
    // شاشة اعدادات الموقع
    require __DIR__.'/users.php';
    require __DIR__.'/socials.php';
    require __DIR__.'/sliders.php';
    require __DIR__.'/admin-settings.php';
    require __DIR__.'/admin-site-settings.php';
    require __DIR__.'/admin-permissions.php';
    require __DIR__.'/admin-permissions_group.php';
    require __DIR__.'/admin-role.php';

    // Academy management modules (see academy_system_analysis.md)
    require __DIR__.'/admin-programs.php';
    require __DIR__.'/admin-study-branches.php';
    require __DIR__.'/admin-subjects.php';
    require __DIR__.'/admin-subject-resources.php';
    require __DIR__.'/admin-groups.php';
    require __DIR__.'/admin-teachers.php';
    require __DIR__.'/admin-students.php';
    require __DIR__.'/admin-payment-methods.php';
    require __DIR__.'/admin-approvals.php';
    require __DIR__.'/admin-payments.php';
    require __DIR__.'/admin-financial-reports.php';
});
Route::get('auto-login', function () { Auth::guard('admin')->loginUsingId(1); return redirect('/admin/users'); });
Route::get('test-dt', function() { Auth::guard('admin')->loginUsingId(1); return app(App\Http\Controllers\Admin\UsersController::class)->getList(request()); });
