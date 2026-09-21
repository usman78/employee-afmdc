@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Advance Salary Audit Report</h1>
        <a href="{{ route('audit-reports.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('audit-reports.advance-salary') }}" class="row align-items-end">
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
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="advanceSalaryDownloadMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-download"></i> Download
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

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th><th>Employee</th><th>Department</th><th>Days</th><th>Requested</th><th>Salary</th><th>Limit</th><th>Sanctioned</th><th>Status</th><th>Reason</th><th>HOD Approval</th><th>HR Approval</th><th>Accounts Approval</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ capitalizeWords($application->employee->name ?? '') }}<br><small class="text-muted">{{ $application->emp_code }}</small></td>
                        <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
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
                    </tr>
                @empty
                    <tr><td colspan="13" class="text-center text-muted">No advance salary applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
