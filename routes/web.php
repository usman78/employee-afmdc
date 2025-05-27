<?php

use App\Http\Middleware\EnsureNoQuit;
use App\Http\Middleware\LogEveryRequest;
use App\Models\Job;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\TeamController;

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/attendance/{emp_code}', [AttendanceController::class, 'attendance'])->name('attendance');

    Route::get('/leaves/{emp_code}', [LeavesController::class, 'leaves'])->name('leaves');
    Route::get('/apply-leave-advance/{emp_code}', [LeavesController::class, 'applyLeaveAdvance'])->name('apply-leave-advance');
    Route::post('/apply-leave-advance/{emp_code}', [LeavesController::class, 'storeLeaveAdvance'])->name('store-leave-advance');
    Route::post('/approve-leave/{leave_id}', [LeavesController::class, 'approveLeave'])->name('approve-leave');
    Route::get('leave-approvals/{emp_code}', [LeavesController::class, 'leaveApprovals'])->name('leave-approvals');
    // Route::get('/apply-leave/{emp_code}/{leave_id}', [LeavesController::class, 'editLeave'])->name('edit-leave');
    // Route::post('/apply-leave/{emp_code}/{leave_id}', [LeavesController::class, 'updateLeave'])->name('update-leave');
    // Route::get('/apply-leave/{emp_code}/{leave_id}/delete', [LeavesController::class, 'deleteLeave'])->name('delete-leave');
    // Route::get('/apply-leave/{emp_code}/cancel', [LeavesController::class, 'cancelLeave'])->name('cancel-leave');
    // Route::get('/apply-leave/{emp_code}/approve', [LeavesController::class, 'approveLeave'])->name('approve-leave');
    // Route::get('/apply-leave/{emp_code}/reject', [LeavesController::class, 'rejectLeave'])->name('reject-leave');

    Route::get('/job-dashboard', [JobController::class, 'summaryDashboard'])->name('job-dashboard');
    Route::get('/job-bank', [JobController::class, 'index'])->name('job-bank');
    Route::get('/profile/{id}', [JobController::class, 'show'])->name('profile');
    Route::post('/change-status/{app_no}', [JobController::class, 'changeStatus'])->name('change-status');
    Route::get('/shortlisted', [JobController::class, 'shortlisted'])->name('shortlisted');
    Route::get('/designation-jobs/{position}', [JobController::class, 'designationJobs'])->name('designation-jobs');


    Route::get('/inventory/{emp_code}', [InventoryController::class, 'inventory'])->name('inventory');

    Route::get('/team/{emp_code}/', [TeamController::class, 'index'])->name('team');
    Route::get('/attendance-filter/{emp_code}/{date_range}', [TeamController::class, 'attendanceFilter'])->name('attendance-filter');

});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/debug', [HomeController::class, 'debug']);

Route::fallback(function () {
    return response()->view('404', [], 404);
});

