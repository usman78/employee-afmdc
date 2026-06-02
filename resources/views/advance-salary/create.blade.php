@extends('layouts.app')

@push('styles')
  .advance-summary {
    border: 1px solid #d7e6f8;
    background: #f6fbff;
    padding: 16px;
    border-radius: 6px;
  }
  .advance-summary strong {
    color: #2196f3;
  }
  .table {
    border: 1px solid #ccc;
  }
  .table>:not(caption)>*>* {
    padding: .5rem .7rem;
  }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="mb-0">Advance Salary Application</h3>
            @if(Auth::user()->isBoss())
              <a href="{{ route('advance-salary.hod-index') }}" class="btn btn-primary">
                <i class="fa-solid fa-list-check"></i> View Subordinate Applications
              </a>
            @endif
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

          <div class="advance-summary mt-4 mb-4">
            <div class="row gy-3">
              <div class="col-md-4">
                <div>Employee</div>
                <strong>{{ capitalizeWords($employee->name) }} ({{ $employee->emp_code }})</strong>
              </div>
              <div class="col-md-4">
                <div>Application Month</div>
                <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $summary['salary_month'])->format('F Y') }}</strong>
              </div>
              <div class="col-md-4">
                <div>Eligible Days</div>
                <strong>{{ $summary['eligible_days'] }} / {{ $summary['required_days'] }}</strong>
              </div>
              <div class="col-md-4">
                <div>Gross Salary</div>
                <strong>
                  {{ $summary['gross_salary'] === null ? 'Not found' : 'PKR ' . number_format($summary['gross_salary']) }}
                </strong>
              </div>
              <div class="col-md-4">
                <div>Monthly Advance Limit</div>
                <strong>PKR {{ number_format($summary['max_amount']) }}</strong>
              </div>
              <div class="col-md-4">
                <div>Remaining Limit</div>
                <strong>PKR {{ number_format($summary['remaining_limit']) }}</strong>
              </div>
            </div>
          </div>

          <div class="alert {{ $summary['is_eligible'] ? 'alert-success' : 'alert-warning' }}">
            {{ $summary['message'] }}
          </div>

          <form action="{{ route('advance-salary.store', $employee->emp_code) }}" method="POST" class="mt-4">
            @csrf
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="requested_amount" class="form-label">Required Amount (PKR)</label>
                <input
                  type="number"
                  id="requested_amount"
                  name="requested_amount"
                  class="form-control"
                  min="1"
                  max="{{ (int) $summary['remaining_limit'] }}"
                  value="{{ old('requested_amount') }}"
                  {{ $summary['is_eligible'] ? 'required' : 'disabled' }}
                >
                <small class="text-muted">Maximum allowed: PKR {{ number_format($summary['remaining_limit']) }}</small>
              </div>
              <div class="col-md-8 mb-3">
                <label for="reason" class="form-label">Reason</label>
                <textarea
                  id="reason"
                  name="reason"
                  class="form-control"
                  rows="4"
                  maxlength="1000"
                  {{ $summary['is_eligible'] ? 'required' : 'disabled' }}
                >{{ old('reason') }}</textarea>
              </div>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="fa-solid fa-backward"></i> Back
              </a>
              <button type="submit" class="btn btn-success" {{ $summary['is_eligible'] ? '' : 'disabled' }}>
                <i class="fa-solid fa-money-bill-transfer"></i> Submit Application
              </button>
            </div>
          </form>

          <h3 class="mt-5">Current Month Applications</h3>
          <table class="table mt-3 mb-5">
            <thead>
              <tr>
                <th>Applied At</th>
                <th>Requested Amount</th>
                <th>Limit At Apply</th>
                <th>Eligible Days</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($applications as $application)
                <tr>
                  <td>{{ $application->applied_at ? \Carbon\Carbon::parse($application->applied_at)->format('j M Y h:i A') : '-' }}</td>
                  <td>PKR {{ number_format($application->requested_amount) }}</td>
                  <td>PKR {{ number_format($application->max_amount) }}</td>
                  <td>{{ $application->eligible_days }}</td>
                  <td><span class="badge bg-secondary">{{ ucfirst($application->status) }}</span></td>
                  <td>{{ $application->reason }}</td>
                  <td>
                    @if($application->status === \App\Models\AdvanceSalaryApplication::STATUS_PENDING)
                      <form
                        action="{{ route('advance-salary.revoke', ['emp_code' => $employee->emp_code, 'application' => $application->id]) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure you want to revoke this advance salary application?');"
                      >
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i class="fa-solid fa-ban"></i> Revoke
                        </button>
                      </form>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">No advance salary application submitted for this month.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
