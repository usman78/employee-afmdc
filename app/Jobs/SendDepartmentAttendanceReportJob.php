<?php

namespace App\Jobs;

use App\Http\Controllers\AttendanceController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDepartmentAttendanceReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $deptCode,
        public string $reportDate,
        public array $toEmails = [],
        public array $ccEmails = []
    ) {
    }

    public function handle(): void
    {
        Log::info('Starting SendDepartmentAttendanceReportJob for dept: ' . $this->deptCode);

        $controller = app(AttendanceController::class);
        $departmentData = $controller->buildDepartmentAttendanceData($this->deptCode, $this->reportDate);

        if (!$departmentData) {
            return;
        }

        $pdf = Pdf::loadView('pdf.department-attendance-report', [
            'department_name' => $departmentData['selected_dept_desc'],
            'report_date' => $departmentData['dept_report_date'],
            'rows' => $departmentData['departmentAttendanceRows'],
        ]);

        $fileName = 'department_attendance_' . $this->deptCode . '_' . Carbon::now()->format('Ymd_His') . '.pdf';

        Mail::send('emails.department-attendance-report', [
            'department_name' => $departmentData['selected_dept_desc'],
            'report_date' => $departmentData['dept_report_date'],
        ], function ($message) use ($departmentData, $pdf, $fileName) {

            $message->to($this->toEmails)
                ->subject(
                    'Department Attendance Report - ' .
                    $departmentData['selected_dept_desc'] .
                    ' (' . dateFormat($departmentData['dept_report_date']) . ')'
                )
                ->attachData($pdf->output(), $fileName, [
                    'mime' => 'application/pdf',
                ]);

            if (!empty($this->ccEmails)) {
                $message->cc($this->ccEmails);
            }
        });

        Log::info('Completed SendDepartmentAttendanceReportJob for dept: ' . $this->deptCode);
    }
}
