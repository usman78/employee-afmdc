<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .header-table td {
            border: none;
            text-align: center;
        }
        .logo-img {
            width: 90px;
            height: auto;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 12px;
        }
        .meta {
            margin: 10px 0 12px;
            font-size: 12px;
        }
        .summary-table,
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table {
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            white-space: normal;
        }
        th {
            background: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <img src="{{ public_path('img/AFMDC-Logo.png') }}" class="logo-img">
            </td>
        </tr>
        <tr>
            <td class="title">Attendance Report</td>
        </tr>
        <tr>
            <td class="subtitle">Period: {{ dateFormat($period_start) }} to {{ dateFormat($period_end) }}</td>
        </tr>
    </table>

    <div class="meta">
        <strong>Employee:</strong> {{ $emp_name }} ({{ $emp_code }})
    </div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Late Days</th>
                <th>Late Minutes</th>
                <th>Early Minutes</th>
                <th>Total Effect Minutes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $late_days }}</td>
                <td>{{ $late_minutes }}</td>
                <td>{{ $early_minutes }}</td>
                <td>{{ $total_minutes }}</td>
            </tr>
        </tbody>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 16%;">Date</th>
                <th style="width: 30%;">Time-In/Out</th>
                <th style="width: 14%;">Late Mins</th>
                <th style="width: 14%;">Early Mins</th>
                <th style="width: 26%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendance as $record)
                @php
                    $timeText = '';
                    if ($record['is_sunday'] || $record['is_holiday']) {
                        $timeText = $record['is_holiday'] ? 'Holiday' : 'Sunday';
                    } elseif (!empty($record['time_logs'])) {
                        $pairs = [];
                        foreach ($record['time_logs'] as $log) {
                            if (!empty($log['timein']) && !empty($log['timeout'])) {
                                $pairs[] = \Carbon\Carbon::parse($log['timein'])->format('H:i') . ' / ' . \Carbon\Carbon::parse($log['timeout'])->format('H:i');
                            } elseif (!empty($log['timein'])) {
                                $pairs[] = \Carbon\Carbon::parse($log['timein'])->format('H:i') . ' / --:--';
                            }
                        }
                        $timeText = implode(' | ', $pairs);
                    } else {
                        $timeText = 'Not timed in';
                    }

                    $lateText = '-';
                    if (!$record['is_sunday'] && !$record['is_holiday'] && (($record['late_minutes'] ?? 0) >= 10)) {
                        $lateText = intval($record['late_minutes']) . ' mins';
                    }

                    $earlyText = '-';
                    if (!$record['is_sunday'] && !$record['is_holiday'] && (($record['early_minutes'] ?? 0) > 0)) {
                        $earlyText = intval(round($record['early_minutes'])) . ' mins';
                    }

                    if ($record['is_sunday'] || $record['is_holiday']) {
                        $statusText = $record['is_holiday'] ? 'Holiday' : 'Sunday';
                    } elseif (!empty($record['leave_type'])) {
                        $statusText = $record['leave_type'];
                    } elseif (empty($record['time_logs'])) {
                        $statusText = 'Absent';
                    } else {
                        $statusText = 'Present';
                    }
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($record['at_date'])->format('D, j M') }}</td>
                    <td>{{ $timeText }}</td>
                    <td>{{ $lateText }}</td>
                    <td>{{ $earlyText }}</td>
                    <td>{{ $statusText }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No attendance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
