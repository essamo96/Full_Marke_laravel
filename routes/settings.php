<?php

use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('settings', [SettingsController::class, 'getIndex'])->name('settings.view')->middleware('permission:admin.settings.view');
Route::post('settings', [SettingsController::class, 'postUpdate'])->name('settings.update')->middleware('permission:admin.settings.edit');
Route::post('settings/sidebar-colors', [SettingsController::class, 'postUpdateSidebarColors'])->name('settings.update_sidebar_colors')->middleware('permission:admin.permissions_group.edit');
