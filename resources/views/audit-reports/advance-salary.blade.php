@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Advance Salary Audit Report</h1>
        <a href="{{ route('audit-reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('audit-reports.advance-salary') }}" class="row align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Month</label>
                    <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
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
                    <a href="{{ route('audit-reports.advance-salary') }}" class="btn btn-outline-secondary">Clear Filters</a>
                </div> --}}
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
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="advanceSalaryDownloadMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-download"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="advanceSalaryDownloadMenu">
                            <a class="dropdown-item" href="{{ route('audit-reports.advance-salary.download', ['scope' => 'all', 'month' => $month]) }}" target="_blank">All Records</a>
                            <a class="dropdown-item" href="{{ route('audit-reports.advance-salary.download', ['scope' => 'approved', 'month' => $month]) }}" target="_blank">Approved Records</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="advanceSalaryAuditTabs" role="tablist">
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
                    <th>#</th><th>Employee</th><th>Department</th><th>Days</th><th>Requested</th><th>Salary</th><th>Limit</th><th>Sanctioned</th><th>Status</th><th>Reason</th><th>HOD Approval</th><th>HR Approval</th><th>Accounts Approval</th><th>Audit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tabApplications as $application)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ capitalizeWords($application->employee->name ?? $application->dailyWager->name ?? '') }}<br><small class="text-muted">{{ $application->emp_code }}</small></td>
                        <td>{{ $application->employee->department->dept_desc ?? $application->dailyWager->department->dept_desc ?? '-' }}</td>
                        <td>{{ $application->eligible_days }}</td>
                        <td>{{ number_format($application->requested_amount ?? 0) }}</td>
                        <td>{{ number_format($application->gross_salary ?? 0) }}</td>
                        <td>{{ number_format($application->max_amount ?? 0) }}</td>
                        <td>{{ number_format($application->sanctioned_amount ?? 0) }}</td>
                        <td>{{ $application->status }}</td>
                        <td>{{ $application->reason ?: '-' }}</td>
                        <td>{{ capitalizeWords($application->hodApprover->name ?? '') ?: '-' }}<br><small>{{ $application->hod_remarks ?: '' }}</small></td>
                        <td>{{ capitalizeWords($application->hrApprover->name ?? '') ?: '-' }}<br><small>{{ $application->hr_remarks ?: '' }}</small></td>
                        <td>{{ capitalizeWords($application->accountsApprover->name ?? '') ?: '-' }}<br><small>{{ $application->accounts_remarks ?: '' }}</small></td>
                        <td>
                            @if($application->audited_at)
                                <span class="text-success"><i class="fas fa-check-circle"></i> Audited</span><br>
                                <small class="text-muted text-nowrap">{{ $application->audited_by }} &bull; {{ $application->audited_at->format('Y-m-d H:i') }}</small>
                            @elseif($application->status === \App\Models\AdvanceSalaryApplication::STATUS_APPROVED)
                                <form method="POST" action="{{ route('audit-reports.advance-salary.audit', $application->id) }}">
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
                    <tr><td colspan="14" class="text-center text-muted">No {{ $tabKey === 'regular' ? 'regular' : 'daily-wager' }} advance salary applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
