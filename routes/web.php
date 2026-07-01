<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteLocaleController;
use Illuminate\Support\Facades\Route;

// Language is switched server-side via session (no /ar or /en prefix in the
// URL) — see App\Http\Middleware\SetSiteLocale and SiteLocaleController.
Route::middleware('site.locale')->group(function () {
    Route::get('/', function () {
        return view('site.home');
    })->name('site.home');

    Route::get('/lang/{locale}', [SiteLocaleController::class, 'switch'])->name('site.lang');

    Route::get('/programs/{program:slug}', [SiteController::class, 'programDetails'])->name('programs.show');
    Route::get('/apply-now', [SiteController::class, 'applyNow'])->name('apply.create');
    Route::post('/apply-now', [SiteController::class, 'storeApplication'])->name('apply.store');
    Route::post('/apply-now/verify', [SiteController::class, 'verifyApplication'])->name('apply.verify');

    // /login redirects to the admin panel login (no duplicate login page needed)
    Route::redirect('/login', '/admin/login', 301)->name('login');

    require __DIR__.'/student.php';
    require __DIR__.'/teacher.php';
    require __DIR__.'/guardian.php';
});

require __DIR__.'/admin.php';
