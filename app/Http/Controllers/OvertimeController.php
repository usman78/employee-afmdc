<?php

namespace App\Http\Controllers;

use App\Models\Holidays;
use App\Models\OvertimeApplication;
use App\Models\Roster;
use App\Models\Salary;
use App\Models\User;
use App\Notifications\OvertimeApplicationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OvertimeController extends Controller
{
    public function create(Request $request, $empCode)
    {
        $this->ensureOwnEmployee($empCode);

        $employee = User::with(['designation', 'department'])->where('emp_code', $empCode)->firstOrFail();

        if (! $this->canEmployeeApplyOvertime($employee)) {
            return redirect()
                ->route('home')
                ->with('error', 'Overtime is not allowed for this employee.');
        }

        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $summary = $this->buildSummary($employee, $month);

        $applications = OvertimeApplication::where('emp_code', $empCode)
            ->where('salary_month', $month)
            ->orderByDesc('overtime_date')
            ->orderByDesc('applied_at')
            ->get();

        return view('overtime.create', [
            'employee' => $employee,
            'month' => $month,
            'summary' => $summary,
            'eligibleRows' => $summary['eligible_rows'],
            'applications' => $applications,
        ]);
    }

    public function store(Request $request, $empCode)
    {
        $this->ensureOwnEmployee($empCode);

        $employee = User::with(['designation', 'department'])->where('emp_code', $empCode)->firstOrFail();

        if (! $this->canEmployeeApplyOvertime($employee)) {
            return redirect()
                ->route('overtime.create', ['emp_code' => $empCode])
                ->with('error', 'Overtime is not allowed for this employee.');
        }

        $validated = $request->validate([
            'overtime_date' => 'required|date',
            'overtime_minutes' => 'required|integer|min:60',
            'remarks' => 'required|string|max:1000',
        ]);

        $month = Carbon::parse($validated['overtime_date'])->format('Y-m');
        $summary = $this->buildSummary($employee, $month);
        $eligibleRow = $summary['eligible_rows']->firstWhere('date', Carbon::parse($validated['overtime_date'])->toDateString());

        if (! $eligibleRow) {
            return redirect()
                ->route('overtime.create', ['emp_code' => $empCode, 'month' => $month])
                ->with('error', 'This overtime date is not eligible for claim.');
        }

        // Validate that overtime_minutes doesn't exceed the detected overtime
        if ($validated['overtime_minutes'] > $eligibleRow['overtime_minutes']) {
            return redirect()
                ->route('overtime.create', ['emp_code' => $empCode, 'month' => $month])
                ->with('error', 'Claimed OT minutes cannot exceed detected overtime minutes.');
        }

        if ($this->hasActiveApplication($empCode, $eligibleRow['date'])) {
            return redirect()
                ->route('overtime.create', ['emp_code' => $empCode, 'month' => $month])
                ->with('error', 'An active overtime application already exists for this date.');
        }

        // Calculate amount based on user-entered overtime minutes
        $claimedAmount = $this->calculateAmount($eligibleRow['hourly_rate'], $validated['overtime_minutes']);

        $application = OvertimeApplication::create([
            'id' => getIncrementedId('OVERTIME_APPLICATIONS', 'ID'),
            'emp_code' => $empCode,
            'salary_month' => $month,
            'overtime_date' => $eligibleRow['date'],
            'gross_salary' => $summary['gross_salary'],
            'overtime_minutes' => $validated['overtime_minutes'],
            'hourly_rate' => $eligibleRow['hourly_rate'],
            'calculated_amount' => $claimedAmount,
            'sanctioned_minutes' => null,
            'sanctioned_amount' => null,
            'shift_start' => $eligibleRow['shift_start'],
            'shift_end' => $eligibleRow['shift_end'],
            'time_in' => $eligibleRow['time_in'],
            'time_out' => $eligibleRow['time_out'],
            'is_holiday' => $eligibleRow['is_holiday'] ? 'Y' : 'N',
            'is_security' => $eligibleRow['is_security'] ? 'Y' : 'N',
            'remarks' => $validated['remarks'],
            'status' => OvertimeApplication::STATUS_PENDING,
            'applied_at' => now(),
        ]);

        $hodCode = hisBoss($empCode);
        if ($hodCode) {
            $hod = User::where('emp_code', $hodCode)->first();
            $hod?->notify(new OvertimeApplicationNotification($application, 'submitted'));
        }

        return redirect()
            ->route('overtime.create', ['emp_code' => $empCode, 'month' => $month])
            ->with('success', 'Overtime application submitted successfully and sent to HOD.');
    }

    public function editMinutes(Request $request, $applicationId)
    {
        $application = OvertimeApplication::findOrFail($applicationId);
        
        // Ensure only the employee can edit their own pending application
        $this->ensureOwnEmployee($application->emp_code);

        if ($application->status !== OvertimeApplication::STATUS_PENDING) {
            return redirect()
                ->route('overtime.create', ['emp_code' => $application->emp_code, 'month' => $application->salary_month])
                ->with('error', 'Only pending applications can be edited.');
        }

        $validated = $request->validate([
            'overtime_minutes' => 'required|integer|min:60',
        ]);

        $newMinutes = $validated['overtime_minutes'];

        // Validate that new minutes don't exceed original detected overtime
        if ($newMinutes > $application->overtime_minutes) {
            return redirect()
                ->route('overtime.create', ['emp_code' => $application->emp_code, 'month' => $application->salary_month])
                ->with('error', 'Cannot exceed the originally detected overtime minutes.');
        }

        // Recalculate the amount based on new minutes
        $newAmount = $this->calculateAmount($application->hourly_rate, $newMinutes);

        // Update the application
        $application->update([
            'overtime_minutes' => $newMinutes,
            'calculated_amount' => $newAmount,
        ]);

        return redirect()
            ->route('overtime.create', ['emp_code' => $application->emp_code, 'month' => $application->salary_month])
            ->with('success', 'Overtime minutes updated successfully. New amount: PKR ' . number_format($newAmount, 2));
    }

    public function hodIndex(Request $request)
    {
        $this->ensureBoss();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $subordinateCodes = auth()->user()->teamMembersOfHod()->pluck('emp_code_l')->filter()->values();

        $applications = OvertimeApplication::with(['employee.designation', 'employee.department'])
            ->whereIn('emp_code', $subordinateCodes)
            ->where('salary_month', $month)
            ->orderByRaw("CASE WHEN STATUS = 'pending' THEN 1 WHEN STATUS = 'HOD_approved' THEN 2 ELSE 3 END")
            ->orderByDesc('applied_at')
            ->get();

        return view('overtime.hod-index', [
            'applications' => $applications,
            'month' => $month,
        ]);
    }

    public function hodShow($application)
    {
        $overtimeApplication = OvertimeApplication::with(['employee.designation', 'employee.department'])
            ->where('id', $application)
            ->firstOrFail();

        $this->ensureHodCanApprove($overtimeApplication);

        return view('overtime.hod-show', [
            'application' => $overtimeApplication,
        ]);
    }

    public function hodDecision(Request $request, $application)
    {
        $overtimeApplication = OvertimeApplication::where('id', $application)->firstOrFail();
        $this->ensureHodCanApprove($overtimeApplication);

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($overtimeApplication->status !== OvertimeApplication::STATUS_PENDING) {
            return redirect()
                ->route('overtime.hod-show', $overtimeApplication->id)
                ->with('error', 'Only pending overtime applications can be updated by HOD.');
        }

        $status = $validated['decision'] === 'approve'
            ? OvertimeApplication::STATUS_HOD_APPROVED
            : OvertimeApplication::STATUS_HOD_REJECTED;

        $overtimeApplication->forceFill([
            'status' => $status,
            'hod_approved_by' => auth()->user()->emp_code,
            'hod_approved_at' => now(),
            'hod_remarks' => $validated['remarks'] ?? null,
        ])->save();

        if ($status === OvertimeApplication::STATUS_HOD_APPROVED) {
            $this->notifyHr($overtimeApplication);
        } else {
            $this->notifyEmployee($overtimeApplication, 'hod_rejected');
        }

        return redirect()
            ->route('overtime.hod-show', $overtimeApplication->id)
            ->with('success', 'Overtime application updated successfully.');
    }

    public function report(Request $request)
    {
        $this->ensureHr();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status');

        $applications = OvertimeApplication::with(['employee.designation', 'employee.department'])
            ->where('salary_month', $month)
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByRaw("CASE WHEN STATUS = 'HOD_approved' THEN 1 WHEN STATUS = 'pending' THEN 2 ELSE 3 END")
            ->orderByDesc('applied_at')
            ->get();

        return view('overtime.report', [
            'applications' => $applications,
            'month' => $month,
            'status' => $status,
            'statuses' => $this->hrReportStatuses(),
        ]);
    }

    public function eligibilityReport(Request $request)
    {
        $this->ensureHr();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $reportRows = $this->buildEligibilityReportRows($month);

        return view('overtime.eligibility-report', [
            'month' => $month,
            'reportRows' => $reportRows,
            'employeeCount' => $reportRows->count(),
            'eligibleDateCount' => $reportRows->sum(fn ($row) => $row['eligible_rows']->count()),
            'totalMinutes' => $reportRows->sum('total_minutes'),
            'totalAmount' => $reportRows->sum('total_amount'),
        ]);
    }

    public function downloadEligibilityReport(Request $request)
    {
        $this->ensureHr();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $reportRows = $this->buildEligibilityReportRows($month);

        $pdf = Pdf::loadView('pdf.overtime-eligibility-report', [
            'month' => $month,
            'reportRows' => $reportRows,
            'employeeCount' => $reportRows->count(),
            'eligibleDateCount' => $reportRows->sum(fn ($row) => $row['eligible_rows']->count()),
            'totalMinutes' => $reportRows->sum('total_minutes'),
            'totalAmount' => $reportRows->sum('total_amount'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("overtime_eligibility_report_{$month}.pdf");
    }

    public function hrDecision(Request $request, $application)
    {
        $this->ensureHr();

        $overtimeApplication = OvertimeApplication::where('id', $application)->firstOrFail();

        if ($overtimeApplication->status !== OvertimeApplication::STATUS_HOD_APPROVED) {
            return redirect()
                ->route('overtime.report', ['month' => $overtimeApplication->salary_month])
                ->with('error', 'Only HOD approved applications can be updated by HR.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'sanctioned_minutes' => [
                'required_if:decision,approve',
                'nullable',
                'integer',
                'min:60',
                'max:' . (int) $overtimeApplication->overtime_minutes,
            ],
            'remarks' => 'nullable|string|max:1000',
        ]);

        $status = $validated['decision'] === 'approve'
            ? OvertimeApplication::STATUS_HR_APPROVED
            : OvertimeApplication::STATUS_HR_REJECTED;

        $sanctionedMinutes = $status === OvertimeApplication::STATUS_HR_APPROVED
            ? (int) $validated['sanctioned_minutes']
            : null;

        $overtimeApplication->forceFill([
            'status' => $status,
            'sanctioned_minutes' => $sanctionedMinutes,
            'sanctioned_amount' => $status === OvertimeApplication::STATUS_HR_APPROVED
                ? $this->calculateAmount($overtimeApplication->hourly_rate, $sanctionedMinutes)
                : null,
            'hr_approved_by' => auth()->user()->emp_code,
            'hr_approved_at' => now(),
            'hr_remarks' => $validated['remarks'] ?? null,
        ])->save();

        if ($status === OvertimeApplication::STATUS_HR_APPROVED) {
            $this->notifyFinance($overtimeApplication);
        } else {
            $this->notifyEmployee($overtimeApplication, 'hr_rejected');
        }

        return redirect()
            ->route('overtime.report', ['month' => $overtimeApplication->salary_month])
            ->with('success', 'Overtime application updated by HR successfully.');
    }

    public function financeReports()
    {
        $this->ensureAccountsOfficer();

        return view('overtime.finance-reports');
    }

    public function financeReport(Request $request)
    {
        $this->ensureAccountsOfficer();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status');

        $applications = OvertimeApplication::with(['employee.designation', 'employee.department', 'hrApprover'])
            ->where('salary_month', $month)
            ->whereIn('status', [
                OvertimeApplication::STATUS_HR_APPROVED,
                OvertimeApplication::STATUS_APPROVED,
                OvertimeApplication::STATUS_FINANCE_REJECTED,
            ])
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByRaw("CASE WHEN STATUS = 'HR approved' THEN 1 WHEN STATUS = 'approved' THEN 2 ELSE 3 END")
            ->orderByDesc('hr_approved_at')
            ->get();

        return view('overtime.finance-report', [
            'applications' => $applications,
            'month' => $month,
            'status' => $status,
            'statuses' => $this->financeReportStatuses(),
        ]);
    }

    public function financeDecision(Request $request, $application)
    {
        $this->ensureAccountsOfficer();

        $overtimeApplication = OvertimeApplication::where('id', $application)->firstOrFail();

        if ($overtimeApplication->status !== OvertimeApplication::STATUS_HR_APPROVED) {
            return redirect()
                ->route('overtime.finance-report', ['month' => $overtimeApplication->salary_month])
                ->with('error', 'Only HR approved applications can be updated by Finance.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $status = $validated['decision'] === 'approve'
            ? OvertimeApplication::STATUS_APPROVED
            : OvertimeApplication::STATUS_FINANCE_REJECTED;

        $overtimeApplication->forceFill([
            'status' => $status,
            'finance_approved_by' => auth()->user()->emp_code,
            'finance_approved_at' => now(),
            'finance_remarks' => $validated['remarks'] ?? null,
        ])->save();

        $this->notifyEmployee(
            $overtimeApplication,
            $status === OvertimeApplication::STATUS_APPROVED ? 'finance_approved' : 'finance_rejected'
        );

        return redirect()
            ->route('overtime.finance-report', ['month' => $overtimeApplication->salary_month])
            ->with('success', 'Overtime application updated by Finance successfully.');
    }

    private function buildSummary(User $employee, string $month): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $today = Carbon::today()->endOfDay();
        if ($monthStart->gt($today)) {
            return [
                'salary_month' => $month,
                'period_start' => $monthStart,
                'period_end' => $today,
                'gross_salary' => Salary::grossSalaryFor($employee->emp_code),
                'eligible_count' => 0,
                'total_eligible_minutes' => 0,
                'total_eligible_amount' => 0,
                'eligible_rows' => collect(),
                'message' => 'No overtime is currently eligible for a future month.',
            ];
        }

        $windowEnd = $monthEnd->lt($today) ? $monthEnd : $today;
        $grossSalary = Salary::grossSalaryFor($employee->emp_code);

        $attendanceData = app(AttendanceController::class)->buildAttendanceData(
            $employee->emp_code,
            $monthStart->toDateString(),
            $windowEnd->toDateString()
        );

        $attendanceRows = collect($attendanceData['attendance'] ?? []);
        $holidayDates = $this->getHolidayDates();
        $rostersByDate = $this->getRosterRowsByDate($employee->emp_code, $monthStart, $windowEnd);
        $claimedDates = OvertimeApplication::where('emp_code', $employee->emp_code)
            ->whereIn('status', OvertimeApplication::activeStatuses())
            ->whereBetween('overtime_date', [$monthStart->toDateString(), $windowEnd->toDateString()])
            ->pluck('overtime_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->all();

        $eligibleRows = $attendanceRows
            ->map(function (array $row) use ($employee, $holidayDates, $rostersByDate, $today, $claimedDates, $grossSalary) {
                $date = Carbon::parse($row['at_date'])->startOfDay();
                $dateString = $date->toDateString();

                if (in_array($dateString, $claimedDates, true)) {
                    return null;
                }

                if ($today->diffInDays($date) > 7) {
                    return null;
                }

                if (empty($row['time_logs'])) {
                    return null;
                }

                if (($row['is_sunday'] ?? false) || ($row['is_weekly_rest'] ?? false)) {
                    if (! in_array($dateString, $holidayDates, true)) {
                        return null;
                    }
                }

                $roster = $rostersByDate->get($dateString);
                [$shiftStart, $shiftEnd] = $this->resolveDutyWindow($employee, $date, $roster);
                if (! $shiftStart || ! $shiftEnd) {
                    return null;
                }

                $isHoliday = in_array($dateString, $holidayDates, true);
                $isSecurity = strtoupper(trim((string) ($employee->department->dept_desc ?? ''))) === 'SECURITY DEPARTMENT';
                $overtimeMinutes = $this->calculateEligibleOvertimeMinutes(
                    $row['time_logs'],
                    $shiftStart,
                    $shiftEnd,
                    $isHoliday,
                    $isSecurity
                );

                if ($overtimeMinutes < 60) {
                    return null;
                }

                $shiftDurationMinutes = max(1, $shiftStart->diffInMinutes($shiftEnd));
                $hourlyRate = (float) ($grossSalary ?? 0) / 30 / max(1, ($shiftDurationMinutes / 60));
                $amount = $this->calculateAmount($hourlyRate, $overtimeMinutes);
                $timeInOut = $this->extractFirstLastSwipe($row['time_logs']);

                return [
                    'date' => $dateString,
                    'day' => $row['day'] ?? $date->format('l'),
                    'overtime_minutes' => $overtimeMinutes,
                    'hourly_rate' => $hourlyRate,
                    'amount' => $amount,
                    'shift_start' => $shiftStart->toDateTimeString(),
                    'shift_end' => $shiftEnd->toDateTimeString(),
                    'time_in' => $timeInOut['time_in'],
                    'time_out' => $timeInOut['time_out'],
                    'is_holiday' => $isHoliday,
                    'is_security' => $isSecurity,
                ];
            })
            ->filter()
            ->values();

        $totalEligibleMinutes = (int) $eligibleRows->sum('overtime_minutes');
        $totalEligibleAmount = (float) $eligibleRows->sum('amount');

        return [
            'salary_month' => $month,
            'period_start' => $monthStart,
            'period_end' => $windowEnd,
            'gross_salary' => $grossSalary,
            'eligible_count' => $eligibleRows->count(),
            'total_eligible_minutes' => $totalEligibleMinutes,
            'total_eligible_amount' => $totalEligibleAmount,
            'eligible_rows' => $eligibleRows->values(),
            'message' => $this->eligibilityMessage($employee, $eligibleRows->count(), $grossSalary),
        ];
    }

    private function buildEligibilityReportRows(string $month): Collection
    {
        return User::with(['designation', 'department'])
            ->whereNull('quit_stat')
            ->whereRaw("UPPER(TRIM(NVL(over_time_allowed, 'N'))) = 'Y'")
            ->orderBy('name')
            ->get()
            ->map(function (User $employee) use ($month) {
                $summary = $this->buildSummary($employee, $month);

                if (! $summary['gross_salary'] || $summary['eligible_rows']->isEmpty()) {
                    return null;
                }

                return [
                    'employee' => $employee,
                    'gross_salary' => $summary['gross_salary'],
                    'eligible_rows' => $summary['eligible_rows'],
                    'total_minutes' => $summary['total_eligible_minutes'],
                    'total_amount' => $summary['total_eligible_amount'],
                ];
            })
            ->filter()
            ->values();
    }

    public function calculateAmount(float $hourlyRate, int $minutes): float
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes <= 25) {
            $remainingMinutes = 0;
        } elseif ($remainingMinutes <= 45) {
            $remainingMinutes = 30;
        } else {
            $hours++;
            $remainingMinutes = 0;
        }

        $payableMinutes = ($hours * 60) + $remainingMinutes;

        return round(($hourlyRate * $payableMinutes) / 60);
    }

    private function getHolidayDates(): array
    {
        return Holidays::query()
            ->whereNotNull('h_date')
            ->pluck('h_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values()
            ->all();
    }

    private function getRosterRowsByDate(string $empCode, Carbon $startDate, Carbon $endDate): Collection
    {
        $rows = Roster::where('r_emp_code', $empCode)
            ->whereRaw(
                "TRUNC(r_effective_from) <= TO_DATE(?, 'YYYY-MM-DD') AND NVL(TRUNC(r_effective_to), TRUNC(r_effective_from)) >= TO_DATE(?, 'YYYY-MM-DD')",
                [$endDate->toDateString(), $startDate->toDateString()]
            )
            ->orderBy('r_effective_from')
            ->get();

        $byDate = collect();
        foreach ($rows as $row) {
            $from = Carbon::parse($row->r_effective_from)->startOfDay();
            $to = $row->r_effective_to
                ? Carbon::parse($row->r_effective_to)->startOfDay()
                : $from->copy();

            $date = $from->gt($startDate) ? $from->copy() : $startDate->copy();
            $last = $to->lt($endDate) ? $to->copy() : $endDate->copy();

            while ($date->lte($last)) {
                $byDate->put($date->toDateString(), $row);
                $date->addDay();
            }
        }

        return $byDate;
    }

    private function isUsableRosterRow($roster): bool
    {
        if ($roster->day_type === null) {
            return false;
        }

        if ((int) $roster->day_type === 0) {
            return true;
        }

        return ! empty($roster->r_shift_start) && ! empty($roster->r_shift_end);
    }

    private function rosterShiftDateTime(Carbon $date, $shiftTime): Carbon
    {
        $shift = Carbon::parse($shiftTime);

        return $date->copy()->setTime(
            (int) $shift->format('H'),
            (int) $shift->format('i'),
            (int) $shift->format('s')
        );
    }

    private function resolveDutyWindow(User $employee, Carbon $date, $roster = null): array
    {
        $workDate = $date->copy()->startOfDay();

        if ($roster && $this->isUsableRosterRow($roster) && (int) $roster->day_type !== 0) {
            $startTimeCarbon = $this->rosterShiftDateTime($workDate, $roster->r_shift_start);
            $endTimeCarbon = $this->rosterShiftDateTime($workDate, $roster->r_shift_end);

            if ($endTimeCarbon->lte($startTimeCarbon)) {
                $endTimeCarbon->addDay();
            }

            return [$startTimeCarbon, $endTimeCarbon];
        }

        if (! $employee->st_time || ! $employee->end_time) {
            return [null, null];
        }

        $startTimeCarbon = $workDate->copy()->setTimeFromTimeString($employee->st_time);
        $endTimeCarbon = $workDate->copy()->setTimeFromTimeString($employee->end_time);

        if ((int) $employee->twh !== 12) {
            if ((int) $employee->catg_code === 2 && $workDate->isFriday()) {
                $startTimeCarbon->setTime(8, 0);
                $endTimeCarbon->setTime(13, 0);
            } elseif ((int) $employee->catg_code === 1 && (int) $employee->loca_code === 2 && $workDate->isFriday()) {
                $startTimeCarbon->setTime(8, 0);
                $endTimeCarbon->setTime(14, 30);
            }
        }

        if ($endTimeCarbon->lte($startTimeCarbon)) {
            $endTimeCarbon->addDay();
        }

        return [$startTimeCarbon, $endTimeCarbon];
    }

    private function calculateEligibleOvertimeMinutes(array $timeLogs, Carbon $shiftStart, Carbon $shiftEnd, bool $isHoliday, bool $isSecurity): int
    {
        $minutes = 0;

        foreach ($timeLogs as $log) {
            if (empty($log['timein']) || empty($log['timeout'])) {
                continue;
            }

            $in = Carbon::parse($log['timein']);
            $out = Carbon::parse($log['timeout']);

            if ($out->lte($in)) {
                $out = $out->copy()->addDay();
            }

            if ($isHoliday) {
                if ($isSecurity) {
                    $overlapStart = $in->greaterThan($shiftStart) ? $in : $shiftStart;
                    $overlapEnd = $out->lessThan($shiftEnd) ? $out : $shiftEnd;

                    if ($overlapEnd->gt($overlapStart)) {
                        $minutes += $overlapStart->diffInMinutes($overlapEnd);
                    }
                } else {
                    $minutes += (int) ($log['worked_minutes'] ?? $in->diffInMinutes($out));
                }
            } else {
                $overtimeStart = $in->greaterThan($shiftEnd) ? $in : $shiftEnd;
                if ($out->gt($overtimeStart)) {
                    $minutes += $overtimeStart->diffInMinutes($out);
                }
            }
        }

        return (int) $minutes;
    }

    private function extractFirstLastSwipe(array $timeLogs): array
    {
        $firstIn = null;
        $lastOut = null;

        foreach ($timeLogs as $log) {
            if (! empty($log['timein']) && ($firstIn === null || Carbon::parse($log['timein'])->lt(Carbon::parse($firstIn)))) {
                $firstIn = $log['timein'];
            }

            if (! empty($log['timeout']) && ($lastOut === null || Carbon::parse($log['timeout'])->gt(Carbon::parse($lastOut)))) {
                $lastOut = $log['timeout'];
            }
        }

        return [
            'time_in' => $firstIn,
            'time_out' => $lastOut,
        ];
    }

    public function canEmployeeApplyOvertime(User $employee): bool
    {
        if (strtoupper(trim((string) ($employee->over_time_allowed ?? 'N'))) !== 'Y') {
            return false;
        }

        return true;
    }

    private function hasActiveApplication(string $empCode, string $date): bool
    {
        return OvertimeApplication::where('emp_code', $empCode)
            ->where('overtime_date', $date)
            ->whereIn('status', OvertimeApplication::activeStatuses())
            ->exists();
    }

    private function ensureOwnEmployee($empCode): void
    {
        if ((string) Auth::user()->emp_code !== (string) $empCode) {
            abort(403);
        }
    }

    private function ensureHodCanApprove(OvertimeApplication $application): void
    {
        if ((string) hisBoss($application->emp_code) !== (string) auth()->user()->emp_code) {
            abort(403);
        }
    }

    private function ensureBoss(): void
    {
        if (! auth()->user()->isBoss()) {
            abort(403);
        }
    }

    private function ensureHr(): void
    {
        if (! auth()->user()->isHR()) {
            abort(403);
        }
    }

    private function ensureAccountsOfficer(): void
    {
        if (! auth()->user()->isAccountsOfficer()) {
            abort(403);
        }
    }

    private function notifyEmployee(OvertimeApplication $application, string $stage): void
    {
        $employee = User::where('emp_code', $application->emp_code)->first();
        $employee?->notify(new OvertimeApplicationNotification($application, $stage));
    }

    private function eligibilityMessage(User $employee, int $eligibleCount, ?float $grossSalary): string
    {
        if (! $grossSalary) {
            return 'Gross salary record was not found, so overtime amount cannot be calculated.';
        }

        if (strtoupper(trim((string) ($employee->over_time_allowed ?? 'N'))) !== 'Y') {
            return 'Overtime is not allowed for this employee.';
        }

        if ($eligibleCount <= 0) {
            return 'No overtime (unclaimed/claimed) is currently eligible for this month.';
        }

        return 'Eligible overtime days for the selected month are listed below.';
    }

    private function hrReportStatuses(): array
    {
        return [
            OvertimeApplication::STATUS_PENDING => 'Pending HOD',
            OvertimeApplication::STATUS_HOD_APPROVED => 'HOD Approved',
            OvertimeApplication::STATUS_HOD_REJECTED => 'HOD Rejected',
            OvertimeApplication::STATUS_HR_APPROVED => 'HR Approved',
            OvertimeApplication::STATUS_HR_REJECTED => 'HR Rejected',
            OvertimeApplication::STATUS_APPROVED => 'Fully Approved',
            OvertimeApplication::STATUS_FINANCE_REJECTED => 'Finance Rejected',
            OvertimeApplication::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    private function financeReportStatuses(): array
    {
        return [
            OvertimeApplication::STATUS_HR_APPROVED => 'Pending Finance',
            OvertimeApplication::STATUS_APPROVED => 'Fully Approved',
            OvertimeApplication::STATUS_FINANCE_REJECTED => 'Finance Rejected',
        ];
    }

    private function notifyFinance(OvertimeApplication $application): void
    {
        User::where('desg_code', '520')
            ->whereNull('quit_stat')
            ->get()
            ->each(fn ($user) => $user->notify(new OvertimeApplicationNotification($application, 'hr_approved')));
    }

    private function notifyHr(OvertimeApplication $application): void
    {
        User::whereIn('desg_code', ['716', '971', '991', '44', '996', '786', '95'])
            ->whereNull('quit_stat')
            ->get()
            ->each(fn ($user) => $user->notify(new OvertimeApplicationNotification($application, 'hod_approved')));
    }
    public function downloadApprovedReport(Request $request)
    {
        $this->ensureHr();

        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $applications = OvertimeApplication::with(['employee.designation', 'employee.department'])
            ->where('salary_month', $month)
            ->where('status', OvertimeApplication::STATUS_APPROVED)
            ->orderByDesc('applied_at')
            ->get();

        $pdf = Pdf::loadView('pdf.overtime-approved-report', [
            'applications' => $applications,
            'month' => $month,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("approved_overtime_report_{$month}.pdf");
    }
}
