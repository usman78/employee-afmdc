@extends('layouts.app')

@php
  use Carbon\Carbon;

  $formatDate = function ($value) {
    return $value ? Carbon::parse($value)->format('j M') : '--';
  };

  $formatTime = function ($value) {
    return $value ? Carbon::parse($value)->format('H:i') : '--:--';
  };

  $formatDateTime = function ($value) {
    return $value ? Carbon::parse($value)->format('j M Y H:i') : '--';
  };
@endphp

@push('styles')
  .table {
    border: 1px solid #ccc;
  }
  .table>:not(caption)>*>* {
    padding: .5rem .7rem;
  }
  th, td {
    text-align: left;
    vertical-align: middle;
  }
  td {
    font-size: 14px;
  }
  .manual-attendance-table-wrap {
    overflow-x: auto;
  }
  .employee-stack strong,
  .employee-stack span {
    display: block;
  }
  .employee-stack span {
    color: #555;
    font-size: 12px;
    line-height: 1.35;
  }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h3>Manual Attendance Report</h3>
              <p class="text-muted mb-0">Manual attendance changes by date range.</p>
            </div>
            <a href="{{ route('hr-reports') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          </div>

          <form action="{{ route('manual-attendance-report-data') }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label for="from_date" class="form-label">From Date</label>
                <input
                  type="date"
                  id="from_date"
                  name="from_date"
                  class="form-control @error('from_date') is-invalid @enderror"
                  value="{{ old('from_date', $from_date ?? Carbon::now()->startOfMonth()->toDateString()) }}"
                  required
                >
                @error('from_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label for="to_date" class="form-label">To Date</label>
                <input
                  type="date"
                  id="to_date"
                  name="to_date"
                  class="form-control @error('to_date') is-invalid @enderror"
                  value="{{ old('to_date', $to_date ?? Carbon::today()->toDateString()) }}"
                  required
                >
                @error('to_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Get Report</button>
              </div>
            </div>
          </form>

          <div class="mb-4" style="padding: 10px; border-radius: 10px; background-color: #cedaff;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">
                {{ Carbon::parse($from_date ?? Carbon::now()->startOfMonth()->toDateString())->format('j M Y') }}
                to
                {{ Carbon::parse($to_date ?? Carbon::today()->toDateString())->format('j M Y') }}
              </h6>
              <div class="d-flex align-items-center gap-2">
                <small class="text-muted">{{ ($manual_rows ?? collect())->count() }} record(s)</small>
                <form action="{{ route('manual-attendance-report-download') }}" method="POST" target="_blank">
                  @csrf
                  <input type="hidden" name="from_date" value="{{ $from_date ?? Carbon::now()->startOfMonth()->toDateString() }}">
                  <input type="hidden" name="to_date" value="{{ $to_date ?? Carbon::today()->toDateString() }}">
                  <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-download"></i> Download PDF
                  </button>
                </form>
              </div>
            </div>

            <div class="manual-attendance-table-wrap">
              <table class="table mt-2 mb-4">
                <thead>
                  <tr>
                    <th>Sr#</th>
                    <th>Code</th>
                    <th>Employee</th>
                    <th style="width: 70px">Date</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Remarks</th>
                    <th>Username</th>
                    <th>User IP</th>
                    <th>Created At</th>
                    <th>Attendance Type</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse(($manual_rows ?? collect()) as $row)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $row['emp_code'] }}</td>
                      <td class="employee-stack">
                        <strong>{{ $row['name'] }}</strong>
                        <span>{{ $row['designation'] ?: '--' }}</span>
                        <span>{{ $row['department'] ?: '--' }}</span>
                      </td>
                      <td>{{ $formatDate($row['date']) }}</td>
                      <td>{{ $formatTime($row['time_in']) }}</td>
                      <td>{{ $formatTime($row['time_out']) }}</td>
                      <td>{{ $row['remarks'] ?: '--' }}</td>
                      <td>{{ $row['username'] ?: '--' }}</td>
                      <td>{{ $row['user_ip'] ?: '--' }}</td>
                      <td>{{ $formatDateTime($row['time_of_change']) }}</td>
                      <td>{{ $row['att_type'] ?: '--' }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="11" class="text-center">No manual attendance records found for this date range.</td>
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
@endsection
