<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Absent Attendance Report</title>
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
        .note {
            margin: 4px 0 8px;
            font-size: 10px;
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
            <td class="title">Absent Attendance Report</td>
        </tr>
        <tr>
            <td class="subtitle">Report Date: {{ dateFormat($report_date) }}</td>
        </tr>
    </table>

    <div class="meta">
        Monthly stats range: {{ dateFormat($stats_start) }} to {{ dateFormat($stats_end) }}
    </div>

    @if(!empty($is_non_working_day))
        <div class="note">Selected date is a Sunday or holiday. Showing employees on leave only.</div>
    @endif

    <table class="report-table">
        <thead>
            <tr>
                <th rowspan="2">Sr#</th>
                <th rowspan="2" style="width: 8%;">Code</th>
                <th rowspan="2" style="width: 15%;">Name</th>
                <th rowspan="2" style="width: 14%;">Designation</th>
                <th rowspan="2" style="width: 16%;">Department</th>
                <th rowspan="2" style="width: 10%;">Status</th>
                <th colspan="4" style="width: 37%; text-align: center; border: none;">Monthly ({{ \Carbon\Carbon::parse($report_date)->format('F') }})</th>
            </tr>
            <tr>
                <th style="width: 7%;">Casual</th>
                <th style="width: 7%;">Medical</th>
                <th style="width: 7%;">Annual</th>
                <th style="width: 7%;">OD</th>
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
                    <td>{{ $row['status'] }}</td>
                    <td>{{ rtrim(rtrim(number_format($row['casual'], 1), '0'), '.') }}</td>
                    <td>{{ rtrim(rtrim(number_format($row['medical'], 1), '0'), '.') }}</td>
                    <td>{{ rtrim(rtrim(number_format($row['annual'], 1), '0'), '.') }}</td>
                    <td>{{ rtrim(rtrim(number_format($row['od'], 1), '0'), '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No absent/leave records found for this date.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
