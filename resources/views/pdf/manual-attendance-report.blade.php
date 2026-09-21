<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manual Attendance Report</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
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
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle,
        .meta {
            font-size: 10px;
        }
        .meta {
            margin: 6px 0 8px;
            text-align: center;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 0;
            padding: 3px 4px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            white-space: normal;
        }
        th {
            background: #f2f2f2;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }
        .report-table td {
            border-bottom: 1px solid #e6e6e6;
        }
        .employee-name {
            font-weight: bold;
        }
        .employee-detail {
            color: #555;
            font-size: 8px;
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
            <td class="title">Manual Attendance Report</td>
        </tr>
        <tr>
            <td class="subtitle">
                {{ dateFormat($from_date) }} to {{ dateFormat($to_date) }}
            </td>
        </tr>
    </table>

    <div class="meta">
        Total records: {{ $rows->count() }} | Report generated on: {{ $download_date_time->format('j M Y H:i') }}
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 4%;">Sr#</th>
                <th style="width: 5%;">Code</th>
                <th style="width: 19%;">Employee</th>
                <th style="width: 8%;">Date</th>
                <th style="width: 5%;">In</th>
                <th style="width: 5%;">Out</th>
                <th style="width: 13%;">Remarks</th>
                <th style="width: 10%;">Username</th>
                <th style="width: 11%;">User IP</th>
                <th style="width: 12%;">Changed At</th>
                <th style="width: 8%;">Type</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['emp_code'] }}</td>
                    <td>
                        <div class="employee-name">{{ $row['name'] }}</div>
                        <div class="employee-detail">{{ $row['designation'] ?: '--' }}</div>
                        <div class="employee-detail">{{ $row['department'] ?: '--' }}</div>
                    </td>
                    <td>{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('j M Y') : '--' }}</td>
                    <td>{{ $row['time_in'] ? \Carbon\Carbon::parse($row['time_in'])->format('H:i') : '--:--' }}</td>
                    <td>{{ $row['time_out'] ? \Carbon\Carbon::parse($row['time_out'])->format('H:i') : '--:--' }}</td>
                    <td>{{ $row['remarks'] ?: '--' }}</td>
                    <td>{{ $row['username'] ?: '--' }}</td>
                    <td>{{ $row['user_ip'] ?: '--' }}</td>
                    <td>{{ $row['time_of_change'] ? \Carbon\Carbon::parse($row['time_of_change'])->format('j M Y H:i') : '--' }}</td>
                    <td>{{ $row['att_type'] ?: '--' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">No manual attendance records found for this date range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
