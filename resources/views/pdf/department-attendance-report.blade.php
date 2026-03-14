<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Department Attendance Report</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            /* font-size: 12px; */
            font-size: 9px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .header img {
            /* width: 90px; */
            width: 40px;
            height: auto;
        }
        .title {
            /* font-size: 18px; */
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
        }
        .subtitle {
            /* font-size: 12px; */
            font-size: 9px;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            /* font-size: 11px; */
            font-size: 9px;
            margin-top: 10px;
        }
        th, td {
            /* border: 1px solid #ccc; */
            /* padding: 6px; */
            padding: 1px;
            text-align: center;
        }
        th.left-side{
            text-align: left;
        }
        td.left-side{
            text-align: left;
        }
        th {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('img/AFMDC-Logo.png') }}" alt="AFMDC Logo">
        <div class="title">Department Attendance Report</div>
        <div class="subtitle">
            <strong>Department:</strong> {{ $department_name }} |
            <strong>Date:</strong> {{ dateFormat($report_date) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee Code</th>
                <th class="left-side">Name</th>
                <th>Designation</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Time Status</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['emp_code'] }}</td>
                    <td class="left-side">{{ $row['name'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['time_in'] }}</td>
                    <td>{{ $row['time_out'] }}</td>
                    <td>{{ $row['time_status'] }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No employee record found for this department/date.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
