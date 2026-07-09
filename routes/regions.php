<?php

use App\Http\Controllers\Admin\RegionsController;
use Illuminate\Support\Facades\Route;

Route::get('regions', [RegionsController::class, 'getIndex'])->name('regions.view')->middleware('permission:admin.regions.view');
Route::get('regions/list', [RegionsController::class, 'getList'])->name('regions.list')->middleware('permission:admin.regions.view');
Route::get('regions/add', [RegionsController::class, 'getAdd'])->name('regions.add')->middleware('permission:admin.regions.add');
Route::post('regions/add', [RegionsController::class, 'postAdd'])->name('regions.add.submit')->middleware('permission:admin.regions.add');
Route::get('regions/edit/{id}', [RegionsController::class, 'getEdit'])->name('regions.edit')->middleware('permission:admin.regions.edit');
Route::post('regions/edit/{id}', [RegionsController::class, 'postEdit'])->name('regions.edit.submit')->middleware('permission:admin.regions.edit');
Route::post('regions/status', [RegionsController::class, 'postStatus'])->name('regions.status')->middleware('permission:admin.regions.status');
Route::post('regions/delete', [RegionsController::class, 'postDelete'])->name('regions.delete')->middleware('permission:admin.regions.delete');
