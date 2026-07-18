<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PaperEditionController;

Route::group(['prefix' => 'paper_editions'], function () {
    Route::get('/view', [PaperEditionController::class, 'getIndex'])->name('paper_editions.view');
    Route::get('/list', [PaperEditionController::class, 'getList'])->name('paper_editions.list');
    Route::get('/add', [PaperEditionController::class, 'getAdd'])->name('paper_editions.add');
    Route::post('/add', [PaperEditionController::class, 'postAdd']);
    Route::get('/edit/{id}', [PaperEditionController::class, 'getEdit'])->name('paper_editions.edit');
    Route::post('/edit/{id}', [PaperEditionController::class, 'postEdit']);
    Route::post('/status', [PaperEditionController::class, 'postStatus'])->name('paper_editions.status');
    Route::post('/delete', [PaperEditionController::class, 'postDelete'])->name('paper_editions.delete');
});
