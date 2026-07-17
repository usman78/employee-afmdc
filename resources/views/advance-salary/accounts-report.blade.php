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
  .portfolio-details .portfolio-info ul li {
    margin-top: 10px;
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
            <div class="dropdown">
              <button class="btn btn-primary dropdown-toggle" type="button"
                  id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                  aria-expanded="false">
                  Download Report
              </button>
              <div class="dropdown-menu animated--fade-in"
                  aria-labelledby="dropdownMenuButton">
                  <a class="dropdown-item" href="{{ route('advance-salary.accounts-approved-download', ['month' => $month]) }}" target="_blank">All Approved</a>
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#filterByNameModal">Download By Name</a>
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#filterByDateModal">Download by Finance Approval Date</a>
              </div>
            </div>
          </form>

          <ul class="nav nav-tabs mt-4 mb-3" id="advanceSalaryAccountsTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link active" id="accounts-on-roll-tab" data-toggle="tab" href="#accounts-on-roll" role="tab" aria-controls="accounts-on-roll" aria-selected="true">
                On Roll Applications
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" id="accounts-daily-wager-tab" data-toggle="tab" href="#accounts-daily-wager" role="tab" aria-controls="accounts-daily-wager" aria-selected="false">
                Daily Wager Applications
              </a>
            </li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane fade show active" id="accounts-on-roll" role="tabpanel" aria-labelledby="accounts-on-roll-tab">
              <div class="table-responsive">
                <table class="table accounts-report-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Code</th>
                      <th>Designation</th>
                      <th>Department</th>
                      <th>Days Worked</th>
                      <th>Requested (PKR)</th>
                      <th>Monthly Salary (PKR)</th>
                      <th>Sanctioned by HR (PKR)</th>
                      <th>HR Approved By</th>
                      <th>Status</th>
                      <th>Accounts Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($onRollApplications as $application)
                      @php
                        $canAccountsAct = $application->status === AdvanceSalaryApplication::STATUS_HR_APPROVED;
                      @endphp
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
                        <td>{{ $application->emp_code }}</td>
                        <td>{{ $application->employee->designation->desg_short ?? '-' }}</td>
                        <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
                        <td>{{ $application->eligible_days }}</td>
                        <td>{{ number_format($application->requested_amount) }}</td>
                        <td>{{ number_format($application->gross_salary) }}</td>
                        <td>{{ number_format($application->sanctioned_amount) }}</td>
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
                        <td colspan="12" class="text-center text-muted">No on-roll HR approved advance salary applications found for this month.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            <div class="tab-pane fade" id="accounts-daily-wager" role="tabpanel" aria-labelledby="accounts-daily-wager-tab">
              <div class="table-responsive">
                <table class="table accounts-report-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Code</th>
                      <th>Designation</th>
                      <th>Department</th>
                      <th>Days Worked</th>
                      <th>Requested (PKR)</th>
                      <th>Monthly Salary (PKR)</th>
                      <th>Sanctioned by HR (PKR)</th>
                      <th>HR Approved By</th>
                      <th>Status</th>
                      <th>Accounts Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($dailyWagerApplications as $application)
                      @php
                        $canAccountsAct = $application->status === AdvanceSalaryApplication::STATUS_HR_APPROVED;
                      @endphp
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
                        <td>{{ $application->emp_code }}</td>
                        <td>{{ $application->employee->designation->desg_short ?? '-' }}</td>
                        <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
                        <td>{{ $application->eligible_days }}</td>
                        <td>{{ number_format($application->requested_amount) }}</td>
                        <td>{{ number_format($application->gross_salary) }}</td>
                        <td>{{ number_format($application->sanctioned_amount) }}</td>
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
                        <td colspan="12" class="text-center text-muted">No daily-wager HR approved advance salary applications found for this month.</td>
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
  </div>
</div>
<!-- Filter By Name Modal -->
<div class="modal fade" id="filterByNameModal" tabindex="-1" role="dialog" aria-labelledby="filterByNameModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterByNameModalLabel">Download Report - Filter by Name</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('advance-salary.name-filtered-download') }}" method="GET" target="_blank">
        <div class="modal-body">
          <div class="form-group">
            <label for="employeeName" class="form-label">Employee Name</label>
            <input type="text" id="employeeName" name="employee_name" class="form-control" placeholder="Enter employee name" required>
          </div>
          <input type="hidden" name="month" value="{{ $month }}">
          <input type="hidden" name="status" value="{{ $status ?? '' }}">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Download</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Filter By Approval Date Modal -->
<div class="modal fade" id="filterByDateModal" tabindex="-1" role="dialog" aria-labelledby="filterByDateModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterByDateModalLabel">Download Report - Filter by Approval Date</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('advance-salary.date-filtered-download') }}" method="GET" target="_blank">
        <div class="modal-body">
          <div class="form-group">
            <label for="approvalDateFrom" class="form-label">From Date</label>
            <input type="date" id="approvalDateFrom" name="from_date" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="approvalDateTo" class="form-label">To Date</label>
            <input type="date" id="approvalDateTo" name="to_date" class="form-control" required>
          </div>
          <input type="hidden" name="month" value="{{ $month }}">
          <input type="hidden" name="status" value="{{ $status ?? '' }}">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Download</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
