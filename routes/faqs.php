<?php
use App\Http\Controllers\Admin\FaqsController;
use Illuminate\Support\Facades\Route;

Route::get('faqs', [FaqsController::class, 'getIndex'])->name('faqs.view');
Route::get('faqs/list', [FaqsController::class, 'getList'])->name('faqs.list');
Route::get('faqs/add', [FaqsController::class, 'getAdd'])->name('faqs.add');
Route::post('faqs/add', [FaqsController::class, 'postAdd'])->name('faqs.add.submit');
Route::get('faqs/edit/{id}', [FaqsController::class, 'getEdit'])->name('faqs.edit');
Route::post('faqs/edit/{id}', [FaqsController::class, 'postEdit'])->name('faqs.edit.submit');
Route::post('faqs/status', [FaqsController::class, 'postStatus'])->name('faqs.status');
Route::post('faqs/delete', [FaqsController::class, 'postDelete'])->name('faqs.delete');
