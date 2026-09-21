<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ReportAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportAccessController extends Controller
{
    public function index(Request $request, ReportAccessService $reportAccess)
    {
        $this->authorizeManager($reportAccess);

        $employees = User::query()
            ->whereNull('quit_stat')
            ->orderBy('name')
            ->get(['emp_code', 'name', 'desg_code', 'dept_code']);

        $employee = null;
        $permissionStates = [];

        if ($request->filled('emp_code')) {
            $employee = $employees->firstWhere('emp_code', $request->string('emp_code')->toString())
                ?? User::where('emp_code', $request->string('emp_code')->toString())->firstOrFail();

            $permissionStates = $reportAccess->permissionStatesFor($employee);
        }

        return view('report-access.index', [
            'employees' => $employees,
            'employee' => $employee,
            'reportsByGroup' => $reportAccess->reportsByGroup(),
            'permissionStates' => $permissionStates,
        ]);
    }

    public function update(Request $request, string $empCode, ReportAccessService $reportAccess)
    {
        Log::info('Updating report access for employee: ' . $empCode);
        $this->authorizeManager($reportAccess);
        Log::info('Authorization successful for user: ' . $request->user()->emp_code);
        $validated = $request->validate([
            'reports' => ['nullable', 'array'],
            'reports.*' => ['accepted'],
        ]);

        $reportKeys = array_keys($validated['reports'] ?? []);
        $invalidReportKeys = array_diff($reportKeys, array_keys($reportAccess->reports()));

        if ($invalidReportKeys !== []) {
            return back()
                ->withInput()
                ->withErrors(['reports' => 'One or more selected report permissions are invalid.']);
        }

        Log::info('Validation successful. Validated data: ' . json_encode($validated));
        $employee = User::where('emp_code', $empCode)->firstOrFail();
        $reportAccess->updateFor(
            $employee,
            $reportKeys,
            (string) $request->user()->emp_code
        );

        return redirect()
            ->route('report-access.index', ['emp_code' => $employee->emp_code])
            ->with('success', 'Report access updated for ' . $employee->name . '.');
    }

    private function authorizeManager(ReportAccessService $reportAccess): void
    {
        abort_unless($reportAccess->canManage(request()->user()), 403);
    }
}
