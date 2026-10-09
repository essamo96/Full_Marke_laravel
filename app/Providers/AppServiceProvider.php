<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One journal per request: batched actions (e.g. "fix everything") share its state so
        // twenty small changes end up as a single undoable entry.
        $this->app->singleton(\App\Services\ResourceLibrary\LibraryJournal::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Exam autosave: limited per student, never per IP - a whole classroom often shares
        // one public IP, and a per-IP limit would throttle (and so delay saving) their answers.
        RateLimiter::for('exam-draft', fn (Request $request) => Limit::perMinute(120)
            ->by('exam-draft:'.(auth('student')->id() ?: $request->ip())));

        \Illuminate\Support\Facades\View::composer(['layouts.partials.admin-header', 'layouts.partials.admin-sidebar'], function ($view) {
            $view->with('pending_applications', \App\Models\Application::where('status', 'new')->latest()->take(5)->get());
            $view->with('pending_applications_count', \App\Models\Application::where('status', 'new')->count());
        });
    }
}
