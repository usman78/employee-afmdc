<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Approved Advance Salary Report</title>
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
  <h2>Approved Advance Salary Report</h2>
  <div class="subtitle">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Code</th>
        <th>Designation</th>
        <th>Department</th>
        <th>Days Worked</th>
        <th>Requested</th>
        <th>Monthly Salary</th>
        <th>Salary Payable</th>
        <th>Sanctioned Amount</th>
        <th>HR Approved By</th>
        <th>Accounts Approved By</th>
      </tr>
    </thead>
    <tbody>
      @forelse($applications as $application)
        @php
          $salaryPayable = (int) floor(((float) $application->gross_salary / 30) * (int) $application->eligible_days);
        @endphp
        <tr>
          <td>{{ $loop->iteration }}</td> 
          <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
          <td>{{ $application->emp_code }}</td>
          <td>{{ $application->employee->designation->desg_short ?? '-' }}</td>
          <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
          <td class="text-right">{{ $application->eligible_days }}</td>
          <td class="text-right">{{ number_format($application->requested_amount) }}</td>
          <td class="text-right">{{ number_format($application->gross_salary) }}</td>
          <td class="text-right">{{ number_format($salaryPayable) }}</td>
          <td class="text-right">{{ number_format($application->sanctioned_amount) }}</td>
          <td>{{ $application->hrApprover ? capitalizeWords($application->hrApprover->name) : '-' }}</td>
          <td>{{ $application->accountsApprover ? capitalizeWords($application->accountsApprover->name) : '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="12" style="text-align: center;">No approved advance salary applications found.</td>
        </tr>
      @endforelse

      <tr class="total-row">
        <td colspan="9" class="text-right">Total Sanctioned Amount</td>
        <td class="text-right">{{ number_format($totalSanctioned) }}</td>
        <td colspan="2"></td>
      </tr>
    </tbody>
  </table>
</body>
</html>
