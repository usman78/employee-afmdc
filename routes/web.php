<?php
use App\Models\Roster;
use App\Http\Middleware\EnsureNoQuit;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeavesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\RosterController;
use App\Models\Employee;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\EmployeeTaskController;
use App\Http\Controllers\AdvanceSalaryController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\ReportAccessController;
use App\Http\Controllers\AuditReportController;

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware(EnsureNoQuit::class);
    Route::get('/change-password', [HomeController::class, 'changePassword'])->name('change-password');
    Route::post('/update-password', [HomeController::class, 'updatePassword'])->name('update-password');

    Route::get('/roster/{empCode}', [RosterController::class, 'index'])->name('roster');

        Route::get('/attendance-report', [AttendanceController::class, 'attendanceReport'])->middleware('report.access:attendance')->name('attendance-report');
    Route::get('/attendance-late-report', [AttendanceController::class, 'attendanceLateReport'])->middleware('report.access:attendance-late')->name('attendance-late-report');
    Route::get('/attendance-absent-report', [AttendanceController::class, 'attendanceAbsentReport'])->middleware('report.access:attendance-absent')->name('attendance-absent-report');
    Route::get('/attendance-present-report', [AttendanceController::class, 'attendancePresentReport'])->middleware('report.access:attendance-present')->name('attendance-present-report');
    Route::get('/manual-attendance-report', [AttendanceController::class, 'manualAttendanceReport'])->middleware('report.access:manual-attendance')->name('manual-attendance-report');
    Route::get('/hr-reports', [AttendanceController::class, 'hrReports'])->middleware('report.access:hr-dashboard')->name('hr-reports');
    Route::get('/department-strength-report', [AttendanceController::class, 'departmentStrengthReport'])->middleware('report.access:department-strength')->name('department-strength-report');
    Route::post('/department-strength-report', [AttendanceController::class, 'departmentStrengthReportData'])->middleware('report.access:department-strength')->name('department-strength-report-data');
    Route::post('/department-strength-report-download', [AttendanceController::class, 'departmentStrengthReportDownload'])->middleware('report.access:department-strength')->name('department-strength-report-download');
    Route::post('/attendance-report-department', [AttendanceController::class, 'attendanceReportDepartmentData'])->middleware('report.access:attendance')->name('attendance-report-department-data');
    Route::post('/attendance-report-department-email', [AttendanceController::class, 'attendanceReportDepartmentEmail'])->middleware('report.access:attendance')->name('attendance-report-department-email');
    Route::post('/attendance-report-department-download', [AttendanceController::class, 'attendanceReportDepartmentDownload'])->middleware('report.access:attendance')->name('attendance-report-department-download');
    Route::post('/attendance-report', [AttendanceController::class, 'attendanceReportData'])->middleware('report.access:attendance')->name('attendance-report-data');
    Route::post('/attendance-late-report', [AttendanceController::class, 'attendanceLateReportData'])->middleware('report.access:attendance-late')->name('attendance-late-report-data');
    Route::post('/attendance-absent-report', [AttendanceController::class, 'attendanceAbsentReportData'])->middleware('report.access:attendance-absent')->name('attendance-absent-report-data');
    Route::post('/attendance-present-report', [AttendanceController::class, 'attendancePresentReportData'])->middleware('report.access:attendance-present')->name('attendance-present-report-data');
    Route::post('/manual-attendance-report', [AttendanceController::class, 'manualAttendanceReportData'])->middleware('report.access:manual-attendance')->name('manual-attendance-report-data');
    Route::post('/manual-attendance-report-download', [AttendanceController::class, 'manualAttendanceReportDownload'])->middleware('report.access:manual-attendance')->name('manual-attendance-report-download');
    Route::post('/attendance-late-report-download', [AttendanceController::class, 'attendanceLateReportDownload'])->middleware('report.access:attendance-late')->name('attendance-late-report-download');
    Route::post('/attendance-absent-report-download', [AttendanceController::class, 'attendanceAbsentReportDownload'])->middleware('report.access:attendance-absent')->name('attendance-absent-report-download');
    Route::post('/attendance-report-download/{emp_code}', [AttendanceController::class, 'attendanceReportDownload'])->middleware('report.access:attendance')->name('attendance-report-download');
    Route::post('/attendance-report-email/{emp_code}', [AttendanceController::class, 'attendanceReportEmail'])->middleware('report.access:attendance')->name('attendance-report-email');
    Route::get('/attendance/{emp_code}', [AttendanceController::class, 'attendance'])->name('attendance');
    Route::get('/att-discrepancy-report', function() {
        return view('attendance-discrepancy');
    })->middleware('report.access:attendance-discrepancy')->name('att-discrepancy-report');
    Route::post('/att-discrepency', [AttendanceController::class, 'attDiscrepency'])->middleware('report.access:attendance-discrepancy')->name('att-discrepency');
    Route::post('/att-discrepancy-download/{emp_code}', [AttendanceController::class, 'attDiscrepancyDownload'])->middleware('report.access:attendance-discrepancy')->name('att-discrepancy-report-download');
    Route::get('/leaves/{emp_code}', [LeavesController::class, 'leaves'])->name('leaves');
    Route::get('/apply-leave-advance/{emp_code}/{shortLeaveOnly?}', [LeavesController::class, 'applyLeaveAdvance'])->name('apply-leave-advance');
    Route::post('/leave/preview', [LeavesController::class, 'preview'])->name('leave.preview');
    Route::post('/apply-leave-advance/{emp_code}', [LeavesController::class, 'storeLeaveAdvance'])->name('store-leave-advance');
    Route::get('/apply-unpaid-leave/{emp_code}', function($emp_code) {
        $employee = Employee::where('emp_code', $emp_code)->first();
        return view('apply-leave-unpaid', [
            'employee' => $employee,
            'emp_code' => request()->route('emp_code')
        ]);
    })->name('apply-unpaid-leave');
    Route::post('/apply-unpaid-leave/{emp_code}', [LeavesController::class, 'storeUnpaidLeave'])->name('store-unpaid-leave');
    Route::get('/apply-od-leave/{emp_code}', function($emp_code) {
        $employee = Employee::where('emp_code', $emp_code)->first();
        return view('apply-od-leave', [
            'employee' => $employee,
            'emp_code' => request()->route('emp_code')
        ]);
    })->name('apply-od-leave');
    Route::post('/apply-od-leave/{emp_code}', [LeavesController::class, 'storeOdLeave'])->name('store-od-leave');
    Route::get('/leaves-applied/{emp_code}', [LeavesController::class, 'leavesApplied'])->name('leaves-applied');
    Route::get('/hr/leaves-applied', [LeavesController::class, 'leavesAppliedHr'])->middleware('report.access:leave')->name('hr-leaves-applied');
    Route::get('leave-approvals/{emp_code}', [LeavesController::class, 'leaveApprovals'])->name('leave-approvals');
    Route::post('/approve-leave/{leave_id}', [LeavesController::class, 'approveLeave'])->name('approve-leave');
    Route::post('/approve-all-leaves', [LeavesController::class, 'approveAll'])->name('approve-all-leaves');
    Route::post('/reject-leave/{leave_id}', [LeavesController::class, 'rejectLeave'])->name('reject-leave');
    Route::get('/leave-report', [LeavesController::class, 'leaveReport'])->middleware('report.access:leave')->name('leave-report');
    Route::post('/leave-report-data', [LeavesController::class, 'leaveReportData'])->middleware('report.access:leave')->name('leave-report-data');
    Route::post('/leave-report-download/{start_date}/{end_date}/{dept_code?}', [LeavesController::class, 'leaveReportDownload'])->middleware('report.access:leave')->name('leave.report.download');
    Route::get('/leave-report-employee-search', [LeavesController::class, 'leaveReportEmployeeSearch'])->middleware('report.access:leave')->name('leave-report-employee-search');
    Route::get('/pending-leaves-report/{status}', [LeavesController::class, 'getPendingLeavesByStatus'])->middleware('report.access:leave')->name('pending-leaves-report');
    Route::get('/pending-leaves-report-view', [LeavesController::class, 'getPendingLeavesReportView'])->middleware('report.access:leave')->name('pending-leaves-report-view');
    Route::get('/individual-leave-report', [LeavesController::class, 'individualLeaveReport'])->middleware('report.access:leave')->name('individual-leave-report');
    Route::get('/my-individual-leave-report', [LeavesController::class, 'myIndividualLeaveReport'])->name('my-individual-leave-report');

    Route::get('/job-dashboard', [JobController::class, 'summaryDashboard'])->name('job-dashboard');
    Route::get('/open-jobs', [JobController::class, 'openJobs'])->name('open-jobs');
    Route::get('/vacancy-jobs', [JobController::class, 'vacancyJobs'])->name('vacancy-jobs');
    Route::get('/job-bank', [JobController::class, 'index'])->name('job-bank');
    Route::get('/profile/{id}', [JobController::class, 'show'])->name('profile');
    Route::post('/change-status/{app_no}', [JobController::class, 'changeStatus'])->name('change-status');
    Route::get('/shortlisted', [JobController::class, 'shortlisted'])->name('shortlisted');
    Route::get('/designation-jobs/{position}', [JobController::class, 'designationJobs'])->name('designation-jobs');
    Route::get('/job-search', [JobController::class, 'jobSearch'])->name('jobs-search');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks');
    Route::get('/meetings', [TaskController::class, 'meetings'])->name('meetings');
    Route::get('/assigned-tasks', [TaskController::class, 'tasks'])->name('assigned-tasks');
    Route::post('/update-progress', [TaskController::class, 'updateProgress'])->name('update-progress');
    Route::get('/sops', [TaskController::class, 'sops'])->name('sops');
    Route::post('/employee-tasks/{employeeTask}/progress', [EmployeeTaskController::class, 'progress'])->name('employee-tasks.progress');
    Route::post('/employee-tasks/{employeeTask}/comments', [EmployeeTaskController::class, 'comment'])->name('employee-tasks.comments.store');
    Route::resource('employee-tasks', EmployeeTaskController::class)->parameters(['employee-tasks' => 'employeeTask']);
    Route::get('/notifications/redirect/{notification}', [NotificationsController::class, 'handle'])->name('notifications.redirect');

    Route::get('/inventory-reports', [InventoryController::class, 'reports'])->middleware('report.access:inventory-reports')->name('inventory.reports');
    Route::get('/inventory/{emp_code}', [InventoryController::class, 'inventory'])->name('inventory');
    Route::post('/inventory/acknowledge/{item_code}/{doc_no}', [InventoryController::class, 'acknowledgeItem'])->name('inventory.acknowledge');
    Route::get('/inventory-report', [InventoryController::class, 'storeReport'])->middleware('report.access:store-report')->name('inventory.store_report');
    Route::get('/indent-to-advise-tracking', [InventoryController::class, 'indentAdviseTracking'])->name('inventory.indent_advise_tracking');

    Route::get('/inventory-reports', [InventoryController::class, 'reports'])->name('inventory.reports');
    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::get('/attendance-filter/{emp_code}/{date_range}', [TeamController::class, 'attendanceFilter'])->name('attendance-filter');
    Route::get('/dgm-team-filter', [TeamController::class, 'dgmTeamFilter'])->name('dgm-team-filter');

    Route::prefix('service-requests')->group(function () {
        Route::get('/', [ServiceRequestController::class, 'index'])->name('service-requests.index');
        Route::get('/create', [ServiceRequestController::class, 'create'])->name('service-requests.create');
        Route::post('/', [ServiceRequestController::class, 'store'])->name('service-requests.store');
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

    Route::prefix('timetables')->group(function () {
        Route::get('/', [TimetableController::class, 'index'])->name('timetables.index');
        Route::get('/calendar', [TimetableController::class, 'show'])->name('timetables.show');
        Route::get('/calendar/events/{year_id}/{program_id}', [TimetableController::class, 'getTimetables'])->name('timetables.get');
        Route::get('/new-timetable', [TimetableController::class, 'newTimetable'])->name('timetables.new-timetable');
        Route::post('/create-timetable', [TimetableController::class, 'store'])->name('timetables.store');
        Route::post('/create', [TimetableController::class, 'create'])->name('timetables.create');
        Route::post('/get-subject', [TimetableController::class, 'getSubject'])->name('timetables.get-subject'); 
        Route::post('/mark-finalized', [TimetableController::class, 'markFinalized'])->name('timetables.mark-finalized');
        Route::get('/timetable/download', [TimetableController::class, 'downloadTimetable'])->name('timetables.download'); 
    });

    Route::prefix('student-admissions')->group(function() {
        Route::get('/', [AdmissionController::class, 'admissions'])->name('admissions');
        Route::get('/applicant/{id}', [AdmissionController::class, 'applicant'])->name('applicant');
        Route::get('/download-admission-pdf/{id}', [AdmissionController::class, 'downloadAdmissionPDF'])->name('download-admission-pdf');
        Route::get('/preview-admission/{id}', [AdmissionController::class, 'previewAdmission'])->name('preview-admission');
        Route::get('/private/admissions/{admission}/{file}', [App\Http\Controllers\FilesController::class, 'getAdmissionFiles'])->name('get.admission.files');
    });

    Route::prefix('exit-interview')->group(function() {
        Route::get('/create/{emp_code}', [App\Http\Controllers\ExitInterviewController::class, 'create'])->name('exit-interview.create');
        Route::post('/store', [App\Http\Controllers\ExitInterviewController::class, 'store'])->name('exit-interview.store');
        Route::get('/report', [App\Http\Controllers\ExitInterviewController::class, 'report'])->middleware('report.access:exit-interview')->name('exit-interview.report');
        Route::get('/show/{id}', [App\Http\Controllers\ExitInterviewController::class, 'show'])->middleware('report.access:exit-interview')->name('exit-interview.show');
        Route::get('/download-pdf/{id}', [App\Http\Controllers\ExitInterviewController::class, 'downloadPDF'])->middleware('report.access:exit-interview')->name('exit-interview.download-pdf');
    });

    Route::get('/advance-salary-report', [AdvanceSalaryController::class, 'report'])->middleware('report.access:advance-salary-hr')->name('advance-salary.report');
    Route::post('/advance-salary-report/{application}/decision', [AdvanceSalaryController::class, 'hrDecision'])->name('advance-salary.hr-decision');
    Route::get('/finance-reports', [AdvanceSalaryController::class, 'financeReports'])->middleware('report.access:finance-dashboard')->name('finance-reports');
    Route::get('/finance/advance-salary-report', [AdvanceSalaryController::class, 'accountsReport'])->middleware('report.access:advance-salary-finance')->name('advance-salary.accounts-report');
    Route::get('/finance/advance-salary-report/download-approved', [AdvanceSalaryController::class, 'accountsApprovedDownload'])->middleware('report.access:advance-salary-finance')->name('advance-salary.accounts-approved-download');
    Route::get('/finance/advance-salary-report/download-by-name', [AdvanceSalaryController::class, 'nameFilteredDownload'])->middleware('report.access:advance-salary-finance')->name('advance-salary.name-filtered-download');
    Route::get('/finance/advance-salary-report/download-by-date', [AdvanceSalaryController::class, 'dateFilteredDownload'])->middleware('report.access:advance-salary-finance')->name('advance-salary.date-filtered-download');
    Route::post('/finance/advance-salary-report/{application}/decision', [AdvanceSalaryController::class, 'accountsDecision'])->name('advance-salary.accounts-decision');
    Route::get('/advance-salary-subordinate-applications', [AdvanceSalaryController::class, 'hodIndex'])->name('advance-salary.hod-index');
    Route::get('/advance-salary-approvals/{application}', [AdvanceSalaryController::class, 'hodShow'])->name('advance-salary.hod-show');
    Route::post('/advance-salary-approvals/{application}/decision', [AdvanceSalaryController::class, 'hodDecision'])->name('advance-salary.hod-decision');
    Route::get('/advance-salary/{emp_code}', [AdvanceSalaryController::class, 'create'])->name('advance-salary.create');
    Route::post('/advance-salary/{emp_code}', [AdvanceSalaryController::class, 'store'])->name('advance-salary.store');
    Route::post('/advance-salary/{emp_code}/{application}/revoke', [AdvanceSalaryController::class, 'revoke'])->name('advance-salary.revoke');

    Route::get('/overtime-subordinate-applications', [OvertimeController::class, 'hodIndex'])->name('overtime.hod-index');
    Route::get('/overtime-approvals/{application}', [OvertimeController::class, 'hodShow'])->name('overtime.hod-show');
    Route::post('/overtime-approvals/{application}/decision', [OvertimeController::class, 'hodDecision'])->name('overtime.hod-decision');
    Route::get('/overtime-report', [OvertimeController::class, 'report'])->middleware('report.access:overtime-hr')->name('overtime.report');
    Route::get('/overtime-eligibility-report', [OvertimeController::class, 'eligibilityReport'])->middleware('report.access:overtime-eligibility')->name('overtime.eligibility-report');
    Route::get('/overtime-eligibility-report/download', [OvertimeController::class, 'downloadEligibilityReport'])->middleware('report.access:overtime-eligibility')->name('overtime.eligibility-download');
    Route::get('/overtime-report/download-approved', [OvertimeController::class, 'downloadApprovedReport'])->middleware('report.access:overtime-hr')->name('overtime.approved-download');
    Route::post('/overtime-report/{application}/decision', [OvertimeController::class, 'hrDecision'])->name('overtime.hr-decision');
    Route::get('/overtime/{emp_code}', [OvertimeController::class, 'create'])->name('overtime.create');
    Route::post('/overtime/{emp_code}', [OvertimeController::class, 'store'])->name('overtime.store');
    Route::post('/overtime/{application}/edit-minutes', [OvertimeController::class, 'editMinutes'])->name('overtime.edit-minutes');
    Route::get('/finance/overtime-reports', [OvertimeController::class, 'financeReports'])->middleware('report.access:overtime-finance')->name('overtime.finance-reports');
    Route::get('/finance/overtime-report', [OvertimeController::class, 'financeReport'])->middleware('report.access:overtime-finance')->name('overtime.finance-report');

    Route::get('/report-access', [ReportAccessController::class, 'index'])->name('report-access.index');
    Route::put('/report-access/{empCode}', [ReportAccessController::class, 'update'])->name('report-access.update');
    Route::post('/finance/overtime-report/{application}/decision', [OvertimeController::class, 'financeDecision'])->name('overtime.finance-decision');
    
    Route::get('/audit-reports', [AuditReportController::class, 'index'])->middleware('report.access:audit-dashboard')->name('audit-reports.index');
    Route::get('/audit-reports/advance-salary', [AuditReportController::class, 'advanceSalary'])->middleware('report.access:audit-advance-salary')->name('audit-reports.advance-salary');
    Route::get('/audit-reports/overtime', [AuditReportController::class, 'overtime'])->middleware('report.access:audit-overtime')->name('audit-reports.overtime');
    Route::get('/audit-reports/advance-salary/download/{scope}', [AuditReportController::class, 'downloadAdvanceSalary'])->middleware('report.access:audit-advance-salary')->name('audit-reports.advance-salary.download');
    Route::get('/audit-reports/overtime/download/{scope}', [AuditReportController::class, 'downloadOvertime'])->middleware('report.access:audit-overtime')->name('audit-reports.overtime.download');

    Route::prefix('notices')->group(function () {
        Route::get('/', [NoticeController::class, 'index'])->name('notices.index');
        Route::get('/create', [NoticeController::class, 'create'])->name('notices.create');
        Route::post('/', [NoticeController::class, 'store'])->name('notices.store');
        Route::get('/{notice}/review', [NoticeController::class, 'review'])->name('notices.review');
        Route::post('/{notice}/approve', [NoticeController::class, 'approve'])->name('notices.approve');
        Route::post('/{notice}/reject', [NoticeController::class, 'reject'])->name('notices.reject');
    });
});
Route::get('/send-shortlist-email/{app_no}', [JobController::class, 'sendShortlistEmail'])->name('send-shortlist-email');

Route::get('applications/{id}/{fileName}', [App\Http\Controllers\FilesController::class, 'download'])
    ->name('download-file');
Route::get('admissions/{id}/{fileName}/{fileFormat}', [App\Http\Controllers\FilesController::class, 'downloadAdmissionFile'])->name('download-admission-file');     

Route::get('/debug', [TaskController::class, 'createSop'])->name('debug');

Route::get('/pagination-test', function () {
    $users = \App\Models\User::paginate(5);
    return view('tasks.pagination-test', compact('users'));
});
Route::get('/query', [HomeController::class, 'query'])
    ->name('query.get');
Route::post('/query', [HomeController::class, 'queryDown'])
    ->name('query.post');
Route::fallback(function () {
    return response()->view('404', [], 404);
});

