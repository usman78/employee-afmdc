@extends('layouts.app')

@php
  use App\Models\AdvanceSalaryApplication;
@endphp

@push('styles')
  .table {
    border: 1px solid #ccc;
  }
  .table>:not(caption)>*>* {
    padding: .5rem .65rem;
    vertical-align: middle;
  }
  .advance-hod-table {
    font-size: 13px;
  }
@endpush

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Subordinate Advance Salary Applications</h3>

          @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-warning mt-3">{{ session('error') }}</div>
          @endif

          <form action="{{ route('advance-salary.hod-index') }}" method="GET" class="d-flex align-items-end gap-2 flex-wrap mt-4 mb-4">
            <div>
              <label for="month" class="form-label">Month</label>
              <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <button type="submit" class="btn btn-primary">View Applications</button>
            <a href="{{ route('advance-salary.create', auth()->user()->emp_code) }}" class="btn btn-secondary">Back</a>
          </form>

          <div class="table-responsive">
            <table class="table advance-hod-table">
              <thead>
                <tr>
                  <th>Applied At</th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Designation</th>
                  <th>Department</th>
                  <th>Days Worked</th>
                  <th>Requested</th>
                  <th>Advance Limit</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications as $application)
                  <tr>
                    <td>{{ $application->applied_at ? \Carbon\Carbon::parse($application->applied_at)->format('j M Y h:i A') : '-' }}</td>
                    <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
                    <td>{{ $application->emp_code }}</td>
                    <td>{{ $application->employee->designation->desg_short ?? '-' }}</td>
                    <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
                    <td>{{ $application->eligible_days }}</td>
                    <td>PKR {{ number_format($application->requested_amount) }}</td>
                    <td>PKR {{ number_format($application->max_amount) }}</td>
                    <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
                    <td>
                      <a href="{{ route('advance-salary.hod-show', $application->id) }}" class="btn btn-sm btn-primary">
                        {{ $application->status === AdvanceSalaryApplication::STATUS_PENDING ? 'Review' : 'View' }}
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center text-muted">No subordinate advance salary applications found for this month.</td>
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
