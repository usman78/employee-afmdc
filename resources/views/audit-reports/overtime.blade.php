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
        <a href="{{ route('audit-reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
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
                <div class="col-md-3">
                    <label for="audit_status" class="form-label">Audit</label>
                    <select id="audit_status" name="audit_status" class="form-control">
                        <option value="">All Audit States</option>
                        <option value="audited" @selected(($auditStatus ?? '') === 'audited')>Audited</option>
                        <option value="unaudited" @selected(($auditStatus ?? '') === 'unaudited')>Not Audited</option>
                    </select>
                </div>
                {{-- <div class="col-md-auto mt-3 mt-md-0">
                    <a href="{{ route('audit-reports.overtime') }}" class="btn btn-outline-secondary">Clear Filters</a>
                </div> --}}
                <div class="col-md-auto mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary">View Report</button>
                </div>
                <div class="col-md-auto mt-3 mt-md-0">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="overtimeDownloadMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-download"></i>
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

    <ul class="nav nav-tabs mb-3" id="overtimeAuditTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="regular-tab" data-toggle="tab" href="#regular" role="tab" aria-controls="regular" aria-selected="true">Regular Applications</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="daily-wager-tab" data-toggle="tab" href="#daily-wager" role="tab" aria-controls="daily-wager" aria-selected="false">Daily Wager Applications</a>
        </li>
    </ul>

    <div class="tab-content">
        @foreach(['regular' => $regularApplications, 'daily-wager' => $dailyWagerApplications] as $tabKey => $tabApplications)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tabKey }}" role="tabpanel" aria-labelledby="{{ $tabKey }}-tab">
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Employee</th><th>Department</th><th>Date</th><th>Shift</th><th>Time In/Out</th><th>Claimed Overtime</th><th>Sanctioned Overtime</th><th>Salary</th><th>Hourly Rate</th><th>Calculated</th><th>Sanctioned</th><th>Status</th><th>HOD Approval</th><th>HR Approval</th><th>Finance Approval</th><th>Audit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tabApplications as $application)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ capitalizeWords($application->employee->name ?? $application->dailyWager->name ?? '') }}<br><small class="text-muted">{{ $application->emp_code }}</small></td>
                        <td>{{ $application->employee->department->dept_desc ?? $application->dailyWager->department->dept_desc ?? '-' }}</td>
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
                        <td>{{ capitalizeWords($application->hodApprover->name ?? '') ?: '-' }}<br><small class="text-muted">{{ $application->hod_remarks ?: '' }}</small></td>
                        <td>{{ capitalizeWords($application->hrApprover->name ?? '') ?: '-' }}<br><small class="text-muted">{{ $application->hr_remarks ?: '' }}</small></td>
                        <td>{{ capitalizeWords($application->financeApprover->name ?? '') ?: '-' }}<br><small class="text-muted">{{ $application->finance_remarks ?: '' }}</small></td>
                        <td>
                            @if($application->audited_at)
                                <span class="text-success"><i class="fas fa-check-circle"></i> Audited</span><br>
                                <small class="text-muted text-nowrap">{{ $application->audited_by }} &bull; {{ $application->audited_at->format('Y-m-d H:i') }}</small>
                            @elseif($application->status === \App\Models\OvertimeApplication::STATUS_APPROVED)
                                <form method="POST" action="{{ route('audit-reports.overtime.audit', $application->id) }}">
                                    @csrf
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <input type="hidden" name="audit_status" value="{{ $auditStatus }}">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as audited"><i class="fas fa-check"></i> Verify</button>
                                </form>
                            @else
                                <span class="text-muted">Pending approval</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="17" class="text-center text-muted">No {{ $tabKey === 'regular' ? 'regular' : 'daily-wager' }} overtime applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
        </div>
        @endforeach
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
