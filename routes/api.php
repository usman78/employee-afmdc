<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HRDashboardController;

Route::get('/hr-dashboard', [HRDashboardController::class, 'index']);
