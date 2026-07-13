<?php

use App\Http\Controllers\Admin\RegistrationsController;
use Illuminate\Support\Facades\Route;

Route::get('registrations', [RegistrationsController::class, 'getIndex'])->name('registrations.view')->middleware('permission:admin.registrations.view');
