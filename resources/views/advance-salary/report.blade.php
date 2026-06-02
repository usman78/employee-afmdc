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
  .advance-report-table {
    font-size: 13px;
  }
  .advance-report-table input,
  .advance-report-table textarea {
    min-width: 130px;
  }
@endpush

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Advance Salary Report</h3>

          @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-warning mt-3">{{ session('error') }}</div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger mt-3">{{ $errors->first() }}</div>
          @endif

          <form action="{{ route('advance-salary.report') }}" method="GET" class="d-flex align-items-end gap-2 flex-wrap mt-4 mb-4">
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
            <table class="table advance-report-table">
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
                  <th>Advance Limit</th>
                  <th>Sanctioned</th>
                  <th>Status</th>
                  <th>HR Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications as $application)
                  @php
                    $salaryPayable = (int) floor(((float) $application->gross_salary / 30) * (int) $application->eligible_days);
                    $canHrAct = $application->status === AdvanceSalaryApplication::STATUS_HOD_APPROVED;
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
                    <td>PKR {{ number_format($application->max_amount) }}</td>
                    <td>
                      @if($application->sanctioned_amount)
                        PKR {{ number_format($application->sanctioned_amount) }}
                      @else
                        -
                      @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
                    <td>
                      @if($canHrAct)
                        <form action="{{ route('advance-salary.hr-decision', $application->id) }}" method="POST">
                          @csrf
                          <input
                            type="number"
                            name="sanctioned_amount"
                            class="form-control form-control-sm mb-2"
                            min="1"
                            max="{{ (int) $application->max_amount }}"
                            value="{{ old('sanctioned_amount', (int) min($application->requested_amount, $application->max_amount)) }}"
                          >
                          <textarea name="remarks" class="form-control form-control-sm mb-2" rows="2" placeholder="Remarks">{{ old('remarks') }}</textarea>
                          <div class="d-flex gap-1">
                            <button type="submit" name="decision" value="approve" class="btn btn-sm btn-success">Approve</button>
                            <button type="submit" name="decision" value="reject" class="btn btn-sm btn-danger">Reject</button>
                          </div>
                        </form>
                      @else
                        <small class="text-muted">{{ $application->hr_remarks ?: $application->hod_remarks ?: '-' }}</small>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="12" class="text-center text-muted">No advance salary applications found for this month.</td>
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
