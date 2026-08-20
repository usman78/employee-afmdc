@php
use App\Models\OvertimeApplication;
@endphp
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Approved Overtime Report</title>
  <style>
    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 10px;
      color: #111;
    }
    h2 {
      margin: 0 0 4px;
      text-align: center;
    }
    .subtitle {
      margin: 0 0 14px;
      text-align: center;
      color: #555;
      font-size: 11px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #777;
      padding: 5px;
      vertical-align: top;
    }
    th {
      background: #2196f3;
      color: #fff;
      font-weight: bold;
      text-align: left;
    }
    .text-right {
      text-align: right;
    }
    .total-row td {
      font-weight: bold;
      background: #eef6ff;
    }
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
  </style>
</head>
<body>
  @php
    $reportTitle = $status === OvertimeApplication::STATUS_HR_APPROVED ? 'HR Approved Overtime Report' : 'Approved Overtime Report';
    $totalAmount = $totalAmount ?? $applications->sum(fn ($application) => $application->sanctioned_amount ?? $application->calculated_amount);
  @endphp
    <table class="header-table">
        <tr>
            <td>
                <img src="{{ public_path('img/AFMDC-Logo.png') }}" class="logo-img">
            </td>
        </tr>
        <tr>
            <td class="title">Aziz Fatimah Medical & Dental College</td>
        </tr>
    </table>
  <h2>{{ $reportTitle }}</h2>
  <div class="subtitle">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Code</th>
        <th>Designation</th>
        <th>Department</th>
        <th>Overtime Date</th>
        <th>OT minutes</th>
        <th>Salary</th>
        <th>OT Amount</th>
        <th>Remarks</th>
        <th>HR Remarks</th>
        <th>Finance Remarks</th>
      </tr>
    </thead>
    <tbody>
      @forelse($applications as $application)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
          <td>{{ $application->emp_code }}</td>
          <td>{{ $application->employee->designation->desg_short ?? '-' }}</td>
          <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
          <td>{{ dateFormat($application->overtime_date) }}</td>
          <td class="text-right">{{ formatMinutes(payableMinutes($application->sanctioned_minutes ?? $application->overtime_minutes)) }}</td>
          <td class="text-right">{{ number_format($application->gross_salary ?? 0, 0) }}</td>
          <td class="text-right">{{ number_format($application->sanctioned_amount ?? $application->calculated_amount, 2) }}</td>
          <td>{{ $application->remarks ?? '-' }}</td>
          <td>{{ $application->hr_remarks ?? '-' }}</td>
          <td>{{ $application->finance_remarks ?? '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="12" style="text-align: center;">No approved overtime applications found yet in this month.</td>
        </tr>
      @endforelse

      @if($applications->count())
        <tr class="total-row">
          <td colspan="8" class="text-right">Grand Total</td>
          <td class="text-right">{{ number_format($totalAmount, 2) }}</td>
          <td colspan="3"></td>
        </tr>
      @endif
    </tbody>
  </table>
</body>
</html>
