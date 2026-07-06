<?php

use App\Http\Controllers\Admin\SlidersController;
use Illuminate\Support\Facades\Route;

// Slider Routes
Route::get('sliders', [SlidersController::class, 'getIndex'])
    ->name('sliders.view')
    ->middleware('permission:admin.sliders.view');

Route::get('sliders/list', [SlidersController::class, 'getList'])
    ->name('sliders.list')
    ->middleware('permission:admin.sliders.view');

Route::get('sliders/add', [SlidersController::class, 'getAdd'])
    ->name('sliders.add')
    ->middleware('permission:admin.sliders.add');

Route::post('sliders/add', [SlidersController::class, 'postAdd'])
    ->name('sliders.postAdd')
    ->middleware('permission:admin.sliders.add');

Route::get('sliders/edit/{id}', [SlidersController::class, 'getEdit'])
    ->name('sliders.edit')
    ->middleware('permission:admin.sliders.edit');

Route::post('sliders/edit/{id}', [SlidersController::class, 'postEdit'])
    ->name('sliders.postEdit')
    ->middleware('permission:admin.sliders.edit');

Route::post('sliders/delete', [SlidersController::class, 'postDelete'])
    ->name('sliders.delete')
    ->middleware('permission:admin.sliders.delete');

Route::post('sliders/status', [SlidersController::class, 'postStatus'])
    ->name('sliders.status')
    ->middleware('permission:admin.sliders.status');
