<?php

namespace App\Jobs;

use App\Http\Controllers\AttendanceController;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendAttendanceReportToHodJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $empCode,
        public string $startDate,
        public string $endDate,
        public string $hodEmail
    ) {
    }

    public function handle(): void
    {
        Log::info('Starting SendAttendanceReportToHodJob for empCode: ' . $this->empCode);
        $employee = Employee::where('emp_code', $this->empCode)->first();
        if (!$employee) {
            return;
        }

        $controller = app(AttendanceController::class);
        $reportData = $controller->buildAttendanceData(
            $this->empCode,
            $this->startDate,
            $this->endDate
        );

        $attendance = $reportData['attendance'] ?? collect();

        $lateMinutes = $attendance->sum(function ($record) {
            $late = intval($record['late_minutes'] ?? 0);
            return $late >= 10 ? $late : 0;
        });

        $earlyMinutes = $attendance->sum(function ($record) {
            return max(0, intval(round($record['early_minutes'] ?? 0)));
        });

        $lateDays = $attendance->filter(function ($record) {
            return intval($record['late_minutes'] ?? 0) >= 10;
        })->count();

        $pdf = Pdf::loadView('pdf.attendance-report', [
            'attendance' => $attendance,
            'emp_name' => $reportData['emp_name'] ?? ucfirst($employee->name),
            'emp_code' => $this->empCode,
            'late_minutes' => $lateMinutes,
            'early_minutes' => $earlyMinutes,
            'total_minutes' => $lateMinutes + $earlyMinutes,
            'late_days' => $lateDays,
            'period_start' => $this->startDate,
            'period_end' => $this->endDate,
        ]);

        $fileName = "attendance_report_{$this->empCode}_" . Carbon::now()->format('Ymd_His') . '.pdf';

        Mail::send('emails.attendance-report', [
            'emp_name' => $reportData['emp_name'],
            'emp_code' => $this->empCode,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ], function ($message) use ($reportData, $pdf, $fileName) {
            $message->to($this->hodEmail)
                ->subject("Attendance Report - {$reportData['emp_name']} ({$this->empCode})")
                ->attachData($pdf->output(), $fileName, [
                    'mime' => 'application/pdf',
                ]);
        });
        Log::info('Completed SendAttendanceReportToHodJob for empCode: ' . $this->empCode);
    }
}
