<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Overtime Eligibility Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h2 { margin: 0 0 4px; }
        .muted { color: #555; }
        .summary { margin: 10px 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 5px; vertical-align: top; }
        th { background: #f0f0f0; }
        .right { text-align: right; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h2>Overtime Eligibility Report</h2>
    <div class="muted">Month: {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>
    <div class="summary">
        Employees: {{ $employeeCount }} |
        Eligible Dates: {{ $eligibleDateCount }} |
        Total Minutes: {{ $totalMinutes }} |
        Estimated Amount: {{ number_format($totalAmount, 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Emp Code</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Eligible Date</th>
                <th>Day</th>
                <th>Attendance</th>
                <th>Shift</th>
                <th class="right">OT Minutes</th>
                <th class="right">Amount</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportRows as $reportRow)
                @foreach ($reportRow['eligible_rows'] as $eligibleRow)
                    <tr>
                        <td class="nowrap">{{ $reportRow['employee']->emp_code }}</td>
                        <td>
                            {{ $reportRow['employee']->name }}<br>
                            <span class="muted">{{ $reportRow['employee']->designation->desg_desc ?? '' }}</span>
                        </td>
                        <td>{{ $reportRow['employee']->department->dept_desc ?? '' }}</td>
                        <td class="nowrap">{{ \Carbon\Carbon::parse($eligibleRow['date'])->format('d-M-Y') }}</td>
                        <td>{{ $eligibleRow['day'] }}</td>
                        <td class="nowrap">
                            {{ $eligibleRow['time_in'] ? \Carbon\Carbon::parse($eligibleRow['time_in'])->format('h:i A') : '-' }}
                            -
                            {{ $eligibleRow['time_out'] ? \Carbon\Carbon::parse($eligibleRow['time_out'])->format('h:i A') : '-' }}
                        </td>
                        <td class="nowrap">
                            {{ \Carbon\Carbon::parse($eligibleRow['shift_start'])->format('h:i A') }}
                            -
                            {{ \Carbon\Carbon::parse($eligibleRow['shift_end'])->format('h:i A') }}
                        </td>
                        <td class="right">{{ $eligibleRow['overtime_minutes'] }}</td>
                        <td class="right">{{ number_format($eligibleRow['amount'], 2) }}</td>
                        <td>
                            {{ $eligibleRow['is_holiday'] ? 'Holiday' : 'Working Day' }}
                            {{ $eligibleRow['is_security'] ? '/ Security' : '' }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;">No unclaimed eligible overtime found for this month.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($reportRows->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="7" class="right">Total</th>
                    <th class="right">{{ $totalMinutes }}</th>
                    <th class="right">{{ number_format($totalAmount, 2) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
