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
            <div class="dropdown">
              <button class="btn btn-primary dropdown-toggle" type="button"
                  id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                  aria-expanded="false">
                  Download Report
              </button>
              <div class="dropdown-menu animated--fade-in"
                  aria-labelledby="dropdownMenuButton">
                  <a class="dropdown-item" href="{{ route('overtime.approved-download', ['month' => $month, 'status' => OvertimeApplication::STATUS_APPROVED]) }}" target="_blank">All Approved</a>
              </div>
            </div>
          </form>
          @php
            $serialNumber = 1;
          @endphp
          <div class="table-responsive">
            <table class="table overtime-finance-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Date</th>
                  <th>Sanctioned Minutes</th>
                  <th>Salary</th>
                  <th>Hourly Rate</th>
                  <th>Sanctioned Amount</th>
                  <th>Status</th>
                  <th>HR Remarks</th>
                  <th>Finance Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications->groupBy('emp_code') as $employeeApplications)
                  @php
                    $employee = $employeeApplications->first();
                  @endphp
                  <tr class="table-secondary">
                    <td></td>
                    <td><strong>{{ capitalizeWords($employee->employee->name ?? '') }}</strong></td>
                    <td><strong>{{ $employee->emp_code }}</strong></td>
                    <td colspan="8"><strong>{{ $employeeApplications->count() }} overtime application(s)</strong></td>
                  </tr>
                  @foreach($employeeApplications as $application)
                    @php
                      $canAccountsAct = $application->status === OvertimeApplication::STATUS_HR_APPROVED;
                    @endphp
                    <tr>
                      <td>{{ $serialNumber++ }}</td>
                      <td></td>
                      <td></td>
                      <td>{{ Carbon::parse($application->overtime_date)->format('d M Y') }}</td>
                      {{-- <td>{{ formatMinutes($application->overtime_minutes) }}</td> --}}
                      <td>{{ formatMinutes(payableMinutes($application->sanctioned_minutes)) ?? formatMinutes(payableMinutes($application->overtime_minutes)) }}</td>
                      <td>{{ number_format($application->gross_salary ?? 0, 0) }}</td>
                      <td>{{ $application->hourly_rate ?? '-' }}</td>
                      <td>
                        @if($application->sanctioned_amount)
                          PKR {{ number_format($application->sanctioned_amount, 2) }}
                        @else
                          -
                        @endif
                      </td>
                      <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
                      <td>{{ $application->hr_remarks ?: '-' }}</td>
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
                  @endforeach
                @empty
                  <tr>
                    <td colspan="11" class="text-center text-muted">No overtime applications found for this month.</td>
                  </tr>
                @endforelse

                @if($applications->count())
                  @php
                    $grandTotal = $applications->reduce(function ($carry, $application) {
                      // Adding only those amounts that have the HR approved status to the grand total
                      // if ($application->status !== OvertimeApplication::STATUS_HR_APPROVED) {
                      //   return $carry;
                      // }
                      $amount = number_format($application->sanctioned_amount ?? 0, 2, '.', '');
                      return bcadd($carry, $amount, 2);
                    }, '0.00');
                  @endphp
                  <tr class="table-active">
                    <td colspan="7" class="text-end"><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>PKR {{ number_format($grandTotal, 2) }}</strong></td>
                    <td colspan="3"></td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
