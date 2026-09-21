<?php

namespace App\Http\Controllers;

use App\Models\AdvanceSalaryApplication;
use App\Models\OvertimeApplication;
use App\Services\ReportAccessService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditReportController extends Controller
{
    public function index(ReportAccessService $reportAccess)
    {
        $this->ensureAnyAuditAccess($reportAccess);

        return view('audit-reports.index');
    }

    public function advanceSalary(Request $request, ReportAccessService $reportAccess)
    {
        $this->ensureAuditAccess($reportAccess, 'audit-advance-salary');

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status');
        $applications = AdvanceSalaryApplication::with([
            'employee.designation',
            'employee.department',
            'hodApprover',
            'hrApprover',
            'accountsApprover',
        ])
            ->where('salary_month', $month)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw('CAST(emp_code AS NUMBER)')
            ->orderByDesc('applied_at')
            ->get();

        return view('audit-reports.advance-salary', [
            'applications' => $applications,
            'month' => $month,
            'status' => $status,
            'statuses' => $this->advanceSalaryStatuses(),
        ]);
    }

    public function overtime(Request $request, ReportAccessService $reportAccess)
    {
        $this->ensureAuditAccess($reportAccess, 'audit-overtime');

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $status = $request->input('status');
        $applications = OvertimeApplication::with([
            'employee.designation',
            'employee.department',
            'hodApprover',
            'hrApprover',
            'financeApprover',
        ])
            ->where('salary_month', $month)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('emp_code')
            ->orderBy('overtime_date')
            ->get();

        return view('audit-reports.overtime', [
            'applications' => $applications,
            'month' => $month,
            'status' => $status,
            'statuses' => $this->overtimeStatuses(),
        ]);
    }

    public function downloadAdvanceSalary(Request $request, string $scope, ReportAccessService $reportAccess)
    {
        $this->ensureAuditAccess($reportAccess, 'audit-advance-salary');
        abort_unless(in_array($scope, ['all', 'approved'], true), 404);

        $month = $this->validatedMonth($request);
        $applications = $this->advanceSalaryApplications($month, $scope);

        return Pdf::loadView('pdf.audit-advance-salary-report', compact('applications', 'month', 'scope'))
            ->setPaper('a4', 'landscape')
            ->stream("advance_salary_audit_{$scope}_{$month}.pdf");
    }

    public function downloadOvertime(Request $request, string $scope, ReportAccessService $reportAccess)
    {
        $this->ensureAuditAccess($reportAccess, 'audit-overtime');
        abort_unless(in_array($scope, ['all', 'approved'], true), 404);

        $month = $this->validatedMonth($request);
        $applications = $this->overtimeApplications($month, $scope);

        return Pdf::loadView('pdf.audit-overtime-report', compact('applications', 'month', 'scope'))
            ->setPaper('a4', 'landscape')
            ->stream("overtime_audit_{$scope}_{$month}.pdf");
    }

    private function ensureAuditAccess(ReportAccessService $reportAccess, string $reportKey): void
    {
        abort_unless(
            request()->user() && $reportAccess->allowed(request()->user(), $reportKey),
            403
        );
    }

    private function validatedMonth(Request $request): string
    {
        return $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ])['month'] ?? Carbon::now()->format('Y-m');
    }

    private function advanceSalaryApplications(string $month, string $scope)
    {
        return AdvanceSalaryApplication::with([
            'employee.designation',
            'employee.department',
            'hodApprover',
            'hrApprover',
            'accountsApprover',
        ])
            ->where('salary_month', $month)
            ->when($scope === 'approved', fn ($query) => $query->where('status', AdvanceSalaryApplication::STATUS_APPROVED))
            ->orderByRaw('CAST(emp_code AS NUMBER)')
            ->orderByDesc('applied_at')
            ->get();
    }

    private function overtimeApplications(string $month, string $scope)
    {
        return OvertimeApplication::with([
            'employee.designation',
            'employee.department',
            'hodApprover',
            'hrApprover',
            'financeApprover',
        ])
            ->where('salary_month', $month)
            ->when($scope === 'approved', fn ($query) => $query->where('status', OvertimeApplication::STATUS_APPROVED))
            ->orderBy('emp_code')
            ->orderBy('overtime_date')
            ->get();
    }

    private function ensureAnyAuditAccess(ReportAccessService $reportAccess): void
    {
        abort_unless(
            request()->user() && $reportAccess->allowedAny(request()->user(), $reportAccess->groupKeys('Audit')),
            403
        );
    }

    private function advanceSalaryStatuses(): array
    {
        return [
            AdvanceSalaryApplication::STATUS_PENDING => 'Pending',
            AdvanceSalaryApplication::STATUS_HOD_APPROVED => 'HOD Approved',
            AdvanceSalaryApplication::STATUS_HOD_REJECTED => 'HOD Rejected',
            AdvanceSalaryApplication::STATUS_HR_APPROVED => 'HR Approved',
            AdvanceSalaryApplication::STATUS_HR_REJECTED => 'HR Rejected',
            AdvanceSalaryApplication::STATUS_APPROVED => 'Accounts Approved',
            AdvanceSalaryApplication::STATUS_ACCOUNTS_REJECTED => 'Accounts Rejected',
            AdvanceSalaryApplication::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    private function overtimeStatuses(): array
    {
        return [
            OvertimeApplication::STATUS_PENDING => 'Pending',
            OvertimeApplication::STATUS_HOD_APPROVED => 'HOD Approved',
            OvertimeApplication::STATUS_HOD_REJECTED => 'HOD Rejected',
            OvertimeApplication::STATUS_HR_APPROVED => 'HR Approved',
            OvertimeApplication::STATUS_HR_REJECTED => 'HR Rejected',
            OvertimeApplication::STATUS_APPROVED => 'Finance Approved',
            OvertimeApplication::STATUS_FINANCE_REJECTED => 'Finance Rejected',
            OvertimeApplication::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
