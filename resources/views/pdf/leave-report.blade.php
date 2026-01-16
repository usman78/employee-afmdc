<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Leave Report Employee Portal AFMDC</title>
        <style>
            body {
                font-family: "DejaVu Sans", sans-serif;
                font-size: 13px;
                color: #333;
                line-height: 1.4;
            }
            .header {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                /* border-bottom: 2px solid #777; */
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .logo {
                width: 120px;
                height: 120px;
                border: 1px solid #ccc;
                text-align: center;
                font-size: 12px;
                padding-top: 45px;
                color: #999;
            }
            .title {
                text-align: center;
                flex: 1;
                font-size: 20px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .photo {
                width: 120px;
                height: 150px;
                border: 1px solid #ccc;
                text-align: center;
                font-size: 12px;
                padding-top: 65px;
                color: #999;
            }
            .section-title {
                font-weight: bold;
                margin-top: 25px;
                margin-bottom: 8px;
                font-size: 16px;
                text-transform: uppercase;
                border-bottom: 1px solid #aaa;
                padding-bottom: 5px;
                text-align: center;
            }
            .logo-cell {
                width: 33%;
                text-align: center;
                vertical-align: middle;
            }
            .photo-cell {
                width: 33%;
                text-align: right;
                vertical-align: middle;
            }
            .logo-img {
                width: 120px;
                height: auto;
            }
            .photo-img {
                width: 120px;
                height: 150px;
                object-fit: cover;
                border: 1px solid #ccc;
                padding: 3px;
            }
            .title-cell {
                text-align: center;
                font-weight: bold;
                font-size: 20px;
                text-transform: uppercase;
                padding-top: 10px;
            }
            .subtitle-cell {
                text-align: center;
                font-size: 16px;
                font-weight: normal;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10px; /* IMPORTANT for PDF */
            }
            th, td {
                border: 1px solid #ccc;
                padding: 5px 6px;
                text-align: center;
                vertical-align: middle;
            }
            .header td, .header th {
                border: none;
            }
            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            td.name {
                text-align: left;
            }
            /* Prevent overflow */
            td, th {
                word-wrap: break-word;
                white-space: normal;
            }
        </style>
    </head>
    <body>
        <!-- ===== Header Section ===== -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <!-- Left Spacer -->
                    <td class="left-space"></td>
                    <!-- Center Logo -->
                    <td class="logo-cell">
                        <img src="{{ public_path('img/AFMDC-Logo.png') }}" class="logo-img">
                    </td>
                </tr>
                <!-- Title Row -->
                <tr>
                    <td colspan="3" class="title-cell">
                        Departmental Leave Report
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="subtitle-cell">
                        Date Range: {{ dateFormat($start) }} to {{ dateFormat($end) }}
                    </td>
                </tr>
            </table>
        </div>
        <!-- ===== Applicant Details ===== -->
        <div class="section-title">Filter By: {{ $dept_desc == null ? $desg_short : $dept_desc }}</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 6%">Code</th>
                    <th style="width: 18%">Name</th>
                    <th style="width: 8%">Casual</th>
                    <th style="width: 8%">Medical</th>
                    <th style="width: 8%">Annual</th>
                    <th style="width: 10%">W/O Pay</th>
                    <th style="width: 10%">Late (Min)</th>
                    <th style="width: 10%">Early (Min)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report as $row)
                    <tr>
                        <td>{{ $row['emp_code'] }}</td>
                        <td class="name">{{ $row['emp_name'] }}
                            <br>
                            <span style="font-size:12px;color:#666;">
                                ({{ $row['designation'] }})
                            </span>
                        </td>
                        <td>{{ $row['leaves']['casual'] }}</td>
                        <td>{{ $row['leaves']['medical'] }}</td>
                        <td>{{ $row['leaves']['annual'] }}</td>
                        <td>{{ $row['leaves']['without_pay'] }}</td>
                        <td>{{ number_format($row['late_mins'], 1) }}</td>
                        <td>{{ number_format($row['early_mins'], 1) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:#777;">
                            No leave records found for the selected criteria
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </body>
</html>
