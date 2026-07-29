<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Shared hosting typically fronts the app with a reverse proxy/CDN
        // that terminates TLS, so PHP sees plain HTTP internally unless the
        // proxy's X-Forwarded-* headers are trusted. Without this, Laravel
        // can misjudge the request as insecure — breaking secure-cookie /
        // session handling and, in turn, CSRF validation on POSTs like the
        // student registration and "Apply Now" forms.
        $middleware->trustProxies(
            at: '*',
            headers: SymfonyRequest::HEADER_X_FORWARDED_FOR
                | SymfonyRequest::HEADER_X_FORWARDED_HOST
                | SymfonyRequest::HEADER_X_FORWARDED_PORT
                | SymfonyRequest::HEADER_X_FORWARDED_PROTO
                | SymfonyRequest::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->alias([
            'admin.locale' => \App\Http\Middleware\SetAdminLocale::class,
            'site.locale' => \App\Http\Middleware\SetSiteLocale::class,
            'site.maintenance' => \App\Http\Middleware\CheckSiteMaintenance::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'student.verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            if ($request->is('teacher') || $request->is('teacher/*')) {
                return route('teacher.login');
            }
            return route('student.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
                return route('admin.dashboard');
            }
            if (\Illuminate\Support\Facades\Auth::guard('teacher')->check()) {
                return route('teacher.dashboard');
            }
            if (\Illuminate\Support\Facades\Auth::guard('guardian')->check()) {
                return route('guardian.dashboard');
            }
            if (\Illuminate\Support\Facades\Auth::guard('student')->check()) {
                return route('student.dashboard');
            }

            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.dashboard');
            }
            if ($request->is('teacher') || $request->is('teacher/*')) {
                return route('teacher.dashboard');
            }
            return route('student.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
