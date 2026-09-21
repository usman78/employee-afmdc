<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Department Strength Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead {
            background-color: #f5f7fa;
            font-weight: bold;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f7fa;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .dept-row {
            background-color: #f5f7fa;
            font-weight: bold;
        }
        .totals-row {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .shortage {
            color: #dc3545;
            font-weight: bold;
        }
        .excess {
            color: #198754;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Department Strength Report</h1>
        <p>As on {{ $report_date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 20%;">Department</th>
                <th style="width: 18%;">Designation</th>
                <th style="width: 12%;">Faculty Req.</th>
                <th style="width: 12%;">Faculty On Roll</th>
                <th style="width: 12%;">Excess / Short</th>
                <th>Faculty Names</th>
            </tr>
        </thead>
        <tbody>
            @php $sr = 1; @endphp
            @forelse($grouped as $deptCode => $deptData)
                @php
                    $firstRow = $deptData['rows']->first();
                    $deptName = $firstRow['dept_desc'] ?? '--';
                    $rowCount = $deptData['rows']->count();
                @endphp
                @foreach($deptData['rows'] as $index => $row)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ $rowCount }}" class="text-center">{{ $sr }}</td>
                            <td rowspan="{{ $rowCount }}">{{ capitalizeWords($deptName) }}</td>
                        @endif
                        <td>{{ $row['desg_short'] }}</td>
                        <td class="text-center">{{ $row['no_of_vacancy'] }}</td>
                        <td class="text-center">{{ $row['filled_vacancy'] }}</td>
                        <td class="text-center">
                            @if($row['shortage'] > 0)
                                <span class="shortage">({{ $row['shortage'] }})</span>
                            @elseif($row['shortage'] < 0)
                                <span class="excess">+{{ abs($row['shortage']) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if(($row['names'] ?? collect())->isNotEmpty())
                                {{ ($row['names'] ?? collect())->implode(', ') }}
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr class="dept-row">
                    <td colspan="3" class="text-right">Subtotal</td>
                    <td class="text-center">{{ $deptData['totals']['required'] }}</td>
                    <td class="text-center">{{ $deptData['totals']['filled'] }}</td>
                    <td class="text-center">
                        @if($deptData['totals']['shortage'] > 0)
                            <span class="shortage">({{ $deptData['totals']['shortage'] }})</span>
                        @elseif($deptData['totals']['shortage'] < 0)
                            <span class="excess">+{{ abs($deptData['totals']['shortage']) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td></td>
                </tr>
                @php $sr++; @endphp
            @empty
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
                </tr>
            @endforelse
            @if($grouped->isNotEmpty())
                <tr class="totals-row">
                    <td colspan="3" class="text-right">Grand Total</td>
                    <td class="text-center">{{ $overall_totals['required'] }}</td>
                    <td class="text-center">{{ $overall_totals['filled'] }}</td>
                    <td class="text-center">
                        @if($overall_totals['shortage'] > 0)
                            <span class="shortage">({{ $overall_totals['shortage'] }})</span>
                        @elseif($overall_totals['shortage'] < 0)
                            <span class="excess">+{{ abs($overall_totals['shortage']) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Shortage is calculated as approved seats minus on-roll seats.</p>
        <p>Generated on {{ now()->format('M d, Y H:i:s') }}</p>
    </div>
</body>
</html>
