<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            $key = 'admin_locale_'.Auth::guard('admin')->id();
            $locale = Session::get($key, config('app.locale'));
        } else {
            $locale = Session::get('admin_locale', config('app.locale'));
        }

        App::setLocale($locale);

        return $next($request);
    }
}
