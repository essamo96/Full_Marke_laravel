<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Guardian\Auth\LoginController;
use App\Http\Controllers\Guardian\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('guardian')->name('guardian.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.submit');

    Route::get('verify', [EmailVerificationController::class, 'showForm'])->name('verify')->defaults('userType', 'guardian');
    Route::post('verify', [EmailVerificationController::class, 'verify'])->name('verify.submit')->defaults('userType', 'guardian');
    Route::post('verify/resend', [EmailVerificationController::class, 'resend'])->name('verify.resend')->defaults('userType', 'guardian');
});
