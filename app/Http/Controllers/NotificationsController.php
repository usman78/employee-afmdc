<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeApplication;

class NotificationsController extends Controller
{
    public function handle($notificationId){
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        $notificationClass = $notification->type;
        $data = $notification->data;
        switch ($notificationClass) {
            case 'App\Notifications\TaskAssignedNotification':
            case 'App\Notifications\TaskCompletedNotification':
            case 'App\Notifications\TaskCommentNotification':
            case 'App\Notifications\TaskProgressNotification':
                return redirect()->route('employee-tasks.show', ['employeeTask' => $data['task_id']]);
            case 'App\Notifications\ServiceRequestToItNotification':
                return redirect()->route('service-requests.assignment', ['id' => $data['request_id']]);
            case 'App\Notifications\ServiceUpdateFromIt':
                return redirect()->route('service-requests.assignment-details', ['requestId' => $data['service_assignment_id']]);
            case 'App\Notifications\NewServiceRequestAssigned':    
                return redirect()->route('service-requests.assignment-details', ['requestId' => $data['request_id']]);
            case 'App\Notifications\AdvanceSalarySubmittedNotification':
                return redirect()->route('advance-salary.hod-show', ['application' => $data['advance_salary_application_id']]);
            case 'App\Notifications\AdvanceSalaryHodApprovedNotification':
                return redirect()->route('advance-salary.report');
            case 'App\Notifications\AdvanceSalaryHrApprovedNotification':
                return redirect()->route('advance-salary.accounts-report');
            case 'App\Notifications\AdvanceSalaryHodDecisionNotification':
            case 'App\Notifications\AdvanceSalaryDecisionNotification':
                return redirect()->route('advance-salary.create', ['emp_code' => auth()->user()->emp_code]);
            case 'App\Notifications\OvertimeApplicationNotification':
                $stage = $data['stage'] ?? '';
                $application = isset($data['overtime_application_id'])
                    ? OvertimeApplication::find($data['overtime_application_id'])
                    : null;
                $month = $application?->salary_month ?? now()->format('Y-m');
                $empCode = $application?->emp_code ?? auth()->user()->emp_code;

                return match ($stage) {
                    'submitted' => $application
                        ? redirect()->route('overtime.hod-show', ['application' => $application->id])
                        : redirect()->route('overtime.hod-index'),
                    'hod_approved' => redirect()->route('overtime.report', ['month' => $month]),
                    'hr_approved' => redirect()->route('overtime.finance-report', ['month' => $month]),
                    'hod_rejected', 'hr_rejected', 'finance_rejected', 'finance_approved', 'employee_update'
                        => redirect()->route('overtime.create', ['emp_code' => $empCode, 'month' => $month]),
                    default => redirect()->route('home'),
                };
            default:
                if (isset($data['notice_id'])) {
                    return redirect()->route('notices.review', ['notice' => $data['notice_id']]);
                }

                if (isset($data['request_id'])) {
                    return redirect()->route('service-requests.show', ['id' => $data['request_id']]);
                }

                return redirect()->route('home');
        }
    }    
}
