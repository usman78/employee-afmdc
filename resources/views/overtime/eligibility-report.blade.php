@extends('layouts.app')
@push('styles')
    .thead {
        --bs-table-bg: #4e73df !important;
    }
@endpush
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Overtime Eligibility Report</h4>
            <small class="text-muted">Employees with eligible overtime dates that have not been applied for.</small>
        </div>
        <a href="{{ route('hr-reports') }}" class="btn btn-outline-secondary btn-sm">
            <span class="fas fa-arrow-left"></span>
            Back to HR Reports
        </a>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('overtime.eligibility-report') }}" class="form-inline">
                <label for="month" class="mr-2 font-weight-bold">Month</label>
                <input type="month" name="month" id="month" value="{{ $month }}" class="form-control mr-2">
                <button type="submit" class="btn btn-primary mr-2">
                    <span class="fas fa-eye"></span>
                    View Report
                </button>
                <a href="{{ route('overtime.eligibility-download', ['month' => $month]) }}" class="btn btn-success" target="_blank">
                    <span class="fas fa-download"></span>
                    Download PDF
                </a>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $employeeCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutter align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Eligible Dates</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $eligibleDateCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Estimated Amount</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalAmount, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-md">
                    <thead class="thead">
                        <tr>
                            <th>Emp Code</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Eligible Date</th>
                            <th>Day</th>
                            <th>Attendance</th>
                            <th>Shift</th>
                            <th class="text-right">OT Minutes</th>
                            <th class="text-right">Amount</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportRows as $reportRow)
                            @foreach ($reportRow['eligible_rows'] as $eligibleRow)
                                <tr>
                                    <td>{{ $reportRow['employee']->emp_code }}</td>
                                    <td>
                                        {{ $reportRow['employee']->name }}
                                        <div class="small text-muted">{{ $reportRow['employee']->designation->desg_desc ?? '' }}</div>
                                    </td>
                                    <td>{{ $reportRow['employee']->department->dept_desc ?? '' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($eligibleRow['date'])->format('d-M-Y') }}</td>
                                    <td>{{ $eligibleRow['day'] }}</td>
                                    <td>
                                        {{ $eligibleRow['time_in'] ? \Carbon\Carbon::parse($eligibleRow['time_in'])->format('h:i A') : '-' }}
                                        -
                                        {{ $eligibleRow['time_out'] ? \Carbon\Carbon::parse($eligibleRow['time_out'])->format('h:i A') : '-' }}
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($eligibleRow['shift_start'])->format('h:i A') }}
                                        -
                                        {{ \Carbon\Carbon::parse($eligibleRow['shift_end'])->format('h:i A') }}
                                    </td>
                                    <td class="text-right">{{ $eligibleRow['overtime_minutes'] }}</td>
                                    <td class="text-right">{{ number_format($eligibleRow['amount'], 2) }}</td>
                                    <td>
                                        @if ($eligibleRow['is_weekly_rest'] ?? false)
                                            <span class="badge badge-primary">Weekly Rest</span>
                                        @elseif ($eligibleRow['is_holiday'])
                                            <span class="badge badge-info">Holiday</span>
                                        @else
                                            <span class="badge badge-secondary">Working Day</span>
                                        @endif
                                        @if ($eligibleRow['is_security'])
                                            <span class="badge badge-warning">Security</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No unclaimed eligible overtime found for this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($reportRows->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-right">Total</th>
                                <th class="text-right">{{ $totalMinutes }}</th>
                                <th class="text-right">{{ number_format($totalAmount, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
