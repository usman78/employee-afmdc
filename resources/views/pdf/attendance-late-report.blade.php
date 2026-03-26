<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Late Attendance Report</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10.5px;
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
        .subtitle {
            font-size: 10.5px;
        }
        .meta {
            margin: 6px 0 6px;
            font-size: 10.5px;
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
            vertical-align: middle;
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
            <td class="title">Late Attendance Report</td>
        </tr>
        <tr>
            <td class="subtitle">Report Date: {{ dateFormat($report_date) }}</td>
        </tr>
    </table>

    <div class="meta">
        Monthly stats range: {{ dateFormat($stats_start) }} to {{ dateFormat($stats_end) }}
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%">Sr#</th>
                <th rowspan="2" style="width: 8%;">Code</th>
                <th rowspan="2" style="width: 20%;">Name</th>
                <th rowspan="2" style="width: 14%;">Designation</th>
                <th rowspan="2" style="width: 16%;">Department</th>
                <th rowspan="2" style="width: 10%;">Time In</th>
                <th colspan="2" style="width: 18%; text-align: center; border: none;">Monthly</th>
            </tr>
            <tr>
                <th style="width: 9%;">Total Late Days</th>
                <th style="width: 9%;">Total Late Minutes</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['emp_code'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['time_in'] }}</td>
                    <td>{{ $row['total_late_days'] }}</td>
                    <td>{{ $row['total_late_minutes'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No late records found for this date.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
