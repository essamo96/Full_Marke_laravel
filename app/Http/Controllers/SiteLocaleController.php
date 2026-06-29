<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class SiteLocaleController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        if (array_key_exists($locale, config('laravellocalization.supportedLocales'))) {
            Session::put('site_locale', $locale);
        }

        return redirect()->back();
    }
}
