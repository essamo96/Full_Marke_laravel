<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteLocaleController;
use Illuminate\Support\Facades\Route;

// Language is switched server-side via session (no /ar or /en prefix in the
// URL) — see App\Http\Middleware\SetSiteLocale and SiteLocaleController.
Route::middleware('site.locale')->group(function () {
    Route::get('/', function () {
        $sliders = \App\Models\Slider::active()->orderBy('sort')->get();
        return view('site.home', compact('sliders'));
    })->name('site.home');

    Route::get('/lang/{locale}', [SiteLocaleController::class, 'switch'])->name('site.lang');
    Route::get('/programs/{program:slug}', [SiteController::class, 'programDetails'])->name('programs.show');
    Route::get('/apply-now', [SiteController::class, 'applyNow'])->name('apply.create');
    Route::post('/apply-now', [SiteController::class, 'storeApplication'])->name('apply.store');
    Route::post('/apply-now/verify', [SiteController::class, 'verifyApplication'])->name('apply.verify');

    // /login redirects to the admin panel login (no duplicate login page needed)
    Route::redirect('/login', '/admin/login', 301)->name('login');

    Route::get('lang/{locale}/datatables.json', function ($locale) {
        $path = base_path("lang/{$locale}/datatables.json");

        if (! file_exists($path)) {
            abort(404, 'Language file not found.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/json',
        ]);
    })->name('datatables.lang');

    require __DIR__.'/student.php';
    require __DIR__.'/teacher.php';
    require __DIR__.'/guardian.php';
});

require __DIR__.'/admin.php';
Route::get('test-dt', function() { Auth::guard('admin')->loginUsingId(1); return app(App\Http\Controllers\Admin\UsersController::class)->getList(request()); });
