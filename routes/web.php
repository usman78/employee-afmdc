<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\InventoryController;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/employee', function () {
    return view('employee');
});

Route::get('/attendance', [AttendanceController::class, 'attendance'])->name('attendance');

Route::get('/leaves', [LeavesController::class, 'leaves'])->name('leaves');

Route::get('/inventory', [InventoryController::class, 'inventory'])->name('inventory');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/debug', [HomeController::class, 'debug']);

