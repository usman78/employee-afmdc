<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Overtime Audit Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 5.5px; color: #111; }
        h2 { margin: 0 0 3px; text-align: center; font-size: 12px; }
        .subtitle { margin: 0 0 8px; text-align: center; color: #555; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #777; padding: 2px; vertical-align: top; }
        th { background: #047857; color: #fff; font-weight: bold; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <h2>Overtime Audit Report</h2>
    <div class="subtitle">{{ $scope === 'approved' ? 'Approved Records' : 'All Records' }} | {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Employee</th><th>Department</th><th>Date</th><th>Shift</th><th>Time In/Out</th><th>Claimed</th><th>Sanctioned Min.</th><th>Salary</th><th>Rate</th><th>Calculated</th><th>Sanctioned</th><th>Status</th><th>HOD Approval</th><th>HR Approval</th><th>Finance Approval</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $application)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ capitalizeWords($application->employee->name ?? '') }}<br>{{ $application->emp_code }}</td>
                    <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
                    <td>{{ optional($application->overtime_date)->format('d M Y') }}</td>
                    <td>{{ $application->shift_start ? timeFormatFromString($application->shift_start) : '-' }} - {{ $application->shift_end ? timeFormatFromString($application->shift_end) : '-' }}</td>
                    <td>{{ $application->time_in ? timeFormatFromString($application->time_in) : '-' }} - {{ $application->time_out ? timeFormatFromString($application->time_out) : '-' }}</td>
                    <td>{{ formatMinutes($application->overtime_minutes ?? 0) }}</td>
                    <td>{{ $application->sanctioned_minutes ? formatMinutes($application->sanctioned_minutes) : '-' }}</td>
                    <td class="number">{{ number_format($application->gross_salary ?? 0) }}</td>
                    <td class="number">{{ number_format($application->hourly_rate ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($application->calculated_amount ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($application->sanctioned_amount ?? 0, 2) }}</td>
                    <td>{{ $application->status }}</td>
                    <td>{{ capitalizeWords($application->hodApprover->name ?? '') ?: '-' }}<br>{{ $application->hod_remarks ?: '' }}</td>
                    <td>{{ capitalizeWords($application->hrApprover->name ?? '') ?: '-' }}<br>{{ $application->hr_remarks ?: '' }}</td>
                    <td>{{ capitalizeWords($application->financeApprover->name ?? '') ?: '-' }}<br>{{ $application->finance_remarks ?: '' }}</td>
                </tr>
            @empty
                <tr><td colspan="16" style="text-align: center;">No overtime applications found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
