<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NewsCategoryController;

Route::group(['prefix' => 'news_categories'], function () {
    Route::get('/view', [NewsCategoryController::class, 'getIndex'])->name('news_categories.view');
    Route::get('/list', [NewsCategoryController::class, 'getList'])->name('news_categories.list');
    Route::get('/add', [NewsCategoryController::class, 'getAdd'])->name('news_categories.add');
    Route::post('/add', [NewsCategoryController::class, 'postAdd']);
    Route::get('/edit/{id}', [NewsCategoryController::class, 'getEdit'])->name('news_categories.edit');
    Route::post('/edit/{id}', [NewsCategoryController::class, 'postEdit']);
    Route::post('/status', [NewsCategoryController::class, 'postStatus'])->name('news_categories.status');
    Route::post('/delete', [NewsCategoryController::class, 'postDelete'])->name('news_categories.delete');
});
