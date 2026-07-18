<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PartnerManagerController;

Route::group(['prefix' => 'partners'], function () {
    Route::get('/view', [PartnerManagerController::class, 'getIndex'])->name('partners.view');
    Route::get('/list', [PartnerManagerController::class, 'getList'])->name('partners.list');
    Route::get('/add', [PartnerManagerController::class, 'getAdd'])->name('partners.add');
    Route::post('/add', [PartnerManagerController::class, 'postAdd']);
    Route::get('/edit/{id}', [PartnerManagerController::class, 'getEdit'])->name('partners.edit');
    Route::post('/edit/{id}', [PartnerManagerController::class, 'postEdit']);
    Route::post('/status', [PartnerManagerController::class, 'postStatus'])->name('partners.status');
    Route::post('/delete', [PartnerManagerController::class, 'postDelete'])->name('partners.delete');
});
