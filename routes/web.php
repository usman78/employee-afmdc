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
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\NotificationsController;

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/attendance/{emp_code}', [AttendanceController::class, 'attendance'])->name('attendance');

    Route::get('/leaves/{emp_code}', [LeavesController::class, 'leaves'])->name('leaves');
    Route::get('/apply-leave-advance/{emp_code}', [LeavesController::class, 'applyLeaveAdvance'])->name('apply-leave-advance');
    Route::post('/apply-leave-advance/{emp_code}', [LeavesController::class, 'storeLeaveAdvance'])->name('store-leave-advance');
    Route::post('/apply-unpaid-leave/{emp_code}', [LeavesController::class, 'storeUnpaidLeave'])->name('store-unpaid-leave');
    Route::get('/check-if-any-leave/{emp_code}', [LeavesController::class, 'checkIfAnyLeave'])->name('check-if-any-leave');
    Route::get('leave-approvals/{emp_code}', [LeavesController::class, 'leaveApprovals'])->name('leave-approvals');
    Route::post('/approve-leave/{leave_id}', [LeavesController::class, 'approveLeave'])->name('approve-leave');
    Route::post('/approve-all-leaves', [LeavesController::class, 'approveAll'])->name('approve-all-leaves');
    Route::post('/reject-leave/{leave_id}', [LeavesController::class, 'rejectLeave'])->name('reject-leave');

    Route::get('/job-dashboard', [JobController::class, 'summaryDashboard'])->name('job-dashboard');
    Route::get('/job-bank', [JobController::class, 'index'])->name('job-bank');
    Route::get('/profile/{id}', [JobController::class, 'show'])->name('profile');
    Route::post('/change-status/{app_no}', [JobController::class, 'changeStatus'])->name('change-status');
    Route::get('/shortlisted', [JobController::class, 'shortlisted'])->name('shortlisted');
    Route::get('/designation-jobs/{position}', [JobController::class, 'designationJobs'])->name('designation-jobs');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
    Route::get('/meetings', [TaskController::class, 'meetings'])->name('meetings');
    Route::get('/assigned-tasks', [TaskController::class, 'tasks'])->name('assigned-tasks');
    Route::post('/update-progress', [TaskController::class, 'updateProgress'])->name('update-progress');
    Route::get('/sops', [TaskController::class, 'sops'])->name('sops');

    Route::get('/inventory/{emp_code}', [InventoryController::class, 'inventory'])->name('inventory');

    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::get('/attendance-filter/{emp_code}/{date_range}', [TeamController::class, 'attendanceFilter'])->name('attendance-filter');

    Route::prefix('service-requests')->group(function () {
        Route::get('/', [ServiceRequestController::class, 'index'])->name('service-requests.index');
        Route::get('/create', [ServiceRequestController::class, 'create'])->name('service-requests.create');
        Route::post('/', [ServiceRequestController::class, 'store'])->name('service-requests.store');
        Route::get('/notifications/redirect/{notification}', [NotificationsController::class, 'handle'])->name('notifications.redirect');
        Route::get('/assignment/{id}', [ServiceRequestController::class, 'assignment'])->name('service-requests.assignment');
        Route::post('/approve/{id}', [ServiceRequestController::class, 'approve'])->name('service-requests.approve');
        Route::get('/hod-approvals', [ServiceRequestController::class, 'hodApprovals'])->name('service-requests.hod-approvals');
        Route::post('/approve_assign/{id}', [ServiceRequestController::class, 'approveAssignment'])->name('service-requests.approve_assign');
        Route::post('/reject-assign/{id}', [ServiceRequestController::class, 'rejectAssignment'])->name('service-requests.reject_assign');
        Route::get('/assignment-details/{requestId}', [ServiceRequestController::class, 'assignmentDetails'])->name('service-requests.assignment-details');
        Route::get('/debug', [ServiceRequestController::class, 'debug'])->name('service-requests.debug');
        Route::post('/assignment-update/{id}', [ServiceRequestController::class, 'assignmentUpdate'])->name('service-requests.assignment-update');
        Route::get('show/{id}', [ServiceRequestController::class, 'show'])->name('service-requests.show');
        Route::post('/update-status/{id}', [ServiceRequestController::class, 'addUpdate'])->name('service-requests.add-update');
    });

});

Route::get('applications/{id}/{fileName}', [App\Http\Controllers\FilesController::class, 'download'])
    ->name('download-file');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/debug', [TaskController::class, 'createSop'])->name('debug');

Route::get('/pagination-test', function () {
    $users = \App\Models\User::paginate(5);
    return view('tasks.pagination-test', compact('users'));
});

Route::fallback(function () {
    return response()->view('404', [], 404);
});

