<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NewsArticleController;

Route::group(['prefix' => 'news_articles'], function () {
    Route::get('/view', [NewsArticleController::class, 'getIndex'])->name('news_articles.view');
    Route::get('/list', [NewsArticleController::class, 'getList'])->name('news_articles.list');
    Route::get('/add', [NewsArticleController::class, 'getAdd'])->name('news_articles.add');
    Route::post('/add', [NewsArticleController::class, 'postAdd']);
    Route::get('/edit/{id}', [NewsArticleController::class, 'getEdit'])->name('news_articles.edit');
    Route::post('/edit/{id}', [NewsArticleController::class, 'postEdit']);
    Route::post('/status', [NewsArticleController::class, 'postStatus'])->name('news_articles.status');
    Route::post('/delete', [NewsArticleController::class, 'postDelete'])->name('news_articles.delete');
});
