<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        if (! array_key_exists($locale, config('laravellocalization.supportedLocales'))) {
            return redirect()->back();
        }

        if (Auth::guard('admin')->check()) {
            Session::put('admin_locale_'.Auth::guard('admin')->id(), $locale);
        } else {
            Session::put('admin_locale', $locale);
        }

        return redirect()->back();
    }
}
