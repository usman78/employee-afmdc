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
  </style>
</head>
<body>
  <h2>Approved Overtime Report</h2>
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
          <td>{{ dateFormat($application->overtime_date) }}</tdlass=>
          <td class="text-right">{{ number_format($application->sanctioned_minutes ?? $application->overtime_minutes) }}</td>
          <td class="text-right">{{ number_format($application->sanctioned_amount ?? $application->calculated_amount) }}</td>
          <td>{{ $application->remarks ?? '-' }}</td>
          <td>{{ $application->hr_remarks ?? '-' }}</tdclass=>
          <td>{{ $application->finance_remarks ?? '-' }}</tdlass=>
        </tr>
      @empty
        <tr>
          <td colspan="12" style="text-align: center;">No approved overtime applications found yet in this month.</td>
        </tr>
      @endforelse

      {{-- <tr class="total-row">
        <td colspan="9" class="text-right">Total Sanctioned Amount</td>
        <td class="text-right">{{ number_format($totalSanctioned) }}</td>
        <td colspan="2"></td>
      </tr> --}}
    </tbody>
  </table>
</body>
</html>
