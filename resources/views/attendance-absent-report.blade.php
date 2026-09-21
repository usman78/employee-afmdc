@extends('layouts.app')

@php
  use Carbon\Carbon;
@endphp

@push('styles')
  .table {
    border: 1px solid #ccc;
  }
  .table>:not(caption)>*>* {
    padding: .5rem .7rem;
  }
  td {
    font-size: 14px;
    vertical-align: middle;
  }
  .status-absent td {
    background-color: #ffd6d6;
  }
  .status-leave td {
    background-color: #d6f0ff;
  }
  .badge-danger {
    background-color: #f44336;
  }
  .badge-info {
    background-color: #2196f3;
  }
  th, td {
    text-align: left;
  }
  .status-absent td,
  .status-leave td {
    background-color: white;
  }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Absent Attendance Report</h3>
          <h5 class="mt-3">Absent/Leave Employees (Single Day)</h5>
          <form action="{{ route('attendance-absent-report-data') }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label for="report_date" class="form-label">Date</label>
                <input
                  type="date"
                  id="report_date"
                  name="report_date"
                  class="form-control @error('report_date') is-invalid @enderror"
                  value="{{ old('report_date', $report_date ?? Carbon::today()->toDateString()) }}"
                  required
                >
                @error('report_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label for="loca_code" class="form-label">Location</label>
                <select
                  id="loca_code"
                  name="loca_code"
                  class="form-control @error('loca_code') is-invalid @enderror"
                  required
                >
                  <option value="1" {{ old('loca_code', $loca_code ?? '1') == '1' ? 'selected' : '' }}>AFMDC (College)</option>
                  <option value="2" {{ old('loca_code', $loca_code ?? '1') == '2' ? 'selected' : '' }}>AFH (Hospital)</option>
                </select>
                @error('loca_code')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label for="dept_code" class="form-label">Department (Optional)</label>
                <select
                  id="dept_code"
                  name="dept_code"
                  class="form-control @error('dept_code') is-invalid @enderror"
                >
                  <option value="">All Departments</option>
                  @foreach (($departments ?? collect()) as $department)
                    <option value="{{ $department->dept_code }}" {{ old('dept_code', $dept_code ?? '') == $department->dept_code ? 'selected' : '' }}>
                      {{ $department->dept_desc }}
                    </option>
                  @endforeach
                </select>
                @error('dept_code')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Get Absent Report</button>
              </div>
            </div>
          </form>

          @if(isset($absent_rows))
            <div class="mb-4" style="padding: 10px; border-radius: 10px; background-color: #cedaff;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="mb-0">
                  {{ Carbon::parse($report_date)->format('j M Y') }}
                </h4>
                <div class="d-flex align-items-center gap-2">
                  <small class="text-muted">
                    Monthly stats: {{ Carbon::parse($stats_start)->format('j M Y') }} to {{ Carbon::parse($stats_end)->format('j M Y') }}
                  </small>
                  <form action="{{ route('attendance-absent-report-download') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="report_date" value="{{ $report_date }}">
                    <input type="hidden" name="loca_code" value="{{ $loca_code ?? '1' }}">
                    <input type="hidden" name="dept_code" value="{{ $dept_code ?? '' }}">
                    <button type="submit" class="btn btn-primary btn-sm">
                      <i class="fas fa-download"></i> Download PDF
                    </button>
                  </form>
                </div>
              </div>

              @if(!empty($is_non_working_day))
                <div class="alert alert-info mb-3">Selected date is a Sunday or holiday. Showing employees on leave only.</div>
              @endif

              <table class="table mt-2 mb-4">
                <thead>
                  <tr>
                    <th rowspan="2">Sr#</th>
                    <th rowspan="2">Code</th>
                    <th rowspan="2">Name</th>
                    <th rowspan="2">Designation</th>
                    <th rowspan="2">Department</th>
                    <th rowspan="2">Status</th>
                    <th colspan="4" style="text-align: center; border: none;">Monthly ({{ Carbon::parse($report_date)->format('F') }})</th>
                  </tr>
                  <tr>
                    <th>Casual</th>
                    <th>Medical</th>
                    <th>Annual</th>
                    <th>OD</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($absent_rows as $row)
                    <tr class="{{ $row['status'] === 'Leave' ? 'status-leave' : 'status-absent' }}">
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $row['emp_code'] }}</td>
                      <td>{{ $row['name'] }}</td>
                      <td>{{ $row['designation'] }}</td>
                      <td>{{ $row['department'] }}</td>
                      <td>
                        @if($row['status'] === 'Leave')
                          <span class="badge badge-info">Leave</span>
                        @else
                          <span class="badge badge-danger">Absent</span>
                        @endif
                      </td>
                      <td>{{ rtrim(rtrim(number_format($row['casual'], 1), '0'), '.') }}</td>
                      <td>{{ rtrim(rtrim(number_format($row['medical'], 1), '0'), '.') }}</td>
                      <td>{{ rtrim(rtrim(number_format($row['annual'], 1), '0'), '.') }}</td>
                      <td>{{ rtrim(rtrim(number_format($row['od'], 1), '0'), '.') }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center">No absent/leave records found for this date.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
