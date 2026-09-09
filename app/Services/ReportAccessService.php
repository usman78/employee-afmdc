<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportAccessService
{
    private const TABLE = 'REPORT_ACCESS_CONTROLS';

    private array $overrides = [];
    private bool $itManagerResolved = false;
    private ?string $itManagerCode = null;

    private const REPORTS = [
        'attendance' => ['label' => 'Attendance Report', 'group' => 'HR'],
        'attendance-late' => ['label' => 'Late Attendance Report', 'group' => 'HR'],
        'attendance-absent' => ['label' => 'Absent Attendance Report', 'group' => 'HR'],
        'attendance-present' => ['label' => 'Present Attendance Report', 'group' => 'HR'],
        'manual-attendance' => ['label' => 'Manual Attendance Report', 'group' => 'HR'],
        'department-strength' => ['label' => 'Department Strength Report', 'group' => 'HR'],
        'leave' => ['label' => 'Leave Reports', 'group' => 'HR'],
        'advance-salary-hr' => ['label' => 'Advance Salary Report', 'group' => 'HR'],
        'exit-interview' => ['label' => 'Exit Interview Report', 'group' => 'HR'],
        'overtime-hr' => ['label' => 'Overtime Report', 'group' => 'HR'],
        'overtime-eligibility' => ['label' => 'Overtime Eligibility Report', 'group' => 'HR'],
        'attendance-discrepancy' => ['label' => 'Attendance Discrepancy Report', 'group' => 'HR'],
        'advance-salary-finance' => ['label' => 'Advance Salary Finance Report', 'group' => 'Finance'],
        'overtime-finance' => ['label' => 'Overtime Finance Report', 'group' => 'Finance'],
        'inventory-reports' => ['label' => 'Inventory Reports', 'group' => 'Inventory'],
        'store-report' => ['label' => 'Store Report', 'group' => 'Inventory'],
        'individual-leave-report' => ['label' => 'Individual Leave Report', 'group' => 'HR'],
    ];

    public function reports(): array
    {
        return self::REPORTS;
    }

    public function reportsByGroup(): array
    {
        return collect(self::REPORTS)->groupBy('group', preserveKeys: true)->all();
    }

    public function canManage(?User $user): bool
    {
        return $user !== null
            && ($user->isAdmin() || (string) $user->emp_code === $this->itManagerCode());
    }

    public function allowed(User $user, string $reportKey): bool
    {
        if (! array_key_exists($reportKey, self::REPORTS)) {
            return false;
        }

        if ($this->canManage($user)) {
            return true;
        }

        $overrides = $this->overridesFor($user);

        return array_key_exists($reportKey, $overrides)
            ? $overrides[$reportKey] === 'Y'
            : $this->legacyAllowed($user, $reportKey);
    }

    public function allowedAny(User $user, array $reportKeys): bool
    {
        foreach ($reportKeys as $reportKey) {
            if ($this->allowed($user, $reportKey)) {
                return true;
            }
        }

        return false;
    }

    public function groupKeys(string $group): array
    {
        return collect(self::REPORTS)
            ->filter(fn (array $report) => $report['group'] === $group)
            ->keys()
            ->all();
    }

    public function permissionStatesFor(User $user): array
    {
        $overrides = $this->overridesFor($user);

        return collect(self::REPORTS)->mapWithKeys(function (array $report, string $key) use ($user, $overrides) {
            return [$key => [
                'allowed' => $this->allowed($user, $key),
                'overridden' => array_key_exists($key, $overrides),
            ]];
        })->all();
    }

    public function updateFor(User $user, array $reportKeys, string $updatedBy): void
    {
        $allowedKeys = array_fill_keys($reportKeys, true);

        DB::transaction(function () use ($user, $allowedKeys, $updatedBy) {
            foreach (array_keys(self::REPORTS) as $reportKey) {
                DB::table(self::TABLE)->updateOrInsert(
                    [
                        'EMP_CODE' => $user->emp_code,
                        'REPORT_KEY' => $reportKey,
                    ],
                    [
                        'IS_ALLOWED' => isset($allowedKeys[$reportKey]) ? 'Y' : 'N',
                        'UPDATED_BY' => $updatedBy,
                        'UPDATED_AT' => now(),
                    ]
                );
            }
        });
    }

    private function legacyAllowed(User $user, string $reportKey): bool
    {
        return match ($reportKey) {
            'leave' => $user->isHR() || (string) $user->emp_code === '1225',
            'advance-salary-finance', 'overtime-finance' => $user->isAccountsOfficer(),
            'inventory-reports' => true,
            'store-report' => $user->isStoreOfficer(),
            default => $user->isHR(),
        };
    }

    private function overridesFor(User $user): array
    {
        $employeeCode = (string) $user->emp_code;

        if (! array_key_exists($employeeCode, $this->overrides)) {
            $this->overrides[$employeeCode] = DB::table(self::TABLE)
                ->where('EMP_CODE', $user->emp_code)
                ->get(['REPORT_KEY', 'IS_ALLOWED'])
                ->mapWithKeys(function (object $row) {
                    $columns = array_change_key_case((array) $row, CASE_LOWER);

                    return [(string) $columns['report_key'] => (string) $columns['is_allowed']];
                })
                ->all();
        }

        return $this->overrides[$employeeCode];
    }

    private function itManagerCode(): string
    {
        if (! $this->itManagerResolved) {
            $this->itManagerCode = getItManagerCode();
            $this->itManagerResolved = true;
        }

        return (string) $this->itManagerCode;
    }
}
