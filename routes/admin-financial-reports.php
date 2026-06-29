<?php

use App\Http\Controllers\Admin\FinancialReportsController;
use Illuminate\Support\Facades\Route;

Route::get('financial-reports', [FinancialReportsController::class, 'getIndex'])->name('financial_reports.view')->middleware('permission:admin.financial_reports.view');
