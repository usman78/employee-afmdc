<?php

namespace App\Http\Controllers;

use App\Models\AdvanceSalaryApplication;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holidays;
use App\Models\Leave;
use App\Models\Roster;
use App\Models\Salary;
use App\Models\User;
use App\Notifications\AdvanceSalaryDecisionNotification;
use App\Notifications\AdvanceSalaryHodApprovedNotification;
use App\Notifications\AdvanceSalaryHodDecisionNotification;
use App\Notifications\AdvanceSalaryHrApprovedNotification;
use App\Notifications\AdvanceSalarySubmittedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvanceSalaryController extends Controller
{
    private const MIN_ELIGIBLE_DAYS = 15;
    private const ABSOLUTE_MAX_AMOUNT = 30000;

    public function create($empCode)
    {
        $this->ensureOwnEmployee($empCode);

        $employee = Employee::where('emp_code', $empCode)->firstOrFail();
        $summary = $this->buildSummary($empCode);
        $applications = AdvanceSalaryApplication::where('emp_code', $empCode)
            ->where('salary_month', $summary['salary_month'])
            ->orderByDesc('applied_at')
            ->get();

        return view('advance-salary.create', [
            'employee' => $employee,
            'summary' => $summary,
            'applications' => $applications,
        ]);
    }

    public function store(Request $request, $empCode)
    {
        $this->ensureOwnEmployee($empCode);

        if (session()->has('advance_salary_submit_in_progress')) {
            return back()
                ->withInput()
                ->with('error', 'Your application is already being processed. Please wait a moment and try again.');
        }

        session()->put('advance_salary_submit_in_progress', true);

        try {
            $summary = $this->buildSummary($empCode);

            if (! $summary['is_eligible']) {
                return back()
                    ->withInput()
                    ->with('error', $summary['message']);
            }

            $validated = $request->validate([
            'requested_amount' => [
                'required',
                'integer',
                'min:1',
                'max:' . $summary['remaining_limit'],
            ],
            'reason' => 'required|string|max:1000',
        ]);

            $application = AdvanceSalaryApplication::create([
                'id' => getIncrementedId('ADVANCE_SALARY_APPLICATIONS', 'ID'),
                'emp_code' => $empCode,
                'salary_month' => $summary['salary_month'],
                'gross_salary' => $summary['gross_salary'],
                'max_amount' => $summary['max_amount'],
                'requested_amount' => (int) $validated['requested_amount'],
                'eligible_days' => $summary['eligible_days'],
                'reason' => $validated['reason'],
                'status' => AdvanceSalaryApplication::STATUS_PENDING,
                'applied_at' => now(),
                'post_flag' => 'Y',
            ]);

            $hodCode = hisBoss($empCode);
            if ($hodCode) {
                $hod = User::where('emp_code', $hodCode)->first();
                $hod?->notify(new AdvanceSalarySubmittedNotification($application));
            }

            return redirect()
                ->route('advance-salary.create', $empCode)
                ->with('success', 'Advance salary application submitted successfully and sent to HOD for approval.');
        } finally {
            session()->forget('advance_salary_submit_in_progress');
        }
    }

    public function hodShow($application)
    {
        $advanceSalaryApplication = AdvanceSalaryApplication::with(['employee.designation', 'employee.department'])
            ->where('id', $application)
            ->firstOrFail();

        $this->ensureHodCanApprove($advanceSalaryApplication);

        return view('advance-salary.hod-show', [
            'application' => $advanceSalaryApplication,
            'salaryPayable' => $this->salaryPayableAtApplication($advanceSalaryApplication),
        ]);
    }

    public function hodIndex(Request $request)
    {
        $this->ensureBoss();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $subordinateCodes = auth()->user()->teamMembersOfHod()->pluck('emp_code_l')->filter()->values();

        $applications = AdvanceSalaryApplication::with(['employee.designation', 'employee.department'])
            ->whereIn('emp_code', $subordinateCodes)
            ->where('salary_month', $month)
            ->orderByRaw("CASE WHEN STATUS = 'pending' THEN 1 WHEN STATUS = 'HOD_approved' THEN 2 ELSE 3 END")
            ->orderByDesc('applied_at')
            ->get();

        return view('advance-salary.hod-index', [
            'applications' => $applications,
            'month' => $month,
        ]);
    }

    public function hodDecision(Request $request, $application)
    {
        $advanceSalaryApplication = AdvanceSalaryApplication::where('id', $application)->firstOrFail();
        $this->ensureHodCanApprove($advanceSalaryApplication);

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($advanceSalaryApplication->status !== AdvanceSalaryApplication::STATUS_PENDING) {
            return redirect()
                ->route('advance-salary.hod-show', $advanceSalaryApplication->id)
                ->with('error', 'Only pending applications can be updated by HOD.');
        }

        $status = $validated['decision'] === 'approve'
            ? AdvanceSalaryApplication::STATUS_HOD_APPROVED
            : AdvanceSalaryApplication::STATUS_HOD_REJECTED;

        $advanceSalaryApplication->forceFill([
            'status' => $status,
            'hod_approved_by' => auth()->user()->emp_code,
            'hod_approved_at' => now(),
            'hod_remarks' => $validated['remarks'] ?? null,
        ])->save();

        if ($status === AdvanceSalaryApplication::STATUS_HOD_APPROVED) {
            $this->notifyHr($advanceSalaryApplication);
        }

        $employee = User::where('emp_code', $advanceSalaryApplication->emp_code)->first();
        $employee?->notify(new AdvanceSalaryHodDecisionNotification($advanceSalaryApplication));

        return redirect()
            ->route('advance-salary.hod-show', $advanceSalaryApplication->id)
            ->with('success', 'Advance salary application updated successfully.');
    }

    public function report(Request $request)
    {
        $this->ensureHr();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status');
        $applications = AdvanceSalaryApplication::with(['employee.designation', 'employee.department'])
            ->where('salary_month', $month)
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByRaw("CASE WHEN STATUS = 'HOD_approved' THEN 1 WHEN STATUS = 'pending' THEN 2 ELSE 3 END")
            ->orderByDesc('applied_at')
            ->get();

        $onRollApplications = $applications->filter(function ($application) {
            return $application->emp_type === '1';
        })->values();

        $dailyWagerApplications = $applications->filter(function ($application) {
            return $application->emp_type === '2';
        })->values();

        // dd($onRollApplications, $dailyWagerApplications);

        return view('advance-salary.report', [
            'applications' => $applications,
            'onRollApplications' => $onRollApplications,
            'dailyWagerApplications' => $dailyWagerApplications,
            'month' => $month,
            'status' => $status,
            'statuses' => $this->hrReportStatuses(),
        ]);
    }

    public function hrDecision(Request $request, $application)
    {
        $this->ensureHr();

        $advanceSalaryApplication = AdvanceSalaryApplication::where('id', $application)->firstOrFail();

        if ($advanceSalaryApplication->status !== AdvanceSalaryApplication::STATUS_HOD_APPROVED) {
            return redirect()
                ->route('advance-salary.report', ['month' => $advanceSalaryApplication->salary_month])
                ->with('error', 'Only HOD approved applications can be updated by HR.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'sanctioned_amount' => [
                'required_if:decision,approve',
                'nullable',
                'integer',
                'min:1',
                'max:' . (int) $advanceSalaryApplication->max_amount,
            ],
            'remarks' => 'nullable|string|max:1000',
        ]);

        $status = $validated['decision'] === 'approve'
            ? AdvanceSalaryApplication::STATUS_HR_APPROVED
            : AdvanceSalaryApplication::STATUS_HR_REJECTED;

        $advanceSalaryApplication->forceFill([
            'status' => $status,
            'sanctioned_amount' => $status === AdvanceSalaryApplication::STATUS_HR_APPROVED
                ? (int) $validated['sanctioned_amount']
                : null,
            'hr_approved_by' => auth()->user()->emp_code,
            'hr_approved_at' => now(),
            'hr_remarks' => $validated['remarks'] ?? null,
        ])->save();

        if ($status === AdvanceSalaryApplication::STATUS_HR_APPROVED) {
            $this->notifyAccounts($advanceSalaryApplication);
        }

        $employee = User::where('emp_code', $advanceSalaryApplication->emp_code)->first();
        $employee?->notify(new AdvanceSalaryDecisionNotification($advanceSalaryApplication));

        return redirect()
            ->route('advance-salary.report', ['month' => $advanceSalaryApplication->salary_month])
            ->with('success', 'Advance salary application updated by HR successfully.');
    }

    public function financeReports()
    {
        $this->ensureAccountsOfficer();

        return view('finance-reports');
    }

    public function accountsReport(Request $request)
    {
        $this->ensureAccountsOfficer();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status');
        $applications = AdvanceSalaryApplication::with(['employee.designation', 'employee.department', 'hrApprover'])
            ->where('salary_month', $month)
            ->whereIn('status', [
                AdvanceSalaryApplication::STATUS_HR_APPROVED,
                AdvanceSalaryApplication::STATUS_APPROVED,
                AdvanceSalaryApplication::STATUS_ACCOUNTS_REJECTED,
            ])
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByRaw("CASE WHEN STATUS = 'HR approved' THEN 1 WHEN STATUS = 'approved' THEN 2 ELSE 3 END")
            ->orderByDesc('hr_approved_at')
            ->get();

        $onRollApplications = $applications->filter(function ($application) {
            return $application->emp_type === '1';
        })->values();

        $dailyWagerApplications = $applications->filter(function ($application) {
            return $application->employee->emp_type === '2';
        })->values();

        return view('advance-salary.accounts-report', [
            'applications' => $applications,
            'onRollApplications' => $onRollApplications,
            'dailyWagerApplications' => $dailyWagerApplications,
            'month' => $month,
            'status' => $status,
            'statuses' => $this->accountsReportStatuses(),
        ]);
    }

    public function accountsApprovedDownload(Request $request)
    {
        $this->ensureAccountsOfficer();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $applications = AdvanceSalaryApplication::with(['employee.designation', 'employee.department', 'hrApprover', 'accountsApprover'])
            ->where('salary_month', $month)
            ->where('status', AdvanceSalaryApplication::STATUS_APPROVED)
            ->orderBy('emp_code')
            ->get();

        $totalSanctioned = $applications->sum(fn ($application) => (float) $application->sanctioned_amount);
        $pdf = Pdf::loadView('pdf.advance-salary-approved', [
            'applications' => $applications,
            'month' => $month,
            'totalSanctioned' => $totalSanctioned,
        ])->setPaper('a4', 'landscape');

        $fileName = 'advance_salary_approved_' . $month . '.pdf';

        return $pdf->stream($fileName);
    }

    public function nameFilteredDownload(Request $request)
    {
        $this->ensureAccountsOfficer();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $employeeName = $request->input('employee_name');

        if (! $employeeName) {
            return redirect()
                ->route('advance-salary.accounts-report', ['month' => $month])
                ->with('error', 'Please provide an employee name.');
        }

        $applications = AdvanceSalaryApplication::with(['employee.designation', 'employee.department', 'hrApprover', 'accountsApprover'])
            ->where('salary_month', $month)
            ->where('status', AdvanceSalaryApplication::STATUS_APPROVED)
            ->whereHas('employee', function ($query) use ($employeeName) {
                $nameParts = explode(' ', trim($employeeName));
                $query->where(function ($q) use ($nameParts) {
                    foreach ($nameParts as $part) {
                        if (! empty($part)) {
                            $q->orWhereRaw('LOWER(name) LIKE LOWER(?)', ['%' . $part . '%']);
                        }
                    }
                });
            })
            ->orderBy('emp_code')
            ->get();

        if ($applications->isEmpty()) {
            return redirect()
                ->route('advance-salary.report', ['month' => $month])
                ->with('error', 'No approved applications found for the employee: ' . $employeeName);
        }

        $totalSanctioned = $applications->sum(fn ($application) => (float) $application->sanctioned_amount);
        $pdf = Pdf::loadView('pdf.advance-salary-approved', [
            'applications' => $applications,
            'month' => $month,
            'totalSanctioned' => $totalSanctioned,
        ])->setPaper('a4', 'landscape');

        $fileName = 'advance_salary_' . str_replace(' ', '_', $employeeName) . '_' . $month . '.pdf';

        return $pdf->stream($fileName);
    }

    public function dateFilteredDownload(Request $request)
    {
        $this->ensureAccountsOfficer();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if (! $fromDate || ! $toDate) {
            return redirect()
                ->route('advance-salary.accounts-report', ['month' => $month])
                ->with('error', 'Please provide both from and to dates.');
        }

        $applications = AdvanceSalaryApplication::with(['employee.designation', 'employee.department', 'hrApprover', 'accountsApprover'])
            ->where('salary_month', $month)
            ->where('status', AdvanceSalaryApplication::STATUS_APPROVED)
            ->whereRaw(
                "TRUNC(accounts_approved_at) >= TO_DATE(?, 'YYYY-MM-DD') AND TRUNC(accounts_approved_at) <= TO_DATE(?, 'YYYY-MM-DD')",
                [$fromDate, $toDate]
            )
            ->orderBy('emp_code')
            ->get();

        if ($applications->isEmpty()) {
            return redirect()
                ->route('advance-salary.report', ['month' => $month])
                ->with('error', 'No approved applications found between ' . $fromDate . ' and ' . $toDate);
        }

        $totalSanctioned = $applications->sum(fn ($application) => (float) $application->sanctioned_amount);
        $pdf = Pdf::loadView('pdf.advance-salary-approved', [
            'applications' => $applications,
            'month' => $month,
            'totalSanctioned' => $totalSanctioned,
        ])->setPaper('a4', 'landscape');

        $fileName = 'advance_salary_approved_' . $fromDate . '_to_' . $toDate . '.pdf';

        return $pdf->stream($fileName);
    }

    public function accountsDecision(Request $request, $application)
    {
        $this->ensureAccountsOfficer();

        $advanceSalaryApplication = AdvanceSalaryApplication::where('id', $application)->firstOrFail();

        if ($advanceSalaryApplication->status !== AdvanceSalaryApplication::STATUS_HR_APPROVED) {
            return redirect()
                ->route('advance-salary.accounts-report', ['month' => $advanceSalaryApplication->salary_month])
                ->with('error', 'Only HR approved applications can be updated by Accounts.');
        }

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $status = $validated['decision'] === 'approve'
            ? AdvanceSalaryApplication::STATUS_APPROVED
            : AdvanceSalaryApplication::STATUS_ACCOUNTS_REJECTED;

        $advanceSalaryApplication->forceFill([
            'status' => $status,
            'accounts_approved_by' => auth()->user()->emp_code,
            'accounts_approved_at' => now(),
            'accounts_remarks' => $validated['remarks'] ?? null,
        ])->save();

        $employee = User::where('emp_code', $advanceSalaryApplication->emp_code)->first();
        $employee?->notify(new AdvanceSalaryDecisionNotification($advanceSalaryApplication));

        return redirect()
            ->route('advance-salary.accounts-report', ['month' => $advanceSalaryApplication->salary_month])
            ->with('success', 'Advance salary application updated by Accounts successfully.');
    }

    public function revoke($empCode, $application)
    {
        $this->ensureOwnEmployee($empCode);

        $advanceSalaryApplication = AdvanceSalaryApplication::where('emp_code', $empCode)
            ->where('id', $application)
            ->firstOrFail();

        if ($advanceSalaryApplication->status !== AdvanceSalaryApplication::STATUS_PENDING) {
            return redirect()
                ->route('advance-salary.create', $empCode)
                ->with('error', 'Only pending advance salary applications can be revoked.');
        }

        $advanceSalaryApplication->forceFill([
            'status' => AdvanceSalaryApplication::STATUS_CANCELLED,
        ])->save();

        return redirect()
            ->route('advance-salary.create', $empCode)
            ->with('success', 'Advance salary application revoked successfully.');
    }

    private function ensureOwnEmployee($empCode): void
    {
        if ((string) Auth::user()->emp_code !== (string) $empCode) {
            abort(403);
        }
    }

    private function ensureHodCanApprove(AdvanceSalaryApplication $application): void
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

    private function buildSummary($empCode): array
    {
        $today = Carbon::today();
        $start = $today->copy()->startOfMonth();
        $salaryMonth = $today->format('Y-m');
        $daysInMonth = Carbon::createFromFormat('Y-m', $salaryMonth)->daysInMonth;
        $eligibleDays = $this->eligibleDays($empCode, $start, $today);
        $grossSalary = Salary::grossSalaryFor($empCode);

        $maxAmount = $grossSalary === null
            ? 0
            : min(self::ABSOLUTE_MAX_AMOUNT, (int) floor(($grossSalary / $daysInMonth) * self::MIN_ELIGIBLE_DAYS));

        $alreadyRequested = (float) AdvanceSalaryApplication::where('emp_code', $empCode)
            ->where('salary_month', $salaryMonth)
            ->whereIn('status', AdvanceSalaryApplication::activeStatuses())
            ->selectRaw('NVL(SUM(NVL(SANCTIONED_AMOUNT, REQUESTED_AMOUNT)), 0) AS total')
            ->value('total');

        $remainingLimit = max(0, $maxAmount - $alreadyRequested);
        $isEligible = $eligibleDays >= self::MIN_ELIGIBLE_DAYS
            && $grossSalary !== null
            && $remainingLimit > 0;

        return [
            'salary_month' => $salaryMonth,
            'period_start' => $start,
            'period_end' => $today,
            'eligible_days' => $eligibleDays,
            'required_days' => self::MIN_ELIGIBLE_DAYS,
            'gross_salary' => $grossSalary,
            'absolute_max_amount' => self::ABSOLUTE_MAX_AMOUNT,
            'max_amount' => $maxAmount,
            'already_requested' => $alreadyRequested,
            'remaining_limit' => $remainingLimit,
            'is_eligible' => $isEligible,
            'message' => $this->eligibilityMessage($eligibleDays, $grossSalary, $remainingLimit),
        ];
    }

    private function eligibilityMessage(int $eligibleDays, ?float $grossSalary, float $remainingLimit): string
    {
        if ($eligibleDays < self::MIN_ELIGIBLE_DAYS) {
            return 'Advance salary is enabled after 15 eligible days in the current month.';
        }

        if ($grossSalary === null) {
            return 'Gross salary record was not found, so the advance limit cannot be calculated.';
        }

        if ($remainingLimit <= 0) {
            return 'Your current month advance salary limit has already been used.';
        }

        return 'You are eligible to apply for advance salary for the current month.';
    }

    private function notifyHr(AdvanceSalaryApplication $application): void
    {
        User::whereIn('desg_code', ['716', '971', '991', '44', '996', '786', '95'])
            ->whereNull('quit_stat')
            ->get()
            ->each(fn ($user) => $user->notify(new AdvanceSalaryHodApprovedNotification($application)));
    }

    private function notifyAccounts(AdvanceSalaryApplication $application): void
    {
        User::where('desg_code', '520')
            ->whereNull('quit_stat')
            ->get()
            ->each(fn ($user) => $user->notify(new AdvanceSalaryHrApprovedNotification($application)));
    }

    private function hrReportStatuses(): array
    {
        return [
            AdvanceSalaryApplication::STATUS_PENDING => 'Pending HOD',
            AdvanceSalaryApplication::STATUS_HOD_APPROVED => 'HOD Approved',
            AdvanceSalaryApplication::STATUS_HOD_REJECTED => 'HOD Rejected',
            AdvanceSalaryApplication::STATUS_HR_APPROVED => 'HR Approved',
            AdvanceSalaryApplication::STATUS_HR_REJECTED => 'HR Rejected',
            AdvanceSalaryApplication::STATUS_APPROVED => 'Fully Approved',
            AdvanceSalaryApplication::STATUS_ACCOUNTS_REJECTED => 'Accounts Rejected',
            AdvanceSalaryApplication::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    private function accountsReportStatuses(): array
    {
        return [
            AdvanceSalaryApplication::STATUS_HR_APPROVED => 'Pending Accounts',
            AdvanceSalaryApplication::STATUS_APPROVED => 'Fully Approved',
            AdvanceSalaryApplication::STATUS_ACCOUNTS_REJECTED => 'Accounts Rejected',
        ];
    }

    public function salaryPayableAtApplication(AdvanceSalaryApplication $application): int
    {
        $daysInMonth = Carbon::createFromFormat('Y-m', (string) $application->salary_month)->daysInMonth;

        return (int) floor(((float) $application->gross_salary / $daysInMonth) * (int) $application->eligible_days);
    }

    private function eligibleDays($empCode, Carbon $start, Carbon $end): int
    {
        $dates = collect();

        $presentDates = Attendance::where('emp_code', $empCode)
            ->whereNull('att_stat')
            ->whereRaw(
                "at_date >= TO_DATE(?, 'YYYY-MM-DD') AND at_date < TO_DATE(?, 'YYYY-MM-DD') + 1",
                [$start->toDateString(), $end->toDateString()]
            )
            ->get(['at_date'])
            ->map(fn ($row) => Carbon::parse($row->at_date)->toDateString());

        $dates = $dates->merge($presentDates);

        $dates = $dates->merge($this->holidayDates($start, $end));
        $dates = $dates->merge($this->restDayDates($empCode, $start, $end));
        $dates = $dates->merge($this->approvedLeaveDates($empCode, $start, $end));

        return $dates->filter()->unique()->count();
    }

    private function holidayDates(Carbon $start, Carbon $end)
    {
        return Holidays::whereNotNull('h_date')
            ->whereRaw(
                "TRUNC(h_date) BETWEEN TO_DATE(?, 'YYYY-MM-DD') AND TO_DATE(?, 'YYYY-MM-DD')",
                [$start->toDateString(), $end->toDateString()]
            )
            ->get(['h_date'])
            ->map(fn ($row) => Carbon::parse($row->h_date)->toDateString());
    }

    private function restDayDates($empCode, Carbon $start, Carbon $end)
    {
        $rosterByDate = Roster::where('r_emp_code', $empCode)
            ->whereRaw(
                "TRUNC(r_effective_from) BETWEEN TO_DATE(?, 'YYYY-MM-DD') AND TO_DATE(?, 'YYYY-MM-DD')",
                [$start->toDateString(), $end->toDateString()]
            )
            ->get(['r_effective_from', 'day_type'])
            ->keyBy(function ($row) {
                return Carbon::parse($row->r_effective_from)->toDateString();
            });

        $employeeRestDay = User::where('emp_code', $empCode)->value('rest_day') ?: 'SUNDAY';

        $dates = collect();
        $date = $start->copy();

        while ($date->lte($end)) {
            $dateString = $date->toDateString();
            $roster = $rosterByDate->get($dateString);

            $dayType = $roster && is_numeric($roster->day_type)
                ? (int) $roster->day_type
                : null;

            if (in_array($dayType, [0, 1], true)) {
                if ($dayType === 0) {
                    $dates->push($dateString);
                }

                $date->addDay();
                continue;
            }

            if ($this->matchesEmployeeRestDay($employeeRestDay, $date)) {
                $dates->push($dateString);
            }

            $date->addDay();
        }

        return $dates;
    }

    private function matchesEmployeeRestDay($restDay, Carbon $date): bool
    {
        $restDay = strtoupper(trim((string) $restDay));

        if ($restDay === '') {
            return false;
        }

        $restDay = preg_replace('/[^A-Z]/', '', $restDay);

        return $restDay === strtoupper($date->format('l'))
            || $restDay === strtoupper($date->format('D'))
            || substr($restDay, 0, 3) === strtoupper($date->format('D'));
    }

    private function approvedLeaveDates($empCode, Carbon $start, Carbon $end)
    {
        $leaves = Leave::where('emp_code', $empCode)
            ->where('status', 7)
            ->whereRaw(
                "from_date < TO_DATE(?, 'YYYY-MM-DD') + 1 AND to_date >= TO_DATE(?, 'YYYY-MM-DD')",
                [$end->toDateString(), $start->toDateString()]
            )
            ->get(['from_date', 'to_date']);

        $dates = collect();

        foreach ($leaves as $leave) {
            $leaveStart = Carbon::parse($leave->from_date)->startOfDay()->max($start);
            $leaveEnd = Carbon::parse($leave->to_date)->startOfDay()->min($end);

            $date = $leaveStart->copy();
            while ($date->lte($leaveEnd)) {
                $dates->push($date->toDateString());
                $date->addDay();
            }
        }

        return $dates;
    }
}
