<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ContactsController;

Route::group(['prefix' => 'contacts'], function () {
    Route::get('/view', [ContactsController::class, 'getIndex'])->name('contacts.view');
    Route::get('/list', [ContactsController::class, 'getList'])->name('contacts.list');
    Route::get('/show/{id}', [ContactsController::class, 'getShow'])->name('contacts.show');
    Route::post('/status', [ContactsController::class, 'postStatus'])->name('contacts.status');
    Route::post('/delete', [ContactsController::class, 'postDelete'])->name('contacts.delete');
});
