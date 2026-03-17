@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-2 text-gray-800">{{ $reportTitle ?? 'Availed Leave Report' }}</h1>
            <p class="mb-4">Date Range: {{ dateFormat($startDate) }} to {{ dateFormat($endDate) }}</p>
            <div class="card shadow mt-3 mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $department == null ? $employee->department->dept_desc : $department->dept_desc }} </h6>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('leave.report.download', [
                            'start_date' => dateFormat($startDate), 
                            'end_date' => dateFormat($endDate), 
                            'dept_desc' => $department == null ? '' : capitalizeWords($department->dept_desc),
                            'desg_short' => $department == null ? capitalizeWords($desigName->desg_short) : '',
                            'report' => $report, 
                            'employee_dept' => $employee?->department?->dept_desc,
                            ]) }}" method="post">
                            @csrf   
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Code</th>
                                        <th rowspan="2">Name</th>
                                        <th colspan="2">Casual Leave</th>
                                        <th colspan="2">Medical Leave</th>
                                        <th colspan="2">Annual Leave</th>
                                        <th rowspan="2">W/O Pay Leaves</th>
                                        <th rowspan="2">Outdoor Duty (OD)</th>
                                        <th rowspan="2">Late Minutes</th>
                                        <th rowspan="2">Early Minutes</th>
                                    </tr>
                                    <tr>
                                        <th>Availed</th>
                                        <th>Balance</th>
                                        <th>Availed</th>
                                        <th>Balance</th>
                                        <th>Availed</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($report as $row)
                                        <tr>
                                            <td>{{ $row['emp_code'] }}</td>
                                            <td>{{ $row['emp_name'] }}
                                                <br>
                                                <span style="font-size:12px;color:#666;">
                                                    ({{ $row['designation'] }})
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $row['leaves']['casual'] }}</td>
                                            <td class="text-center">{{ $row['balances']['casual'] ?? 0 }}</td>
                                            <td class="text-center">{{ $row['leaves']['medical'] }}</td>
                                            <td class="text-center">{{ $row['balances']['medical'] ?? 0 }}</td>
                                            <td class="text-center">{{ $row['leaves']['annual'] }}</td>
                                            <td class="text-center">{{ $row['balances']['annual'] ?? 0 }}</td>
                                            <td class="text-center">{{ $row['leaves']['without_pay'] }}</td>
                                            <td class="text-center">{{ $row['leaves']['outdoor_duty'] }}</td>
                                            <td class="text-center">{{ $row['late_mins'] }}</td>
                                            <td class="text-center">{{ $row['early_mins'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted">
                                                No employees record found for the selected criteria.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- Download button to download as pdf --}}
                        <div class="mt-3">
                            <a href="{{ route('leave-report') }}" class="btn btn-secondary btn-icon-split">
                                <span class="icon text-gray-300">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                                <span class="text">Back</span>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download Report as PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
