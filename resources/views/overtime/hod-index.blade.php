@extends('layouts.app')

@php
  use App\Models\OvertimeApplication;
  use Carbon\Carbon;
@endphp

@push('styles')
  .table { border: 1px solid #ccc; }
  .table>:not(caption)>*>* { padding: .5rem .65rem; vertical-align: middle; }
  .overtime-hod-table { font-size: 13px; }
@endpush

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Overtime HOD Approvals</h3>

          <form action="{{ route('overtime.hod-index') }}" method="GET" class="d-flex align-items-end gap-2 flex-wrap mt-4 mb-4">
            <div>
              <label for="month" class="form-label">Month</label>
              <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <button type="submit" class="btn btn-primary">View Applications</button>
            {{-- <a href="{{ route('overtime.create', auth()->user()->emp_code) }}" class="btn btn-secondary">Back</a> --}}
          </form>

          <div class="table-responsive">
            <table class="table overtime-hod-table">
              <thead>
                <tr>
                  <th>Applied At</th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Date</th>
                  <th>OT Minutes</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications as $application)
                  <tr>
                    <td>{{ $application->applied_at ? Carbon::parse($application->applied_at)->format('j M Y h:i A') : '-' }}</td>
                    <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
                    <td>{{ $application->emp_code }}</td>
                    <td>{{ Carbon::parse($application->overtime_date)->format('d M Y') }}</td>
                    <td>{{ $application->overtime_minutes }}</td>
                    <td>PKR {{ number_format($application->calculated_amount, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
                    <td>
                      <a href="{{ route('overtime.hod-show', $application->id) }}" class="btn btn-sm btn-primary">
                        {{ $application->status === OvertimeApplication::STATUS_PENDING ? 'Review' : 'View' }}
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted">No subordinate overtime applications found for this month.</td>
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
