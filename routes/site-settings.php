<?php

use App\Http\Controllers\Admin\SiteSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('site-settings', [SiteSettingsController::class, 'getIndex'])->name('site_settings.view')->middleware('permission:admin.site_settings.view');
Route::post('site-settings', [SiteSettingsController::class, 'postUpdate'])->name('site_settings.update')->middleware('permission:admin.site_settings.edit');
