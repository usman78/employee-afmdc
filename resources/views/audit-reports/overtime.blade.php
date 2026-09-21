@extends('layouts.app')

@push('styles')
    table.table.table-bordered.table-sm {
        font-size: 12px;
    }
    .table .thead-light th {
        color: #000;
    }    
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Overtime Audit Report</h1>
        <a href="{{ route('audit-reports.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('audit-reports.overtime') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Month</label>
                    <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary">View Report</button>
                </div>
                <div class="col-md-auto mt-3 mt-md-0">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="overtimeDownloadMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-download"></i> Download
                        </button>
                        <div class="dropdown-menu" aria-labelledby="overtimeDownloadMenu">
                            {{-- <a class="dropdown-item" href="{{ route('audit-reports.overtime.download', ['scope' => 'all', 'month' => $month]) }}" target="_blank">All Records</a> --}}
                            {{-- <a class="dropdown-item" href="{{ route('audit-reports.overtime.download', ['scope' => 'approved', 'month' => $month]) }}" target="_blank">Approved Records</a> --}}
                            <a class="dropdown-item" href="#" id="ot-approved-report-btn">All Approved</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Employee</th><th>Department</th><th>Date</th><th>Shift</th><th>Time In/Out</th><th>Claimed Minutes</th><th>Sanctioned Minutes</th><th>Salary</th><th>Hourly Rate</th><th>Calculated</th><th>Sanctioned</th><th>Status</th><th>HOD Approval</th><th>HR Approval</th><th>Finance Approval</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ capitalizeWords($application->employee->name ?? '') }}<br><small class="text-muted">{{ $application->emp_code }}</small></td>
                        <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
                        <td>{{ optional($application->overtime_date)->format('d M Y') }}</td>
                        <td>{{ $application->shift_start ? timeFormatFromString($application->shift_start) : '-' }} - {{ $application->shift_end ? timeFormatFromString($application->shift_end) : '-' }}</td>
                        <td>{{ $application->time_in ? timeFormatFromString($application->time_in) : '-' }} - {{ $application->time_out ? timeFormatFromString($application->time_out) : '-' }}</td>
                        <td>{{ formatMinutes($application->overtime_minutes ?? 0) }}</td>
                        <td>{{ $application->sanctioned_minutes ? formatMinutes($application->sanctioned_minutes) : '-' }}</td>
                        <td>{{ number_format($application->gross_salary ?? 0) }}</td>
                        <td>{{ number_format($application->hourly_rate ?? 0, 2) }}</td>
                        <td>{{ number_format($application->calculated_amount ?? 0, 2) }}</td>
                        <td>{{ number_format($application->sanctioned_amount ?? 0, 2) }}</td>
                        <td @class([
                            'text-center',
                            'table-success' => $application->status === 'approved',
                            'table-warning' => $application->status === 'pending',
                        ])>{{ $application->status }}</td>
                        <td>{{ capitalizeWords($application->hodApprover->name ?? '') ?: '-' }}<br><small>{{ $application->hod_remarks ?: '' }}</small></td>
                        <td>{{ capitalizeWords($application->hrApprover->name ?? '') ?: '-' }}<br><small>{{ $application->hr_remarks ?: '' }}</small></td>
                        <td>{{ capitalizeWords($application->financeApprover->name ?? '') ?: '-' }}<br><small>{{ $application->finance_remarks ?: '' }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="16" class="text-center text-muted">No overtime applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
document.getElementById('ot-approved-report-btn').addEventListener('click', function(event) {
    event.preventDefault();

    const month = document.getElementById('month').value;
    if (!/^\d{4}-(0[1-9]|1[0-2])$/.test(month)) {
      Swal.fire('Month required', 'Please select a month in YYYY-MM format.', 'warning');
      return;
    }

    const reportUrl = new URL('http://110.39.174.203:7777/reports/rwservlet');
    reportUrl.searchParams.set('P_RMS', '');
    reportUrl.searchParams.set('report', 'R:\\Applications\\Payroll\\Reports\\over_time_reg.rdf');
    reportUrl.searchParams.set('destype', 'cache');
    reportUrl.searchParams.set('desformat', 'pdf');
    reportUrl.searchParams.set('dt1', `${month.slice(5, 7)}-${month.slice(0, 4)}`);
    window.open(reportUrl.toString(), '_blank');
});
@endpush