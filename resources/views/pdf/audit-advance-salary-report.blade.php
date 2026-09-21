<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Advance Salary Audit Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 6px; color: #111; }
        h2 { margin: 0 0 3px; text-align: center; font-size: 12px; }
        .subtitle { margin: 0 0 8px; text-align: center; color: #555; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #777; padding: 3px; vertical-align: top; }
        th { background: #1d4ed8; color: #fff; font-weight: bold; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <h2>Advance Salary Audit Report</h2>
    <div class="subtitle">{{ $scope === 'approved' ? 'Approved Records' : 'All Records' }} | {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Employee</th><th>Department</th><th>Days</th><th>Requested</th><th>Salary</th><th>Limit</th><th>Sanctioned</th><th>Status</th><th>Reason</th><th>HOD Approval</th><th>HR Approval</th><th>Accounts Approval</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $application)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ capitalizeWords($application->employee->name ?? '') }}<br>{{ $application->emp_code }}</td>
                    <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
                    <td class="number">{{ $application->eligible_days }}</td>
                    <td class="number">{{ number_format($application->requested_amount ?? 0) }}</td>
                    <td class="number">{{ number_format($application->gross_salary ?? 0) }}</td>
                    <td class="number">{{ number_format($application->max_amount ?? 0) }}</td>
                    <td class="number">{{ number_format($application->sanctioned_amount ?? 0) }}</td>
                    <td>{{ $application->status }}</td>
                    <td>{{ $application->reason ?: '-' }}</td>
                    <td>{{ capitalizeWords($application->hodApprover->name ?? '') ?: '-' }}<br>{{ $application->hod_remarks ?: '' }}</td>
                    <td>{{ capitalizeWords($application->hrApprover->name ?? '') ?: '-' }}<br>{{ $application->hr_remarks ?: '' }}</td>
                    <td>{{ capitalizeWords($application->accountsApprover->name ?? '') ?: '-' }}<br>{{ $application->accounts_remarks ?: '' }}</td>
                </tr>
            @empty
                <tr><td colspan="13" style="text-align: center;">No advance salary applications found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
