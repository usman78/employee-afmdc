<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use App\Models\DepartmentStrength;
use App\Jobs\SendAttendanceReportToHodJob;
use App\Jobs\SendDepartmentAttendanceReportJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    public function attendance(Request $request, $emp_code)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate || $endDate) {
            if (!$startDate || !$endDate) {
                return redirect()
                    ->route('attendance', ['emp_code' => $emp_code])
                    ->with('error', 'Both start and end dates are required.');
            }

            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            if ($end->lt($start)) {
                return redirect()
                    ->route('attendance', ['emp_code' => $emp_code])
                    ->with('error', 'End date must be on or after start date.');
            }
        }

        return view('attendance', $this->buildAttendanceData($emp_code, $startDate, $endDate));
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

    public function hrReports()
    {
        $today = Carbon::today()->toDateString();

        $totalStrength = Employee::whereNull('quit_stat')->count();
        $totalStrengthAfmdc = Employee::whereNull('quit_stat')->where('loca_code', 1)->count();
        $totalStrengthAfh = Employee::whereNull('quit_stat')->where('loca_code', 2)->count();

        $genderColumn = null;
        foreach (['gender', 'sex', 'gndr'] as $column) {
            if (Schema::hasColumn('pay_pers', $column)) {
                $genderColumn = $column;
                break;
            }
        }

        $maleStrength = 0;
        $femaleStrength = 0;
        if ($genderColumn) {
            $maleStrength = Employee::whereNull('quit_stat')
                ->whereRaw("UPPER(TRIM({$genderColumn})) IN ('M', 'MALE')")
                ->count();
            $femaleStrength = Employee::whereNull('quit_stat')
                ->whereRaw("UPPER(TRIM({$genderColumn})) IN ('F', 'FEMALE')")
                ->count();
        }

        $totalPresent = DB::table('daily_attnd')
            ->join('pay_pers', 'daily_attnd.emp_code', '=', 'pay_pers.emp_code')
            ->whereNull('daily_attnd.att_stat')
            ->whereNull('pay_pers.quit_stat')
            ->where('pay_pers.loca_code', 1)
            ->whereRaw(
                "daily_attnd.at_date >= TO_DATE(?, 'YYYY-MM-DD') AND daily_attnd.at_date < TO_DATE(?, 'YYYY-MM-DD') + 1",
                [$today, $today]
            )
            ->distinct()
            ->count('daily_attnd.emp_code');

        $lateEmployees = Employee::whereNull('quit_stat')
            ->where('loca_code', 1)
            ->get(['emp_code', 'catg_code', 'loca_code', 'st_time', 'end_time', 'twh']);

        $lateData = $this->buildLateEmployeesForDate($lateEmployees, Carbon::parse($today));
        $lateComing = count($lateData['late_minutes']);

        $absentReport = $this->buildAbsentAttendanceReport($today, '1');
        $absentLeaveCount = $absentReport['rows']->count();

        $percent = function (int $value, int $total): int {
            if ($total <= 0) {
                return 0;
            }
            return (int) round(($value / $total) * 100);
        };

        return view('hr-reports', [
            'total_strength_afmdc' => $totalStrengthAfmdc,
            'total_strength_afh' => $totalStrengthAfh,
            'male_strength' => $maleStrength,
            'female_strength' => $femaleStrength,
            'present_count' => $totalPresent,
            'late_count' => $lateComing,
            'absent_leave_count' => $absentLeaveCount,
            'male_percent' => $percent($maleStrength, $totalStrength),
            'female_percent' => $percent($femaleStrength, $totalStrength),
            'present_percent' => $percent($totalPresent, $totalStrengthAfmdc),
            'late_percent' => $percent($lateComing, $totalStrengthAfmdc),
            'absent_leave_percent' => $percent($absentLeaveCount, $totalStrengthAfmdc),
        ]);
    }

    public function departmentStrengthReport()
    {
        $departments = Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']);
        return view('department-strength-report', [
            'departments' => $departments,
            'report_date' => Carbon::today()->toDateString(),
        ]);
    }
    public function attendanceLateReport()
    {
        $departments = Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']);
        return view('attendance-late-report', [
            'departments' => $departments,
            'report_date' => Carbon::today()->toDateString(),
        ]);
    }
    public function departmentStrengthReportData(Request $request)
    {
        $request->validate([
            'dept_code' => 'nullable',
        ]);

        $deptCode = $request->input('dept_code');
        $departments = Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']);

        // Raw SQL query for accurate department strength data
        $query = <<<SQL
            SELECT dp.dept_desc,
                   dp.dept_code,
                   dg.desg_code,
                   dg.desg_short,
                   SUM(d.no_of_vacancy) AS no_of_vacancy,
                   (
                     SELECT COUNT(*)
                     FROM payroll.pay_pers s
                     WHERE s.desg_code = dg.desg_code
                       AND s.dept_code = dp.dept_code
                       AND s.quit_stat IS NULL
                   ) AS filled_vacancy
            FROM payroll.pay_dept_strength_d d
            JOIN payroll.pay_desig dg ON d.desg_code = dg.desg_code
            JOIN payroll.pay_dept dp ON d.dept_code = dp.dept_code
        SQL;

        if ($deptCode) {
            $query .= " WHERE dp.dept_code = '{$deptCode}'";
        }

        $query .= " GROUP BY dp.dept_desc, dg.desg_code, dp.dept_code, dg.desg_short
                    ORDER BY dp.dept_desc, dg.desg_short";

        $rows = collect(DB::select($query))->map(function ($row) {
            return (array) $row;
        });

        $employeeNames = Employee::whereNull('quit_stat')
            ->where('loca_code', 1)
            ->orderBy('name')
            ->get(['emp_code', 'name', 'dept_code', 'desg_code'])
            ->groupBy(function ($emp) {
                return $emp->dept_code . '|' . $emp->desg_code;
            })
            ->map(function ($group) {
                return $group->map(function ($emp) {
                    return function_exists('capitalizeWords')
                        ? capitalizeWords($emp->name)
                        : ucfirst(strtolower($emp->name));
                })->values();
            });

        $grouped = $rows->groupBy('dept_code')->map(function ($deptRows) use ($employeeNames) {
            $deptRows = $deptRows->map(function ($row) use ($employeeNames) {
                $key = $row['dept_code'] . '|' . $row['desg_code'];
                $names = $employeeNames->get($key, collect());
                $shortage = (int) $row['no_of_vacancy'] - (int) $row['filled_vacancy'];

                return [
                    'dept_code' => $row['dept_code'],
                    'dept_desc' => $row['dept_desc'] ?? '--',
                    'desg_code' => $row['desg_code'],
                    'desg_short' => $row['desg_short'] ?? '--',
                    'no_of_vacancy' => (int) $row['no_of_vacancy'],
                    'filled_vacancy' => (int) $row['filled_vacancy'],
                    'shortage' => $shortage,
                    'names' => $names,
                ];
            });

            $totals = [
                'required' => $deptRows->sum('no_of_vacancy'),
                'filled' => $deptRows->sum('filled_vacancy'),
            ];
            $totals['shortage'] = $totals['required'] - $totals['filled'];

            return [
                'rows' => $deptRows,
                'totals' => $totals,
            ];
        });

        $overallTotals = [
            'required' => $rows->sum('no_of_vacancy'),
            'filled' => $rows->sum('filled_vacancy'),
        ];
        $overallTotals['shortage'] = $overallTotals['required'] - $overallTotals['filled'];

        return view('department-strength-report', [
            'report_date' => Carbon::today()->toDateString(),
            'departments' => $departments,
            'dept_code' => $deptCode,
            'grouped' => $grouped,
            'overall_totals' => $overallTotals,
        ]);
    }

    public function departmentStrengthReportDownload(Request $request)
    {
        $request->validate([
            'dept_code' => 'nullable',
        ]);

        $deptCode = $request->input('dept_code');

        // Raw SQL query for accurate department strength data
        $query = <<<SQL
            SELECT dp.dept_desc,
                   dp.dept_code,
                   dg.desg_code,
                   dg.desg_short,
                   SUM(d.no_of_vacancy) AS no_of_vacancy,
                   (
                     SELECT COUNT(*)
                     FROM payroll.pay_pers s
                     WHERE s.desg_code = dg.desg_code
                       AND s.dept_code = dp.dept_code
                       AND s.quit_stat IS NULL
                   ) AS filled_vacancy
            FROM payroll.pay_dept_strength_d d
            JOIN payroll.pay_desig dg ON d.desg_code = dg.desg_code
            JOIN payroll.pay_dept dp ON d.dept_code = dp.dept_code
        SQL;

        if ($deptCode) {
            $query .= " WHERE dp.dept_code = '{$deptCode}'";
        }

        $query .= " GROUP BY dp.dept_desc, dg.desg_code, dp.dept_code, dg.desg_short
                    ORDER BY dp.dept_desc, dg.desg_short";

        $rows = collect(DB::select($query))->map(function ($row) {
            return (array) $row;
        });

        $employeeNames = Employee::whereNull('quit_stat')
            ->where('loca_code', 1)
            ->orderBy('name')
            ->get(['emp_code', 'name', 'dept_code', 'desg_code'])
            ->groupBy(function ($emp) {
                return $emp->dept_code . '|' . $emp->desg_code;
            })
            ->map(function ($group) {
                return $group->map(function ($emp) {
                    return function_exists('capitalizeWords')
                        ? capitalizeWords($emp->name)
                        : ucfirst(strtolower($emp->name));
                })->values();
            });

        $grouped = $rows->groupBy('dept_code')->map(function ($deptRows) use ($employeeNames) {
            $deptRows = $deptRows->map(function ($row) use ($employeeNames) {
                $key = $row['dept_code'] . '|' . $row['desg_code'];
                $names = $employeeNames->get($key, collect());
                $shortage = (int) $row['no_of_vacancy'] - (int) $row['filled_vacancy'];

                return [
                    'dept_code' => $row['dept_code'],
                    'dept_desc' => $row['dept_desc'] ?? '--',
                    'desg_code' => $row['desg_code'],
                    'desg_short' => $row['desg_short'] ?? '--',
                    'no_of_vacancy' => (int) $row['no_of_vacancy'],
                    'filled_vacancy' => (int) $row['filled_vacancy'],
                    'shortage' => $shortage,
                    'names' => $names,
                ];
            });

            $totals = [
                'required' => $deptRows->sum('no_of_vacancy'),
                'filled' => $deptRows->sum('filled_vacancy'),
            ];
            $totals['shortage'] = $totals['required'] - $totals['filled'];

            return [
                'rows' => $deptRows,
                'totals' => $totals,
            ];
        });

        $overallTotals = [
            'required' => $rows->sum('no_of_vacancy'),
            'filled' => $rows->sum('filled_vacancy'),
        ];
        $overallTotals['shortage'] = $overallTotals['required'] - $overallTotals['filled'];

        $pdf = Pdf::loadView('pdf.department-strength-report', [
            'report_date' => Carbon::today()->toDateString(),
            'grouped' => $grouped,
            'overall_totals' => $overallTotals,
        ]);

        $fileName = 'department_strength_' . Carbon::now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }

    public function attendanceLateReportData(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'dept_code' => 'nullable',
        ]);

        $reportDate = Carbon::parse($request->input('report_date'))->toDateString();
        $deptCode = $request->input('dept_code');
        $lateReport = $this->buildLateAttendanceReport($reportDate, $deptCode);

        return view('attendance-late-report', [
            'departments' => Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']),
            'report_date' => $reportDate,
            'dept_code' => $deptCode,
            'late_rows' => $lateReport['rows'],
            'stats_start' => $lateReport['stats_start'],
            'stats_end' => $lateReport['stats_end'],
        ]);
    }

    public function attendanceAbsentReport()
    {
        $departments = Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']);
        return view('attendance-absent-report', [
            'departments' => $departments,
            'report_date' => Carbon::today()->toDateString(),
            'loca_code' => '1',
        ]);
    }

    public function attendanceAbsentReportData(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'loca_code' => 'nullable|in:1,2',
            'dept_code' => 'nullable',
        ]);

        $reportDate = Carbon::parse($request->input('report_date'))->toDateString();
        $locaCode = $request->input('loca_code', '1');
        $deptCode = $request->input('dept_code');
        $absentReport = $this->buildAbsentAttendanceReport($reportDate, $locaCode, $deptCode);

        return view('attendance-absent-report', [
            'departments' => Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']),
            'report_date' => $reportDate,
            'absent_rows' => $absentReport['rows'],
            'stats_start' => $absentReport['stats_start'],
            'stats_end' => $absentReport['stats_end'],
            'is_non_working_day' => $absentReport['is_non_working_day'],
            'loca_code' => (string) $locaCode,
            'dept_code' => $deptCode,
        ]);
    }

    public function attendanceAbsentReportDownload(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'loca_code' => 'nullable|in:1,2',
            'dept_code' => 'nullable',
        ]);

        $reportDate = Carbon::parse($request->input('report_date'))->toDateString();
        $locaCode = $request->input('loca_code', '1');
        $deptCode = $request->input('dept_code');
        $absentReport = $this->buildAbsentAttendanceReport($reportDate, $locaCode, $deptCode);

        $pdf = Pdf::loadView('pdf.attendance-absent-report', [
            'report_date' => $reportDate,
            'stats_start' => $absentReport['stats_start'],
            'stats_end' => $absentReport['stats_end'],
            'rows' => $absentReport['rows'],
            'is_non_working_day' => $absentReport['is_non_working_day'],
            'loca_code' => (string) $locaCode,
            'dept_code' => $deptCode,
        ]);

        $fileName = 'absent_attendance_' . Carbon::parse($reportDate)->format('Ymd') . '_' . Carbon::now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
    }

    public function attendancePresentReport()
    {
        $departments = Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']);

        return view('attendance-present-report', [
            'departments' => $departments,
            'report_date' => Carbon::today()->toDateString(),
            'dept_code' => null,
        ]);
    }

    public function attendancePresentReportData(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'dept_code' => 'nullable',
        ]);

        $reportDate = Carbon::parse($request->input('report_date'))->toDateString();
        $deptCode = $request->input('dept_code');
        $departments = Department::orderBy('dept_desc')->get(['dept_code', 'dept_desc']);
        $presentReport = $this->buildPresentAttendanceReport($reportDate, $deptCode);

        return view('attendance-present-report', [
            'departments' => $departments,
            'report_date' => $reportDate,
            'dept_code' => $deptCode,
            'present_rows' => $presentReport['rows'],
        ]);
    }
    public function attendanceLateReportDownload(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'dept_code' => 'nullable',
        ]);

        $reportDate = Carbon::parse($request->input('report_date'))->toDateString();
        $deptCode = $request->input('dept_code');
        $lateReport = $this->buildLateAttendanceReport($reportDate, $deptCode);

        $pdf = Pdf::loadView('pdf.attendance-late-report', [
            'report_date' => $reportDate,
            'dept_code' => $deptCode,
            'stats_start' => $lateReport['stats_start'],
            'stats_end' => $lateReport['stats_end'],
            'rows' => $lateReport['rows'],
        ]);

        $fileName = 'late_attendance_' . Carbon::parse($reportDate)->format('Ymd') . '_' . Carbon::now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($fileName);
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
            '2026-02-05', '2026-02-06', '2026-02-07',
            '2026-03-19', '2026-03-20', '2026-03-21', '2026-03-23'
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
                            $overlapStart = $startTimeCarbon->gt($leaveStart) ? $startTimeCarbon : $leaveStart;
                            $overlapEnd = $minIn->lt($leaveEnd) ? $minIn : $leaveEnd;

                            if ($overlapStart->lt($overlapEnd)) {
                                $lateMins = max(0, $lateMins - $overlapStart->diffInMinutes($overlapEnd));
                            }
                        }

                        if ($earlyMins > 0 && $maxOut) {
                            $overlapStart = $maxOut->gt($leaveStart) ? $maxOut : $leaveStart;
                            $overlapEnd = $adjustedEndTimeCarbon->lt($leaveEnd) ? $adjustedEndTimeCarbon : $leaveEnd;

                            if ($overlapStart->lt($overlapEnd)) {
                                $earlyMins = max(0, $earlyMins - $overlapStart->diffInMinutes($overlapEnd));
                            }
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
            'casual' => $leaves->where('leave_code', 1)->sum(fn ($leave) => $leave->l_day ?? $leave->days ?? 0),
            'medical' => $leaves->where('leave_code', 2)->sum(fn ($leave) => $leave->l_day ?? $leave->days ?? 0),
            'annual' => $leaves->where('leave_code', 3)->sum(fn ($leave) => $leave->l_day ?? $leave->days ?? 0),
            'outdoor_duty' => $leaves->where('leave_code', 12)->sum(fn ($leave) => $leave->l_day ?? $leave->days ?? 0),
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

    private function buildLateAttendanceReport(string $reportDate, ?string $deptCode = null): array
    {
        $reportCarbon = Carbon::parse($reportDate);
        $monthStart = $reportCarbon->copy()->startOfMonth()->toDateString();
        $statsEnd = $reportCarbon->gt(Carbon::today()) ? Carbon::today()->toDateString() : $reportCarbon->toDateString();
        $holidays = $this->getHolidayDates();

        $employees = Employee::whereNull('quit_stat')
            ->when($deptCode, function ($query) use ($deptCode) {
                $query->where('dept_code', $deptCode);
            })
            ->with(['designation', 'department'])
            ->orderBy('name')
            ->get(['emp_code', 'name', 'desg_code', 'dept_code', 'catg_code', 'loca_code', 'st_time', 'end_time', 'twh']);

        if ($employees->isEmpty()) {
            return [
                'rows' => collect(),
                'stats_start' => $monthStart,
                'stats_end' => $statsEnd,
            ];
        }

        $lateData = $this->buildLateEmployeesForDate($employees, $reportCarbon, $holidays);
        $lateMinutesByEmp = $lateData['late_minutes'];
        $attendanceByEmp = $lateData['attendance_by_emp'];
        $lateEmpCodes = array_keys($lateMinutesByEmp);

        if (empty($lateEmpCodes)) {
            return [
                'rows' => collect(),
                'stats_start' => $monthStart,
                'stats_end' => $statsEnd,
            ];
        }

        $employeesByCode = $employees->keyBy('emp_code');
        $monthlyStats = $this->buildMonthlyLateStats($employeesByCode, $lateEmpCodes, $monthStart, $statsEnd, $holidays);

        $rows = $employeesByCode->only($lateEmpCodes)->values()->map(function ($employee) use ($monthlyStats, $attendanceByEmp, $reportDate) {
            $stats = $monthlyStats[$employee->emp_code] ?? ['total_late_minutes' => 0, 'total_late_days' => 0];
            $records = $attendanceByEmp->get($employee->emp_code, collect());
            $timeInRaw = $records->min('timein');
            $timeIn = $timeInRaw ? Carbon::parse($timeInRaw)->format('H:i') : '--:--';

            return [
                'date' => $reportDate,
                'emp_code' => $employee->emp_code,
                'name' => function_exists('capitalizeWords') ? capitalizeWords($employee->name) : ucfirst(strtolower($employee->name)),
                'designation' => $employee->designation->desg_short ?? '--',
                'department' => $employee->department->dept_desc ?? '--',
                'time_in' => $timeIn,
                'total_late_days' => $stats['total_late_days'],
                'total_late_minutes' => $stats['total_late_minutes'],
            ];
        });

        return [
            'rows' => $rows,
            'stats_start' => $monthStart,
            'stats_end' => $statsEnd,
        ];
    }

    private function getHolidayDates(): array
    {
        return [
            '2025-03-31','2025-04-01','2025-04-02',
            '2025-05-01','2025-05-07',
            '2025-06-09','2025-06-10','2025-06-11',
            '2025-07-05','2025-08-14',
            '2025-11-09','2025-12-25',
            '2026-02-05', '2026-02-06', '2026-02-07',
            '2026-03-19', '2026-03-20', '2026-03-21', '2026-03-23'
        ];
    }

    private function buildLateEmployeesForDate($employees, Carbon $date, ?array $holidays = null): array
    {
        $holidays = $holidays ?? $this->getHolidayDates();
        $dateStr = $date->toDateString();

        if ($date->isSunday() || in_array($dateStr, $holidays, true)) {
            return [
                'late_minutes' => [],
                'attendance_by_emp' => collect(),
            ];
        }

        $employeeCodes = $employees->pluck('emp_code')->all();

        $attendanceByEmp = Attendance::whereRaw(
                "TRUNC(at_date) = TO_DATE(?, 'YYYY-MM-DD')",
                [$dateStr]
            )
            ->whereIn('emp_code', $employeeCodes)
            ->whereNull('att_stat')
            ->orderBy('timein')
            ->get()
            ->groupBy('emp_code');

        $leavesByEmp = Leave::whereIn('emp_code', $employeeCodes)
            ->whereNot('status', 9)
            ->whereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$dateStr])
            ->whereRaw("TRUNC(to_date)   >= TO_DATE(?, 'YYYY-MM-DD')", [$dateStr])
            ->get()
            ->groupBy('emp_code');

        $lateMinutes = [];
        foreach ($employees as $emp) {
            $records = $attendanceByEmp->get($emp->emp_code, collect());
            if ($records->isEmpty()) {
                continue;
            }

            $leave = $leavesByEmp->get($emp->emp_code, collect())->first();
            $late = $this->calculateLateMinutesForDay($emp, $records, $date, $leave);

            if ($late >= 10) {
                $lateMinutes[$emp->emp_code] = $late;
            }
        }

        return [
            'late_minutes' => $lateMinutes,
            'attendance_by_emp' => $attendanceByEmp,
        ];
    }

    private function buildMonthlyLateStats($employeesByCode, array $empCodes, string $monthStart, string $statsEnd, array $holidays): array
    {
        if (empty($empCodes)) {
            return [];
        }

        $attendanceByEmp = Attendance::whereIn('emp_code', $empCodes)
            ->whereNull('att_stat')
            ->whereRaw(
                "at_date >= TO_DATE(?, 'YYYY-MM-DD') AND at_date < TO_DATE(?, 'YYYY-MM-DD') + 1",
                [$monthStart, $statsEnd]
            )
            ->orderBy('timein')
            ->get()
            ->groupBy('emp_code');

        $leavesByEmp = Leave::whereIn('emp_code', $empCodes)
            ->whereNot('status', 9)
            ->whereRaw(
                "from_date <= TO_DATE(?, 'YYYY-MM-DD') + 1 AND to_date >= TO_DATE(?, 'YYYY-MM-DD')",
                [$statsEnd, $monthStart]
            )
            ->get()
            ->groupBy('emp_code');

        $stats = [];
        $periodStart = Carbon::parse($monthStart);
        $periodEnd = Carbon::parse($statsEnd);

        foreach ($empCodes as $empCode) {
            $emp = $employeesByCode->get($empCode);
            if (!$emp) {
                continue;
            }

            $empAttendanceByDate = $attendanceByEmp
                ->get($empCode, collect())
                ->groupBy(function ($record) {
                    return Carbon::parse($record->at_date)->toDateString();
                });

            $empLeaves = $leavesByEmp->get($empCode, collect());
            $totalLateMinutes = 0;
            $totalLateDays = 0;

            $date = $periodStart->copy();
            while ($date->lte($periodEnd)) {
                $dateStr = $date->toDateString();

                if ($date->isSunday() || in_array($dateStr, $holidays, true)) {
                    $date->addDay();
                    continue;
                }

                $records = $empAttendanceByDate->get($dateStr, collect());
                if ($records->isEmpty()) {
                    $date->addDay();
                    continue;
                }

                $leave = $this->findLeaveForDate($empLeaves, $date);
                $late = $this->calculateLateMinutesForDay($emp, $records, $date, $leave);

                if ($late >= 10) {
                    $totalLateMinutes += $late;
                    $totalLateDays += 1;
                }

                $date->addDay();
            }

            $stats[$empCode] = [
                'total_late_minutes' => $totalLateMinutes,
                'total_late_days' => $totalLateDays,
            ];
        }

        return $stats;
    }

    private function findLeaveForDate($leaves, Carbon $date): ?Leave
    {
        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->from_date)->startOfDay();
            $to = Carbon::parse($leave->to_date)->startOfDay();

            if ($from->lte($date) && $to->gte($date)) {
                return $leave;
            }
        }

        return null;
    }

    private function calculateLateMinutesForDay($emp, $records, Carbon $workDate, ?Leave $leave): int
    {
        $isFullDayLeave = false;
        $leaveStart = null;
        $leaveEnd = null;
        $leaveMins = 0;

        if ($leave) {
            $from = Carbon::parse($leave->from_date);
            $to   = Carbon::parse($leave->to_date);

            if ($from->format('H:i:s') === '00:00:00' &&
                $to->format('H:i:s')   === '00:00:00') {
                $isFullDayLeave = true;
            } else {
                $leaveStart = $from;
                $leaveEnd   = $to;

                // leave minutes (floor)
                $leaveSeconds = $from->diffInSeconds($to);
                $leaveMins = intdiv($leaveSeconds, 60);
            }
        }

        if ($isFullDayLeave) {
            return 0;
        }

        $totalMins = 480;
        if ($emp->catg_code == 2) {
            $totalMins = 360;
        } elseif ($emp->twh == 12) {
            $totalMins = 720;
        }

        $startShift = $workDate->copy()->setTimeFromTimeString($emp->st_time);
        $endShift   = $workDate->copy()->setTimeFromTimeString($emp->end_time);

        if ($emp->twh != 12) {
            if ($emp->catg_code == 2 && $workDate->isFriday()) {
                $totalMins = 300;
                $startShift->setTime(8, 0);
                $endShift->setTime(13, 0);
            } elseif ($emp->catg_code == 1 &&
                $emp->loca_code == 2 &&
                $workDate->isFriday()) {

                $totalMins = 390;
                $startShift->setTime(8, 0);
                $endShift->setTime(14, 30);
            }
        }

        $minsWorked = 0;
        foreach ($records as $record) {
            $minsWorked += minutesWorked($record->timein, $record->timeout);
        }

        $minIn = Carbon::parse($records->min('timein'));
        $late = 0;

        if ($emp->twh == 12) {
            $required = $totalMins;

            if ($leaveMins > 0) {
                $required = max(0, $totalMins - $leaveMins);
            }

            $late = max(0, $required - $minsWorked);

        } else {
            if ($minIn->gt($startShift)) {

                // FIX: calculate exact seconds, then floor minutes
                $lateSeconds = $startShift->diffInSeconds($minIn);
                $late = intdiv($lateSeconds, 60);
            }

            if ($leaveStart && $late > 0) {

                $overlapStart = $startShift->gt($leaveStart) ? $startShift : $leaveStart;
                $overlapEnd   = $minIn->lt($leaveEnd) ? $minIn : $leaveEnd;

                if ($overlapStart->lt($overlapEnd)) {

                    // FIX: overlap also using seconds
                    $overlapSeconds = $overlapStart->diffInSeconds($overlapEnd);
                    $overlapMinutes = intdiv($overlapSeconds, 60);

                    $late = max(0, $late - $overlapMinutes);
                }
            }
        }

        return max(0, (int) $late);
    }
    private function buildAbsentAttendanceReport(string $reportDate, string $locaCode = '1', ?string $deptCode = null): array
    {
        $reportCarbon = Carbon::parse($reportDate);
        $monthStart = $reportCarbon->copy()->startOfMonth()->toDateString();
        $statsEnd = $reportCarbon->gt(Carbon::today()) ? Carbon::today()->toDateString() : $reportCarbon->toDateString();

        $holidays = [
            '2025-03-31','2025-04-01','2025-04-02',
            '2025-05-01','2025-05-07',
            '2025-06-09','2025-06-10','2025-06-11',
            '2025-07-05','2025-08-14',
            '2025-11-09','2025-12-25',
            '2026-02-05', '2026-02-06', '2026-02-07',
            '2026-03-19', '2026-03-20', '2026-03-21', '2026-03-23'
        ];

        $isNonWorkingDay = $reportCarbon->isSunday() || in_array($reportDate, $holidays, true);

        $leaveEmpCodes = Leave::select('emp_code')
            ->whereNot('status', 9)
            ->whereRaw(
                "from_date < TO_DATE(?, 'YYYY-MM-DD') + 1 AND to_date >= TO_DATE(?, 'YYYY-MM-DD')",
                [$reportDate, $reportDate]
            )
            ->distinct()
            ->pluck('emp_code')
            ->all();

        $leaveEmpLookup = array_fill_keys($leaveEmpCodes, true);

        if ($isNonWorkingDay) {
            $employees = Employee::whereNull('quit_stat')
                ->where('loca_code', $locaCode)
                ->when($deptCode, function ($query) use ($deptCode) {
                    $query->where('dept_code', $deptCode);
                })
                ->whereIn('emp_code', $leaveEmpCodes)
                ->with(['designation', 'department'])
                ->orderBy('name')
                ->get(['emp_code', 'name', 'desg_code', 'dept_code', 'loca_code']);
        } else {
            $employees = Employee::whereNull('quit_stat')
                ->where('loca_code', $locaCode)
                ->when($deptCode, function ($query) use ($deptCode) {
                    $query->where('dept_code', $deptCode);
                })
                ->whereNotExists(function ($query) use ($reportDate) {
                    $query->select(DB::raw(1))
                        ->from('daily_attnd')
                        ->whereColumn('daily_attnd.emp_code', 'pay_pers.emp_code')
                        ->whereNull('att_stat')
                        ->whereRaw(
                            "at_date >= TO_DATE(?, 'YYYY-MM-DD') AND at_date < TO_DATE(?, 'YYYY-MM-DD') + 1",
                            [$reportDate, $reportDate]
                        );
                })
                ->with(['designation', 'department'])
                ->orderBy('name')
                ->get(['emp_code', 'name', 'desg_code', 'dept_code', 'loca_code']);
        }

        if ($employees->isEmpty()) {
            return [
                'rows' => collect(),
                'stats_start' => $monthStart,
                'stats_end' => $statsEnd,
                'is_non_working_day' => $isNonWorkingDay,
            ];
        }

        $employeeCodes = $employees->pluck('emp_code')->all();

        $leaveTotals = Leave::selectRaw("
                emp_code,
                SUM(CASE WHEN leave_code = 1 THEN l_day ELSE 0 END) AS casual,
                SUM(CASE WHEN leave_code = 2 THEN l_day ELSE 0 END) AS medical,
                SUM(CASE WHEN leave_code = 3 THEN l_day ELSE 0 END) AS annual,
                SUM(CASE WHEN leave_code = 12 THEN l_day ELSE 0 END) AS od
            ")
            ->whereNot('status', 9)
            ->whereRaw(
                "from_date < TO_DATE(?, 'YYYY-MM-DD') + 1 AND to_date >= TO_DATE(?, 'YYYY-MM-DD')",
                [$statsEnd, $monthStart]
            )
            ->whereIn('emp_code', $employeeCodes)
            ->groupBy('emp_code')
            ->get()
            ->keyBy('emp_code');

        $rows = $employees->map(function ($employee) use ($leaveEmpLookup, $leaveTotals, $reportDate) {
            $totals = $leaveTotals->get($employee->emp_code);

            return [
                'date' => $reportDate,
                'emp_code' => $employee->emp_code,
                'name' => function_exists('capitalizeWords') ? capitalizeWords($employee->name) : ucfirst(strtolower($employee->name)),
                'designation' => $employee->designation->desg_short ?? '--',
                'department' => $employee->department->dept_desc ?? '--',
                'status' => isset($leaveEmpLookup[$employee->emp_code]) ? 'Leave' : 'Absent',
                'casual' => $totals ? (float) $totals->casual : 0,
                'medical' => $totals ? (float) $totals->medical : 0,
                'annual' => $totals ? (float) $totals->annual : 0,
                'od' => $totals ? (float) $totals->od : 0,
            ];
        });

        return [
            'rows' => $rows,
            'stats_start' => $monthStart,
            'stats_end' => $statsEnd,
            'is_non_working_day' => $isNonWorkingDay,
        ];
    }

    private function buildPresentAttendanceReport(string $reportDate, ?string $deptCode = null): array
    {
        $presentQuery = DB::table('daily_attnd')
            ->selectRaw('daily_attnd.emp_code, MIN(timein) as time_in, MAX(timeout) as time_out')
            ->join('pay_pers', 'daily_attnd.emp_code', '=', 'pay_pers.emp_code')
            ->whereNull('daily_attnd.att_stat')
            ->whereNull('pay_pers.quit_stat')
            ->where('pay_pers.loca_code', 1)
            ->whereRaw(
                "daily_attnd.at_date >= TO_DATE(?, 'YYYY-MM-DD') AND daily_attnd.at_date < TO_DATE(?, 'YYYY-MM-DD') + 1",
                [$reportDate, $reportDate]
            );

        if ($deptCode) {
            $presentQuery->where('pay_pers.dept_code', $deptCode);
        }

        $presentRows = $presentQuery
            ->groupBy('daily_attnd.emp_code')
            ->get()
            ->keyBy('emp_code');

        if ($presentRows->isEmpty()) {
            return ['rows' => collect()];
        }

        $employees = Employee::whereIn('emp_code', $presentRows->keys()->all())
            ->whereNull('quit_stat')
            ->where('loca_code', 1)
            ->when($deptCode, function ($query) use ($deptCode) {
                $query->where('dept_code', $deptCode);
            })
            ->with(['designation', 'department'])
            ->orderBy('name')
            ->get(['emp_code', 'name', 'desg_code', 'dept_code']);

        $rows = $employees->map(function ($employee) use ($presentRows, $reportDate) {
            $timeRow = $presentRows->get($employee->emp_code);
            $timeIn = $timeRow && $timeRow->time_in ? Carbon::parse($timeRow->time_in)->format('H:i') : '--:--';
            $timeOut = $timeRow && $timeRow->time_out ? Carbon::parse($timeRow->time_out)->format('H:i') : '--:--';

            return [
                'date' => $reportDate,
                'emp_code' => $employee->emp_code,
                'name' => function_exists('capitalizeWords') ? capitalizeWords($employee->name) : ucfirst(strtolower($employee->name)),
                'designation' => $employee->designation->desg_short ?? '--',
                'department' => $employee->department->dept_desc ?? '--',
                'time_in' => $timeIn,
                'time_out' => $timeOut,
            ];
        });

        return ['rows' => $rows];
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
