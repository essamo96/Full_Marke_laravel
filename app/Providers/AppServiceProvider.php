<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer(['layouts.partials.admin-header', 'layouts.partials.admin-sidebar'], function ($view) {
            $view->with('pending_applications', \App\Models\Application::where('status', 'new')->latest()->take(5)->get());
            $view->with('pending_applications_count', \App\Models\Application::where('status', 'new')->count());
        });
    }
}
