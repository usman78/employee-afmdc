@extends('layouts.app')

@php
  use App\Models\OvertimeApplication;
  use Carbon\Carbon;
@endphp

@push('styles')
  .table { border: 1px solid #ccc; }
  .table>:not(caption)>*>* { padding: .5rem .6rem; vertical-align: middle; }
  .overtime-finance-table { font-size: 13px; }
  .overtime-finance-table textarea { min-width: 130px; }
@endpush

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="mb-0">Overtime Finance Report</h3>
            <a href="{{ route('finance-reports') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          </div>

          @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-warning mt-3">{{ session('error') }}</div>
          @endif

          <form action="{{ route('overtime.finance-report') }}" method="GET" class="d-flex align-items-end gap-2 flex-wrap mt-4 mb-4">
            <div>
              <label for="month" class="form-label">Month</label>
              <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div>
              <label for="status" class="form-label">Status</label>
              <select id="status" name="status" class="form-control">
                <option value="">All Statuses</option>
                @foreach($statuses as $statusValue => $statusLabel)
                  <option value="{{ $statusValue }}" @selected(($status ?? '') === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
              </select>
            </div>
            <button type="submit" class="btn btn-primary">View Report</button>
          </form>

          <div class="table-responsive">
            <table class="table overtime-finance-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Date</th>
                  <th>OT Minutes</th>
                  <th>Sanctioned Minutes</th>
                  <th>Sanctioned Amount</th>
                  <th>Status</th>
                  <th>Finance Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications as $application)
                  @php
                    $canAccountsAct = $application->status === OvertimeApplication::STATUS_HR_APPROVED;
                  @endphp
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
                    <td>{{ $application->emp_code }}</td>
                    <td>{{ Carbon::parse($application->overtime_date)->format('d M Y') }}</td>
                    <td>{{ $application->overtime_minutes }}</td>
                    <td>{{ $application->sanctioned_minutes ?? '-' }}</td>
                    <td>
                      @if($application->sanctioned_amount)
                        PKR {{ number_format($application->sanctioned_amount, 2) }}
                      @else
                        -
                      @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
                    <td>
                      @if($canAccountsAct)
                        <form action="{{ route('overtime.finance-decision', $application->id) }}" method="POST">
                          @csrf
                          <textarea name="remarks" class="form-control form-control-sm mb-2" rows="2" placeholder="Remarks">{{ old('remarks') }}</textarea>
                          <div class="d-flex gap-1">
                            <button type="submit" name="decision" value="approve" class="btn btn-sm btn-success">Approve</button>
                            <button type="submit" name="decision" value="reject" class="btn btn-sm btn-danger">Reject</button>
                          </div>
                        </form>
                      @else
                        <small class="text-muted">{{ $application->finance_remarks ?: $application->hr_remarks ?: '-' }}</small>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center text-muted">No overtime applications found for this month.</td>
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
