<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use App\Jobs\SendAttendanceReportToHodJob;
use App\Jobs\SendDepartmentAttendanceReportJob;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 
use App\Models\Leave;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    private function getRamadanDeductionMinutes($empCategory, Carbon $workDate): int
    {
        $ramadanStart = Carbon::create(2026, 2, 19)->startOfDay();
        $ramadanEnd   = Carbon::create(2026, 3, 20)->endOfDay();

        if (!$workDate->betweenIncluded($ramadanStart, $ramadanEnd)) {
            return 0;
        }

        $locaCode = (int) $empCategory->loca_code;
        $catgCode = (int) $empCategory->catg_code;

        if ($locaCode === 1 && $catgCode !== 2) {
            return 90;
        }

        if ($locaCode === 1 && $catgCode === 2) {
            return 60;
        }

        if ($locaCode === 2 && $catgCode !== 2) {
            return $workDate->isFriday() ? 210 : 60;
        }

        if ($locaCode === 2 && $catgCode === 2) {
            return $workDate->isFriday() ? 30 : 0;
        }

        return 0;
    }

    public function attendance($emp_code)
    {
        $authUser = Auth::user();
        if ($authUser->emp_code != $emp_code) {
            return redirect()->route('home');
        }

        return view('attendance', $this->buildAttendanceData($emp_code));
    }

    public function attendanceReport()
    {
        $departments = Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']);

        return view('attendance-report', [
            'departments' => $departments,
            'searched_start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'searched_end_date' => Carbon::today()->toDateString(),
            'dept_report_date' => Carbon::today()->toDateString(),
        ]);
    }

    public function attendanceReportData(Request $request)
    {
        $request->validate([
            'emp_code' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $empCode = trim($request->input('emp_code'));
        $startDate = Carbon::parse($request->input('start_date'))->toDateString();
        $endDate = Carbon::parse($request->input('end_date'))->toDateString();
        $employee = Employee::where('emp_code', $empCode)->first();

        if (!$employee) {
            return redirect()->route('attendance-report')
                ->withInput()
                ->with('error', 'Employee code not found.');
        }

        return view('attendance-report', array_merge(
            $this->buildAttendanceData($empCode, $startDate, $endDate),
            [
                'departments' => Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']),
                'searched_emp_code' => $empCode,
                'searched_start_date' => $startDate,
                'searched_end_date' => $endDate,
                'dept_report_date' => Carbon::today()->toDateString(),
            ]
        ));
    }

    public function attendanceReportDepartmentData(Request $request)
    {
        $request->validate([
            'dept_code' => 'required',
            'dept_report_date' => 'required|date',
        ]);

        $deptCode = trim($request->input('dept_code'));
        $reportDate = Carbon::parse($request->input('dept_report_date'))->toDateString();
        $departmentReport = $this->buildDepartmentAttendanceData($deptCode, $reportDate);
        if (!$departmentReport) {
            return redirect()->route('attendance-report')
                ->withInput()
                ->with('error', 'Department not found.');
        }

        return view('attendance-report', array_merge([
            'departments' => Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']),
            'searched_start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'searched_end_date' => Carbon::today()->toDateString(),
        ], $departmentReport));
    }

    public function attendanceReportDepartmentEmail(Request $request)
    {
        $request->validate([
            'dept_code' => 'required',
            'dept_report_date' => 'required|date',
            'to_emails' => 'required|string',
            'cc_emails' => 'nullable|string',
        ]);

        $deptCode = trim($request->input('dept_code'));
        $reportDate = Carbon::parse($request->input('dept_report_date'))->toDateString();
        $toEmails = $this->parseEmailList($request->input('to_emails'));
        $ccEmails = $this->parseEmailList($request->input('cc_emails'));

        $departmentReport = $this->buildDepartmentAttendanceData($deptCode, $reportDate);
        if (!$departmentReport) {
            return redirect()->route('attendance-report')
                ->withInput()
                ->with('error', 'Department not found.');
        }

        if (empty($toEmails)) {
            return redirect()->route('attendance-report')
                ->withInput()
                ->with('error', 'At least one To email is required.');
        }

        $invalidTo = collect($toEmails)->first(function ($email) {
            return !filter_var($email, FILTER_VALIDATE_EMAIL);
        });
        if ($invalidTo) {
            return redirect()->route('attendance-report')
                ->withInput()
                ->with('error', "Invalid To email: {$invalidTo}");
        }

        $invalidCc = collect($ccEmails)->first(function ($email) {
            return !filter_var($email, FILTER_VALIDATE_EMAIL);
        });
        if ($invalidCc) {
            return redirect()->route('attendance-report')
                ->withInput()
                ->with('error', "Invalid CC email: {$invalidCc}");
        }

        SendDepartmentAttendanceReportJob::dispatch(
            $deptCode,
            $reportDate,
            $toEmails,
            $ccEmails
        );

        $ccMessage = count($ccEmails) > 0 ? (' + ' . count($ccEmails) . ' CC') : '';
        return redirect()->route('attendance-report')
            ->withInput([
                'dept_code' => $deptCode,
                'dept_report_date' => $reportDate,
            ])
            ->with('success', 'Department attendance report queued for ' . count($toEmails) . " recipient(s){$ccMessage}.");
    }

    public function attendanceReportDepartmentDownload(Request $request)
    {
        $request->validate([
            'dept_code' => 'required',
            'dept_report_date' => 'required|date',
        ]);

        $deptCode = trim($request->input('dept_code'));
        $reportDate = Carbon::parse($request->input('dept_report_date'))->toDateString();

        $departmentReport = $this->buildDepartmentAttendanceData($deptCode, $reportDate);
        if (!$departmentReport) {
            return redirect()->route('attendance-report')
                ->withInput()
                ->with('error', 'Department not found.');
        }

        $pdf = Pdf::loadView('pdf.department-attendance-report', [
            'department_name' => $departmentReport['selected_dept_desc'],
            'report_date' => $departmentReport['dept_report_date'],
            'rows' => $departmentReport['departmentAttendanceRows'],
        ]);

        $fileName = 'department_attendance_' . $deptCode . '_' . Carbon::now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }

    public function buildDepartmentAttendanceData($deptCode, $reportDate): ?array
    {
        $department = Department::where('dept_code', $deptCode)->first();
        if (!$department) {
            return null;
        }

        $employees = Employee::where('dept_code', $deptCode)
            ->whereNull('quit_stat')
            ->with('designation')
            ->orderBy('name')
            ->get(['emp_code', 'name', 'st_time', 'desg_code']);

        $employeeCodes = $employees->pluck('emp_code')->all();
        $attendanceByEmp = collect();
        $leavesByEmp = collect();

        if (!empty($employeeCodes)) {
            $attendanceByEmp = Attendance::whereRaw(
                    "TRUNC(at_date) = TO_DATE(?, 'YYYY-MM-DD')",
                    [$reportDate]
                )
                ->whereIn('emp_code', $employeeCodes)
                ->whereNull('att_stat')
                ->orderBy('timein')
                ->get()
                ->groupBy('emp_code');

            $leavesByEmp = Leave::whereIn('emp_code', $employeeCodes)
                ->whereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$reportDate])
                ->whereRaw("TRUNC(to_date)   >= TO_DATE(?, 'YYYY-MM-DD')", [$reportDate])
                ->whereNot('status', 9)
                ->get()
                ->groupBy('emp_code');
        }

        $departmentAttendanceRows = $employees->map(function ($employee) use ($attendanceByEmp, $leavesByEmp, $reportDate) {
            $records = $attendanceByEmp->get($employee->emp_code, collect());
            $hasLeave = $leavesByEmp->has($employee->emp_code);

            if ($records->isNotEmpty()) {
                $minTimeIn = $records->min('timein');
                $maxTimeOut = $records->max('timeout');
                $timeStatus = '--';
                if ($minTimeIn && $employee->st_time) {
                    $shiftStart = Carbon::parse($reportDate . ' ' . $employee->st_time);
                    $minIn = Carbon::parse($minTimeIn);
                    if ($minIn->gt($shiftStart)) {
                        $lateSeconds = $shiftStart->diffInSeconds($minIn);
                        $timeStatus = $lateSeconds >= 630 ? 'Late' : 'On-time';
                    } else {
                        $timeStatus = 'On-time';
                    }
                }

                return [
                    'emp_code' => $employee->emp_code,
                    'name' => ucfirst($employee->name),
                    'designation' => $employee->designation->desg_short ?? '--',
                    'time_in' => $minTimeIn ? Carbon::parse($minTimeIn)->format('H:i') : '--:--',
                    'time_out' => $maxTimeOut ? Carbon::parse($maxTimeOut)->format('H:i') : '--:--',
                    'status' => 'Present',
                    'time_status' => $timeStatus,
                ];
            }

            return [
                'emp_code' => $employee->emp_code,
                'name' => ucfirst($employee->name),
                'designation' => $employee->designation->desg_short ?? '--',
                'time_in' => '--:--',
                'time_out' => '--:--',
                'status' => $hasLeave ? 'Leave' : 'Absent',
                'time_status' => '--',
            ];
        })->values();

        return [
            'selected_dept_code' => $deptCode,
            'selected_dept_desc' => $department->dept_desc,
            'dept_report_date' => $reportDate,
            'departmentAttendanceRows' => $departmentAttendanceRows,
        ];
    }

    public function attendanceReportDownload(Request $request, $emp_code)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->toDateString();
        $endDate = Carbon::parse($request->input('end_date'))->toDateString();
        $employee = Employee::with(['department', 'designation'])
            ->where('emp_code', $emp_code)
            ->first();

        if (!$employee) {
            return redirect()->route('attendance-report')
                ->with('error', 'Employee code not found.');
        }

        $reportData = $this->buildAttendanceData($emp_code, $startDate, $endDate);
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

        $periodStart = $startDate;
        $periodEnd   = $endDate;

        $pdf = Pdf::loadView('pdf.attendance-report', [
            'attendance' => $attendance,
            'emp_name' => $reportData['emp_name'] ?? ucfirst($employee->name),
            'emp_code' => $emp_code,
            'emp_department' => $reportData['emp_department'] ?? '--',
            'emp_designation' => $reportData['emp_designation'] ?? '--',
            'late_minutes' => $lateMinutes,
            'early_minutes' => $earlyMinutes,
            'total_minutes' => $lateMinutes + $earlyMinutes,
            'late_days' => $lateDays,
            'leave_counts' => $reportData['leave_counts'] ?? [],
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);

        $now = Carbon::now()->format('Ymd_His');
        return $pdf->stream("attendance_report_{$emp_code}_{$now}.pdf");
    }

    public function attendanceReportEmail(Request $request, $emp_code)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'additional_to_emails' => 'nullable|string',
            'cc_emails' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->toDateString();
        $endDate = Carbon::parse($request->input('end_date'))->toDateString();
        $additionalToEmails = $this->parseEmailList($request->input('additional_to_emails'));
        $ccEmails = $this->parseEmailList($request->input('cc_emails'));
        $employee = Employee::where('emp_code', $emp_code)->first();

        if (!$employee) {
            return redirect()->route('attendance-report')
                ->with('error', 'Employee code not found.');
        }

        $hodCode = hisBoss($emp_code);
        $hodEmail = null;
        if ($hodCode) {
            $hodEmail = Employee::where('emp_code', $hodCode)->value('afmdcemail');
        }

        if (empty($hodEmail)) {
            return redirect()->route('attendance-report')
                ->withInput([
                    'emp_code' => $emp_code,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'additional_to_emails' => $request->input('additional_to_emails'),
                    'cc_emails' => $request->input('cc_emails'),
                ])
                ->with('error', 'email of the HOD is not in the records.');
        }

        \Log::info('Attendance report email request', [
            'emp_code' => $emp_code,
            'hod_email' => $hodEmail,
            'additional_to' => $additionalToEmails,
            'cc' => $ccEmails,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $invalidAdditionalTo = collect($additionalToEmails)->first(function ($email) {
            return !filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if ($invalidAdditionalTo) {
            return redirect()->route('attendance-report')
                ->withInput([
                    'emp_code' => $emp_code,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'additional_to_emails' => $request->input('additional_to_emails'),
                    'cc_emails' => $request->input('cc_emails'),
                ])
                ->with('error', "Invalid additional To email: {$invalidAdditionalTo}");
        }

        $invalidCc = collect($ccEmails)->first(function ($email) {
            return !filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if ($invalidCc) {
            return redirect()->route('attendance-report')
                ->withInput([
                    'emp_code' => $emp_code,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'additional_to_emails' => $request->input('additional_to_emails'),
                    'cc_emails' => $request->input('cc_emails'),
                ])
                ->with('error', "Invalid CC email: {$invalidCc}");
        }

        SendAttendanceReportToHodJob::dispatch(
            $emp_code,
            $startDate,
            $endDate,
            $hodEmail,
            $additionalToEmails,
            $ccEmails
        );

        $toMessage = count($additionalToEmails) > 0 ? (' + ' . count($additionalToEmails) . ' additional To') : '';
        $ccMessage = count($ccEmails) > 0 ? (' + ' . count($ccEmails) . ' CC') : '';

        return redirect()->route('attendance-report')
            ->withInput([
                'emp_code' => $emp_code,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ])
            ->with('success', "Attendance report queued for HOD ({$hodEmail}){$toMessage}{$ccMessage}.");
    }

    public function buildAttendanceData($emp_code, $startDate = null, $endDate = null)
    {
        $emp_category = Employee::select(
            'catg_code', 'loca_code', 'st_time', 'end_time', 'twh'
        )->where('emp_code', $emp_code)->first();

        if (!$emp_category) {
            return [
                'attendance' => collect(),
                'leaves' => collect(),
                'emp_name' => 'Unknown Employee',
                'emp_code' => $emp_code,
            ];
        }

        /* -------------------------
        Required minutes
        -------------------------- */
        $totalMins = 480;

        if ($emp_category->catg_code == 2) {
            $totalMins = 360;
        } elseif ($emp_category->twh == 12) {
            $totalMins = 720;
        }

        /* -------------------------
        Holidays
        -------------------------- */
        $holidays = [
            '2025-03-31','2025-04-01','2025-04-02',
            '2025-05-01','2025-05-07',
            '2025-06-09','2025-06-10','2025-06-11',
            '2025-07-05','2025-08-14',
            '2025-11-09','2025-12-25',
            '2026-02-05', '2026-02-06', '2026-02-07'
        ];

        $start_date = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $selectedEndDate = $endDate ? Carbon::parse($endDate)->startOfDay() : Carbon::today();
        $end_date = $selectedEndDate->gt(Carbon::today()) ? Carbon::today() : $selectedEndDate;

        $allDates = collect();
        $tempDate = $start_date->copy();

        while ($tempDate->lte($end_date)) {
            $dateString = $tempDate->toDateString();
            $isHoliday  = in_array($dateString, $holidays);

            if ($tempDate->isSunday() || $isHoliday) {
                $allDates->push([
                    'at_date'   => $dateString,
                    'time_logs' => [],
                    'is_sunday' => $tempDate->isSunday(),
                    'is_holiday'=> $isHoliday,
                    'is_leave'  => false
                ]);

                $tempDate->addDay();
                continue;
            }

            $attendanceRecords = Attendance::whereRaw(
                    "TRUNC(at_date) = TO_DATE(?, 'YYYY-MM-DD')", [$dateString]
                )
                ->where('emp_code', $emp_code)
                ->whereNull('att_stat')
                ->orderBy('timein')
                ->get();

            $isLeave = false;
            $leaveType = null;
            $leaveStart = null;
            $leaveEnd   = null;
            $leaveMins  = 0;
            $isFullDayLeave = false;

            if (ifLeaveExists($emp_code, $dateString)) {
                $leave = Leave::whereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                    ->whereRaw("TRUNC(to_date)   >= TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                    ->where('emp_code', $emp_code)
                    ->whereNot('status', 9)
                    ->first();

                if ($leave) {
                    $isLeave = true;
                    $leaveType = leaveDescription(
                        $leave->leave_code,
                        $leave->from_date,
                        $leave->to_date
                    );

                    $from = Carbon::parse($leave->from_date);
                    $to   = Carbon::parse($leave->to_date);

                    if ($from->format('H:i:s') === '00:00:00' &&
                        $to->format('H:i:s')   === '00:00:00') {
                        $isFullDayLeave = true;
                    } else {
                        $leaveStart = $from;
                        $leaveEnd   = $to;
                        $leaveMins  = $from->diffInMinutes($to);
                    }
                }
            }

            if ($attendanceRecords->isEmpty()) {
                $allDates->push([
                    'at_date'   => $dateString,
                    'time_logs' => [],
                    'is_sunday' => false,
                    'is_holiday'=> $isHoliday,
                    'is_leave'  => $isLeave,
                    'leave_type'=> $isLeave ? $leaveType : null
                ]);

                $tempDate->addDay();
                continue;
            }

            $workDate = Carbon::parse($dateString);
            $startTimeCarbon = $workDate->copy()->setTimeFromTimeString($emp_category->st_time);
            $endTimeCarbon   = $workDate->copy()->setTimeFromTimeString($emp_category->end_time);

            if ($emp_category->twh != 12) {
                if ($emp_category->catg_code == 2 && $workDate->isFriday()) {
                    $totalMins = 300;
                    $startTimeCarbon->setTime(8, 0);
                    $endTimeCarbon->setTime(13, 0);
                } elseif ($emp_category->catg_code == 1 &&
                        $emp_category->loca_code == 2 &&
                        $workDate->isFriday()) {
                    $totalMins = 390;
                    $startTimeCarbon->setTime(8, 0);
                    $endTimeCarbon->setTime(14, 30);
                }
            }

            $ramadanDeductionMins = $this->getRamadanDeductionMinutes($emp_category, $workDate);
            $adjustedEndTimeCarbon = $endTimeCarbon->copy()->subMinutes($ramadanDeductionMins);

            $minsWorked = 0;
            $timeLogs   = [];

            foreach ($attendanceRecords as $record) {
                $worked = minutesWorked($record->timein, $record->timeout);
                $minsWorked += $worked;

                $timeLogs[] = [
                    'timein'          => $record->timein,
                    'timeout'         => $record->timeout,
                    'worked_minutes'  => $worked
                ];
            }

            $lateMins  = 0;
            $earlyMins = 0;

            if (!$isFullDayLeave) {
                $minIn  = Carbon::parse($attendanceRecords->min('timein'));
                if ($attendanceRecords->whereNull('timeout')->isNotEmpty()) {
                    $maxOut = null;
                } else {
                    $maxOut = Carbon::parse($attendanceRecords->max('timeout'));
                }

                if ($emp_category->twh == 12) {
                    $required = $totalMins;

                    if ($leaveMins > 0) {
                        $required = max(0, $totalMins - $leaveMins);
                    }

                    $lateMins = max(0, $required - $minsWorked);
                } else {
                    if ($minIn->gt($startTimeCarbon)) {
                        $lateMins = $startTimeCarbon->diffInMinutes($minIn);
                    }

                    if ($maxOut == null) {
                        $earlyMins = null;
                    } elseif ($maxOut->lt($adjustedEndTimeCarbon)) {
                        $earlyMins = $maxOut->diffInMinutes($adjustedEndTimeCarbon);
                    }

                    if ($leaveStart) {
                        if ($lateMins > 0) {
                            $lateMins = 0;
                        }

                        if ($earlyMins > 0) {
                            $earlyMins = 0;
                        }
                    }
                }
            }

            $lateMins  = max(0, $lateMins);
            $earlyMins = max(0, $earlyMins);
            $leaveRemark = null;

            if (!$isLeave) {
                if ($minsWorked >= ($totalMins / 2) && $minsWorked < $totalMins - 120) {
                    $leaveRemark = 'Half Day Eligible';
                } elseif ($minsWorked < ($totalMins / 2)) {
                    $leaveRemark = 'Full Day Eligible';
                }
            }

            $allDates->push([
                'at_date'           => $dateString,
                'time_logs'         => $timeLogs,
                'worked_minutes'    => $minsWorked,
                'late_minutes'      => $lateMins,
                'early_minutes'     => $earlyMins,
                'is_sunday'         => false,
                'is_holiday'        => $isHoliday,
                'is_leave'          => $isLeave,
                'leave_type'        => $isLeave ? $leaveType : null,
                'short_duty_status' => $leaveRemark
            ]);

            $tempDate->addDay();
        }

        $attendance = $allDates->sortBy('at_date')->values();
        $employee = Employee::where('emp_code', $emp_code)->first();
        $hodCode = hisBoss($emp_code);
        $hodEmail = null;
        if ($hodCode) {
            $hodEmail = Employee::where('emp_code', $hodCode)->value('afmdcemail');
        }
        $leaves = Leave::where('emp_code', $emp_code)
            ->whereNot('status', 9)
            ->where('from_date', '>=', $start_date)
            ->where('to_date', '<=', $end_date)
            ->get();

        $leaveCounts = [
            'casual' => $leaves->where('leave_code', 1)->count(),
            'medical' => $leaves->where('leave_code', 2)->count(),
            'annual' => $leaves->where('leave_code', 3)->count(),
            'outdoor_duty' => $leaves->where('leave_code', 12)->count(),
        ];

        return [
            'attendance' => $attendance,
            'leaves'     => $leaves,
            'leave_counts' => $leaveCounts,
            'emp_name'   => $employee ? ucfirst($employee->name) : 'Unknown Employee',
            'emp_code'   => $employee ? $employee->emp_code : $emp_code,
            'emp_department' => $employee && $employee->department ? $employee->department->dept_desc : '--',
            'emp_designation' => $employee && $employee->designation ? $employee->designation->desg_short : '--',
            'hod_email'  => $hodEmail,
            'report_start_date' => $start_date->toDateString(),
            'report_end_date' => $end_date->toDateString(),
        ];
    }

    private function parseEmailList(?string $emails): array
    {
        if (!$emails) {
            return [];
        }

        $emailList = preg_split('/[\s,;]+/', $emails);
        $emailList = array_filter(array_map('trim', $emailList));

        return array_values(array_unique($emailList));
    }

}

function leaveDescription($leaveCode, $fromDate = null, $toDate = null)
{
    // identify full day leave using isStartOfDay and isEndOfDay methods
    if ($fromDate && $toDate) {
        $fromDate = Carbon::parse($fromDate);
        $toDate = Carbon::parse($toDate);
        if ($fromDate->isStartOfDay() && $toDate->isStartOfDay()) {
            switch ($leaveCode) {
                case '1':
                    return 'Full Day Casual Leave';
                case '2':
                    return 'Full Day Medical Leave';
                case '3':
                    return 'Full Day Annual Leave';
                case '5':
                    return 'Full Day Without Pay Leave';
                case '12':
                    return 'Full Day Outdoor Duty';
                default:
                    return 'Unknown Leave';
            }
        }
    }
    switch ($leaveCode) {
        case '1':
            return 'Casual ' . date('H:i', strtotime($fromDate)) . ' to ' . date('H:i',strtotime($toDate));
        case '2':
            return 'Medical ' . date('H:i', strtotime($fromDate)) . ' to ' . date('H:i',strtotime($toDate));
        case '3':
            return 'Annual '  . date('H:i', strtotime($fromDate)) . ' to ' . date('H:i',strtotime($toDate));
        case '5':
            return 'Without Pay Leave';
        case '8':
            return 'Short ' . date('H:i', strtotime($fromDate)) . ' to ' . date('H:i',strtotime($toDate));
        case '12':
            return 'Outdoor Duty';
        default:
            return 'Unknown Leave';
    }
}

function minutesWorked($timein, $timeout) {
    if ($timein && $timeout) {
        $in = Carbon::parse($timein);
        $out = Carbon::parse($timeout);
        $workedMinutes = round($in->diffInMinutes($out));
        return $workedMinutes;
    }
    return 0;
}
