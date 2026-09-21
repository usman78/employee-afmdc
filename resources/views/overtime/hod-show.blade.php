@extends('layouts.app')

@php
  use Carbon\Carbon;
@endphp

@push('styles')
  .table { border: 1px solid #ccc; }
  .table>:not(caption)>*>* { padding: .55rem .7rem; }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Overtime HOD Approval</h3>

          @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-warning mt-3">{{ session('error') }}</div>
          @endif

          <table class="table mt-4">
            <tbody>
              <tr>
                <th>Employee</th>
                <td>{{ capitalizeWords($application->employee->name ?? '') }} ({{ $application->emp_code }})</td>
              </tr>
              <tr>
                <th>Designation</th>
                <td>{{ $application->employee->designation->desg_short ?? '-' }}</td>
              </tr>
              <tr>
                <th>Department</th>
                <td>{{ $application->employee->department->dept_desc ?? '-' }}</td>
              </tr>
              <tr>
                <th>Date</th>
                <td>{{ Carbon::parse($application->overtime_date)->format('d M Y') }}</td>
              </tr>
              <tr>
                <th>OT Minutes</th>
                <td>{{ $application->overtime_minutes }}</td>
              </tr>
              <tr>
                <th>Claimed Amount</th>
                <td>PKR {{ number_format($application->calculated_amount, 2) }}</td>
              </tr>
              <tr>
                <th>Status</th>
                <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
              </tr>
              <tr>
                <th>Remarks</th>
                <td>{{ $application->remarks }}</td>
              </tr>
            </tbody>
          </table>

          @if($application->status === \App\Models\OvertimeApplication::STATUS_PENDING)
            <form action="{{ route('overtime.hod-decision', $application->id) }}" method="POST" class="mt-4">
              @csrf
              <div class="mb-3">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea id="remarks" name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" name="decision" value="approve" class="btn btn-success">
                  <i class="fa-solid fa-check"></i> Approve
                </button>
                <button type="submit" name="decision" value="reject" class="btn btn-danger">
                  <i class="fa-solid fa-xmark"></i> Reject
                </button>
              </div>
            </form>
          @else
            <div class="alert alert-info mt-4">This application has already been processed by HOD.</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
