<?php
use App\Http\Controllers\Admin\TeamsController;
use Illuminate\Support\Facades\Route;

Route::get('teams', [TeamsController::class, 'getIndex'])->name('teams.view');
Route::get('teams/list', [TeamsController::class, 'getList'])->name('teams.list');
Route::get('teams/add', [TeamsController::class, 'getAdd'])->name('teams.add');
Route::post('teams/add', [TeamsController::class, 'postAdd'])->name('teams.add.submit');
Route::get('teams/edit/{id}', [TeamsController::class, 'getEdit'])->name('teams.edit');
Route::post('teams/edit/{id}', [TeamsController::class, 'postEdit'])->name('teams.edit.submit');
Route::post('teams/status', [TeamsController::class, 'postStatus'])->name('teams.status');
Route::post('teams/delete', [TeamsController::class, 'postDelete'])->name('teams.delete');
Route::post('teams/type', [TeamsController::class, 'postType'])->name('teams.type');
Route::post('teams/chairman', [TeamsController::class, 'postChairman'])->name('teams.chairman');
