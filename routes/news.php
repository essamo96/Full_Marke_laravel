<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NewsController;

Route::group(['prefix' => 'news'], function () {
    Route::get('/view', [NewsController::class, 'getIndex'])->name('news.view');
    Route::get('/list', [NewsController::class, 'getList'])->name('news.list');
    Route::get('/add', [NewsController::class, 'getAdd'])->name('news.add');
    Route::post('/add', [NewsController::class, 'postAdd']);
    Route::get('/edit/{id}', [NewsController::class, 'getEdit'])->name('news.edit');
    Route::post('/edit/{id}', [NewsController::class, 'postEdit']);
    Route::post('/status', [NewsController::class, 'postStatus'])->name('news.status');
    Route::post('/delete', [NewsController::class, 'postDelete'])->name('news.delete');
});
