<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Student\Auth\LoginController;
use App\Http\Controllers\Student\Auth\RegisterController;
use App\Http\Controllers\Student\CartController;
use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\RegistrationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.submit');

    Route::get('verify', [EmailVerificationController::class, 'showForm'])->name('verify')->defaults('userType', 'student');
    Route::post('verify', [EmailVerificationController::class, 'verify'])->name('verify.submit')->defaults('userType', 'student');
    Route::post('verify/resend', [EmailVerificationController::class, 'resend'])->name('verify.resend')->defaults('userType', 'student');

    Route::middleware('auth:student')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('cart', [CartController::class, 'index'])->name('cart');
        Route::post('cart', [CartController::class, 'store'])->name('cart.store');
        Route::post('cart/{cartItem}/group', [CartController::class, 'updateGroup'])->name('cart.update-group');
        Route::delete('cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

        Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('registrations', [RegistrationsController::class, 'index'])->name('registrations');
        Route::get('registrations/{registration}', [RegistrationsController::class, 'show'])->name('registrations.show');
        Route::post('registrations/{registration}/pay-remaining', [RegistrationsController::class, 'payRemaining'])->name('registrations.pay-remaining');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});
