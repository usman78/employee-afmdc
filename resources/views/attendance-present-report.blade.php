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
  th, td {
    text-align: left;
  }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Present Attendance Report (AFMDC)</h3>
          <h5 class="mt-3">Present Employees (Single Day)</h5>
          <form action="{{ route('attendance-present-report-data') }}" method="POST" class="mb-4">
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
                <button type="submit" class="btn btn-primary">Get Present Report</button>
              </div>
            </div>
          </form>

          @if(isset($present_rows))
            <div class="mb-4" style="padding: 10px; border-radius: 10px; background-color: #cedaff;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">
                  {{ Carbon::parse($report_date)->format('j M Y') }}
                </h6>
              </div>
              <table class="table mt-2 mb-4">
                <thead>
                  <tr>
                    <th>Sr#</th>
                    <th>Emp Code</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($present_rows as $row)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $row['emp_code'] }}</td>
                      <td>{{ $row['name'] }}</td>
                      <td>{{ $row['designation'] }}</td>
                      <td>{{ $row['department'] }}</td>
                      <td>{{ $row['time_in'] }}</td>
                      <td>{{ $row['time_out'] }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center">No present records found for this date.</td>
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
