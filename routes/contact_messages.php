<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ContactMessageController;

Route::group(['prefix' => 'contact_messages'], function () {
    Route::get('/view', [ContactMessageController::class, 'getIndex'])->name('contact_messages.view');
    Route::get('/list', [ContactMessageController::class, 'getList'])->name('contact_messages.list');
    Route::post('/delete', [ContactMessageController::class, 'postDelete'])->name('contact_messages.delete');
});
