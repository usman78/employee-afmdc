<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8.5px;
            color: #333;
            line-height: 1.2;
            margin: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            border: none;
            text-align: center;
        }
        .logo-img {
            width: 40px;
            height: auto;
        }
        .title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9.5px;
        }
        .meta {
            margin: 6px 0 6px;
            font-size: 9.5px;
        }
        .summary-table,
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table {
            margin-bottom: 8px;
            table-layout: fixed;
        }
        th, td {
            border: 0;
            padding: 2px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            white-space: normal;
        }
        th {
            background: #f2f2f2;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }
        .summary-table th,
        .summary-table td {
            width: 25%;
        }
        .summary-table td {
            border-bottom: 1px solid #e0e0e0;
        }
        .report-table td {
            border-bottom: 1px solid #e6e6e6;
        }
        .notice-box {
            margin-top: 14px;
            border: 1px solid #333;
            padding: 8px 10px 10px;
            font-size: 8.5px;
            line-height: 1.25;
        }
        .notice-strong {
            font-weight: bold;
            margin-top: 6px;
        }
        .signature-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
        }
        .signature-table td {
            border: 0;
            text-align: center;
            padding: 0;
        }
        .sig-name {
            font-weight: bold;
        }
        .sig-title {
            font-size: 9px;
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
            <td class="title">Attendance Report <small>({{ dateFormat($period_start) }} to {{ dateFormat($period_end) }})</small></td>
        </tr>
    </table>

    <div class="meta" style="text-align: center;">
        <strong>Employee:</strong> {{ $emp_name }} ({{ $emp_code }}) &nbsp;
        <strong>Department:</strong> {{ $emp_department ?? '--' }} &nbsp;
        <strong>Designation:</strong> {{ $emp_designation ?? '--' }}
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

    <table class="summary-table">
        <thead>
            <tr>
                <th>Casual Leaves</th>
                <th>Medical Leaves</th>
                <th>Annual Leaves</th>
                <th>Outdoor Duty</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $leave_counts['casual'] ?? 0 }}</td>
                <td>{{ $leave_counts['medical'] ?? 0 }}</td>
                <td>{{ $leave_counts['annual'] ?? 0 }}</td>
                <td>{{ $leave_counts['outdoor_duty'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 10%; text-align: left;">Sr#</th>
                <th style="width: 20%; text-align: left;">Date</th>
                <th style="width: 20%; text-align: left;">Time-In/Out</th>
                <th style="width: 15%; text-align: left;">Late Mins</th>
                <th style="width: 15%; text-align: left;">Early Mins</th>
                <th style="width: 20%; text-align: left;">Status</th>
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
                    <td style="text-align: left;">{{ $loop->iteration }}</td>
                    <td style="text-align: left;">{{ \Carbon\Carbon::parse($record['at_date'])->format('D, j M') }}</td>
                    <td style="text-align: left;">{{ $timeText }}</td>
                    <td style="text-align: left;">{{ $lateText }}</td>
                    <td style="text-align: left;">{{ $earlyText }}</td>
                    <td style="text-align: left;">{{ $statusText }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No attendance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($include_signatures ?? true)
    <div class="notice-box">
        <div>
            It is observed from your attendance record that, you are habitually coming on your duty
            late or going early from your duty, which is violation of duty timings of the institute and
            is evident from your attendance report (In / Out) for current month.
        </div>
        <div>
            Therefore, you are advised to follow the Institute timings, otherwise necessary action will
            be taken as per institute policy.
        </div>
        <div class="notice-strong">Submit your explanation within three days after receiving this letter.</div>

        <table class="signature-table">
            <tr>
                <td style="width: 33.33%;">
                    <div class="sig-name">Saba Khan Sherwani</div>
                    <div class="sig-title">Manager HR</div>
                </td>
                <td style="width: 33.33%;">
                    <div class="sig-name">Dr. Ghulam Abbas Sheikh</div>
                    <div class="sig-title">Principal AFM&amp;DC</div>
                </td>
                <td style="width: 33.33%;">
                    <div class="sig-name">Prof. Dr. Muhammad Saeed</div>
                    <div class="sig-title">COO AFT</div>
                </td>
            </tr>
        </table>
    </div>
    @endif
</body>
</html>
