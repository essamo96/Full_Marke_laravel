<?php

use App\Http\Controllers\Admin\SocialsController;
use Illuminate\Support\Facades\Route;

// Social Routes
Route::get('socials', [SocialsController::class, 'getIndex'])
    ->name('socials.view')
    ->middleware('permission:admin.socials.view');

Route::get('socials/list', [SocialsController::class, 'getList'])
    ->name('socials.list')
    ->middleware('permission:admin.socials.view');

Route::get('socials/add', [SocialsController::class, 'getAdd'])
    ->name('socials.add')
    ->middleware('permission:admin.socials.add');

Route::post('socials/add', [SocialsController::class, 'postAdd'])
    ->name('socials.add.submit')
    ->middleware('permission:admin.socials.add');

Route::get('socials/edit/{id}', [SocialsController::class, 'getEdit'])
    ->name('socials.edit')
    ->middleware('permission:admin.socials.edit');

Route::post('socials/edit/{id}', [SocialsController::class, 'postEdit'])
    ->name('socials.edit.submit')
    ->middleware('permission:admin.socials.edit');

Route::post('socials/delete', [SocialsController::class, 'postDelete'])
    ->name('socials.delete')
    ->middleware('permission:admin.socials.delete');

Route::post('socials/status', [SocialsController::class, 'postStatus'])
    ->name('socials.status')
    ->middleware('permission:admin.socials.status');
