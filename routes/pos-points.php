<?php

use App\Http\Controllers\Admin\PosPointsController;
use Illuminate\Support\Facades\Route;

Route::get('pos-points', [PosPointsController::class, 'getIndex'])->name('pos_points.view')->middleware('permission:admin.pos_points.view');
Route::get('pos-points/list', [PosPointsController::class, 'getList'])->name('pos_points.list')->middleware('permission:admin.pos_points.view');
Route::get('pos-points/add', [PosPointsController::class, 'getAdd'])->name('pos_points.add')->middleware('permission:admin.pos_points.add');
Route::post('pos-points/add', [PosPointsController::class, 'postAdd'])->name('pos_points.add.submit')->middleware('permission:admin.pos_points.add');
Route::get('pos-points/edit/{id}', [PosPointsController::class, 'getEdit'])->name('pos_points.edit')->middleware('permission:admin.pos_points.edit');
Route::post('pos-points/edit/{id}', [PosPointsController::class, 'postEdit'])->name('pos_points.edit.submit')->middleware('permission:admin.pos_points.edit');
Route::post('pos-points/status', [PosPointsController::class, 'postStatus'])->name('pos_points.status')->middleware('permission:admin.pos_points.status');
Route::post('pos-points/delete', [PosPointsController::class, 'postDelete'])->name('pos_points.delete')->middleware('permission:admin.pos_points.delete');
