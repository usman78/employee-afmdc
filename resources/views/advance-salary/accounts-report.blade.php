@extends('layouts.app')

@php
  use App\Models\AdvanceSalaryApplication;
@endphp

@push('styles')
  .table {
    border: 1px solid #ccc;
  }
  .table>:not(caption)>*>* {
    padding: .5rem .6rem;
    vertical-align: middle;
  }
  .accounts-report-table {
    font-size: 13px;
  }
  .accounts-report-table textarea {
    min-width: 130px;
  }
@endpush

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="mb-0">Advance Salary Finance Report</h3>
            <a href="{{ route('finance-reports') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          </div>

          @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-warning mt-3">{{ session('error') }}</div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger mt-3">{{ $errors->first() }}</div>
          @endif

          <form action="{{ route('advance-salary.accounts-report') }}" method="GET" class="d-flex align-items-end gap-2 flex-wrap mt-4 mb-4">
            <div>
              <label for="month" class="form-label">Month</label>
              <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div>
              <label for="status" class="form-label">Status</label>
              <select id="status" name="status" class="form-control">
                <option value="">All Statuses</option>
                @foreach($statuses as $statusValue => $statusLabel)
                  <option value="{{ $statusValue }}" @selected(($status ?? '') === $statusValue)>
                    {{ $statusLabel }}
                  </option>
                @endforeach
              </select>
            </div>
            <button type="submit" class="btn btn-primary">View Report</button>
          </form>

          <div class="table-responsive">
            <table class="table accounts-report-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Designation</th>
                  <th>Department</th>
                  <th>Days Worked</th>
                  <th>Requested</th>
                  <th>Monthly Salary</th>
                  <th>Salary Payable</th>
                  <th>Sanctioned by HR</th>
                  <th>HR Approved By</th>
                  <th>Status</th>
                  <th>Accounts Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications as $application)
                  @php
                    $salaryPayable = (int) floor(((float) $application->gross_salary / 30) * (int) $application->eligible_days);
                    $canAccountsAct = $application->status === AdvanceSalaryApplication::STATUS_HR_APPROVED;
                  @endphp
                  <tr>
                    <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
                    <td>{{ $application->emp_code }}</td>
                    <td>{{ $application->employee->designation->desg_short ?? '-' }}</td>
                    <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
                    <td>{{ $application->eligible_days }}</td>
                    <td>PKR {{ number_format($application->requested_amount) }}</td>
                    <td>PKR {{ number_format($application->gross_salary) }}</td>
                    <td>PKR {{ number_format($salaryPayable) }}</td>
                    <td>PKR {{ number_format($application->sanctioned_amount) }}</td>
                    <td>{{ $application->hrApprover ? capitalizeWords($application->hrApprover->name) : '-' }}</td>
                    <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
                    <td>
                      @if($canAccountsAct)
                        <form action="{{ route('advance-salary.accounts-decision', $application->id) }}" method="POST">
                          @csrf
                          <textarea name="remarks" class="form-control form-control-sm mb-2" rows="2" placeholder="Remarks">{{ old('remarks') }}</textarea>
                          <div class="d-flex gap-1">
                            <button type="submit" name="decision" value="approve" class="btn btn-sm btn-success">Approve</button>
                            <button type="submit" name="decision" value="reject" class="btn btn-sm btn-danger">Reject</button>
                          </div>
                        </form>
                      @else
                        <small class="text-muted">{{ $application->accounts_remarks ?: $application->hr_remarks ?: '-' }}</small>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="12" class="text-center text-muted">No HR approved advance salary applications found for this month.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
