<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StaticPageController;

Route::group(['prefix' => 'static_pages'], function () {
    Route::get('/view', [StaticPageController::class, 'getIndex'])->name('static_pages.view');
    Route::get('/list', [StaticPageController::class, 'getList'])->name('static_pages.list');
    Route::get('/add', [StaticPageController::class, 'getAdd'])->name('static_pages.add');
    Route::post('/add', [StaticPageController::class, 'postAdd']);
    Route::get('/edit/{id}', [StaticPageController::class, 'getEdit'])->name('static_pages.edit');
    Route::post('/edit/{id}', [StaticPageController::class, 'postEdit']);
    Route::post('/status', [StaticPageController::class, 'postStatus'])->name('static_pages.status');
    Route::post('/delete', [StaticPageController::class, 'postDelete'])->name('static_pages.delete');
});
