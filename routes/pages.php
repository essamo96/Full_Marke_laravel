<?php
use App\Http\Controllers\Admin\PagesController;
use Illuminate\Support\Facades\Route;

Route::get('pages', [PagesController::class, 'getIndex'])->name('pages.view');
Route::get('pages/list', [PagesController::class, 'getList'])->name('pages.list');
Route::get('pages/add', [PagesController::class, 'getAdd'])->name('pages.add');
Route::post('pages/add', [PagesController::class, 'postAdd'])->name('pages.add.submit');
Route::get('pages/edit/{id}', [PagesController::class, 'getEdit'])->name('pages.edit');
Route::post('pages/edit/{id}', [PagesController::class, 'postEdit'])->name('pages.edit.submit');
Route::post('pages/status', [PagesController::class, 'postStatus'])->name('pages.status');
Route::post('pages/delete', [PagesController::class, 'postDelete'])->name('pages.delete');
