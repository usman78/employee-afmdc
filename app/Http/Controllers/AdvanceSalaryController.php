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
        ]);

        $hodCode = hisBoss($empCode);
        if ($hodCode) {
            $hod = User::where('emp_code', $hodCode)->first();
            $hod?->notify(new AdvanceSalarySubmittedNotification($application));
        }

        return redirect()
            ->route('advance-salary.create', $empCode)
            ->with('success', 'Advance salary application submitted successfully and sent to HOD for approval.');
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
        $subordinateCodes = auth()->user()->teamMembers->pluck('emp_code_l')->filter()->values();

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

        return view('advance-salary.report', [
            'applications' => $applications,
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

        return view('advance-salary.accounts-report', [
            'applications' => $applications,
            'month' => $month,
            'status' => $status,
            'statuses' => $this->accountsReportStatuses(),
        ]);
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
        $eligibleDays = $this->eligibleDays($empCode, $start, $today);
        $grossSalary = Salary::grossSalaryFor($empCode);

        $maxAmount = $grossSalary === null
            ? 0
            : min(self::ABSOLUTE_MAX_AMOUNT, (int) floor(($grossSalary / 30) * self::MIN_ELIGIBLE_DAYS));

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
        return (int) floor(((float) $application->gross_salary / 30) * (int) $application->eligible_days);
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
        $rosterDates = Roster::where('emp_code', $empCode)
            ->whereBetween('dated', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->filter(function ($row) {
                $day = strtoupper(trim((string) ($row->day ?? '')));
                $leaveName = strtoupper(trim((string) ($row->leav_name ?? '')));

                return $day === 'SUN' || $leaveName === 'WR';
            })
            ->map(fn ($row) => Carbon::parse($row->dated)->toDateString());

        if ($rosterDates->isNotEmpty()) {
            return $rosterDates;
        }

        $dates = collect();
        $date = $start->copy();
        while ($date->lte($end)) {
            if ($date->isSunday()) {
                $dates->push($date->toDateString());
            }
            $date->addDay();
        }

        return $dates;
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
