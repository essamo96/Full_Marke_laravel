<?php

use App\Http\Controllers\Admin\TestimonialsController;
use Illuminate\Support\Facades\Route;

Route::prefix('testimonials')->name('testimonials.')->group(function () {
    Route::get('/', [TestimonialsController::class, 'getIndex'])->name('view');
    Route::get('/list', [TestimonialsController::class, 'getList'])->name('list');
    Route::get('/add', [TestimonialsController::class, 'getAdd'])->name('add');
    Route::post('/add', [TestimonialsController::class, 'postAdd']);
    Route::get('/edit/{id}', [TestimonialsController::class, 'getEdit'])->name('edit');
    Route::post('/edit/{id}', [TestimonialsController::class, 'postEdit']);
    Route::post('/status', [TestimonialsController::class, 'postStatus'])->name('status');
    Route::post('/delete', [TestimonialsController::class, 'postDelete'])->name('delete');
});
