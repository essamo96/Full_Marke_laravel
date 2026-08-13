<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteLocaleController;
use Illuminate\Support\Facades\Route;

// Language is switched server-side via session (no /ar or /en prefix in the
// URL) — see App\Http\Middleware\SetSiteLocale and SiteLocaleController.
Route::middleware(['site.locale', 'site.maintenance'])->group(function () {
    Route::get('/', function () {
        $sliders = \App\Models\Slider::active()->orderBy('sort')->get();
        $pages = \App\Models\Page::active()->with('translations')->get()->keyBy('slug');
        $teams = \App\Models\Team::where('status', 1)->with('translations')->orderBy('display_order')->get();
        $faqs = \App\Models\Faq::where('status', 1)->with('translations')->orderBy('id')->get();
        $testimonials = \App\Models\Testimonial::where('status', 1)->with('translations')->orderBy('display_order')->get();
        $latestNews = \App\Models\News::where('status', 1)->with('translations')->latest()->take(6)->get();
        $programs = \App\Models\Program::where('is_active', true)->orderBy('sort_order')->get();
        $posPoints = \App\Models\PosPoint::active()->orderBy('sort_order')->get();
        return view('site.home', compact('sliders', 'pages', 'teams', 'faqs', 'testimonials', 'latestNews', 'programs', 'posPoints'));
    })->name('site.home');

    Route::get('/lang/{locale}', [SiteLocaleController::class, 'switch'])->name('site.lang');
    Route::get('/news/{id}', [SiteController::class, 'newsDetails'])->name('site.news.show');
    Route::get('/qr/subject/{subject}', [\App\Http\Controllers\QrCodeController::class, 'subject'])->name('qr.subject');
    Route::get('/qr/exam/{exam}', [\App\Http\Controllers\QrCodeController::class, 'exam'])->name('qr.exam');
    // Hit automatically by the deploy workflow after every FTP sync, since
    // compiled Blade views in storage/framework/views are never touched by
    // the deploy (excluded so runtime-generated files aren't clobbered) and
    // would otherwise keep serving a stale/incompatible compiled template
    // until someone cleared it by hand. Requires DEPLOY_CACHE_TOKEN to be
    // set in .env — the route 404s if it isn't, so this can't be left open.
    Route::get('/deploy/clear-cache', function (\Illuminate\Http\Request $request) {
        $token = env('DEPLOY_CACHE_TOKEN');
        if (! $token || ! hash_equals($token, (string) $request->query('token'))) {
            abort(404);
        }
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        return 'Cache cleared successfully.';
    });

    Route::middleware(['student.verified'])->group(function () {
        Route::get('/programs/{program:slug}', [SiteController::class, 'programDetails'])->name('programs.show');
        Route::get('/apply-now', [SiteController::class, 'applyNow'])->name('apply.create');
        Route::post('/apply-now', [SiteController::class, 'storeApplication'])->name('apply.store');
    });
    Route::post('/apply-now/verify', [SiteController::class, 'verifyApplication'])->name('apply.verify');
    
    Route::post('/contact/submit', [SiteController::class, 'storeContact'])->name('contact.store');

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

    Route::prefix('exam/guest')->name('guest.exam.')->group(function () {
        Route::get('/{exam}', [\App\Http\Controllers\GuestExamController::class, 'enter'])->name('enter');
        Route::post('/{exam}/register', [\App\Http\Controllers\GuestExamController::class, 'register'])->name('register');
        Route::get('/{exam}/take', [\App\Http\Controllers\GuestExamController::class, 'take'])->name('take');
        Route::post('/{exam}/violation', [\App\Http\Controllers\GuestExamController::class, 'recordViolation'])->name('violation');
        Route::post('/{exam}/submit', [\App\Http\Controllers\GuestExamController::class, 'submit'])->name('submit');
        Route::get('/result/{submission}', [\App\Http\Controllers\GuestExamController::class, 'result'])->name('result');
    });

    require __DIR__.'/student.php';
    require __DIR__.'/teacher.php';
    require __DIR__.'/guardian.php';
});

require __DIR__.'/admin.php';
Route::get('test-dt', function() { Auth::guard('admin')->loginUsingId(1); return app(App\Http\Controllers\Admin\UsersController::class)->getList(request()); });
