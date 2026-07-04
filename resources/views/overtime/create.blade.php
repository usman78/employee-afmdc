@extends('layouts.app')

@php
  use Carbon\Carbon;
@endphp

@push('styles')
  .overtime-summary {
    border: 1px solid #d7e6f8;
    background: #f7fbff;
    padding: 16px;
    border-radius: 6px;
  }
  .overtime-summary strong { color: #2196f3; }
  .table { border: 1px solid #ccc; }
  .table>:not(caption)>*>* { padding: .5rem .7rem; vertical-align: middle; }
  .overtime-create-table textarea { min-width: 180px; }
  .overtime-create-table { font-size: 13px; }
  .amount-display {
    font-weight: bold;
    color: #28a745;
    font-size: 14px;
    margin-top: 5px;
  }
  .amount-display {
    font-weight: bold;
    color: #28a745;
    font-size: 14px;
    margin-top: 5px;
  }
@endpush

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="mb-0">Overtime Application</h3>
            <div class="d-flex gap-2 flex-wrap">
              @if(Auth::user()->isBoss())
                <a href="{{ route('overtime.hod-index') }}" class="btn btn-outline-primary btn-sm">
                  <i class="fa-solid fa-list-check"></i> HOD Approvals
                </a>
              @endif
              @if(Auth::user()->isHR())
                <a href="{{ route('overtime.report') }}" class="btn btn-outline-success btn-sm">
                  <i class="fa-solid fa-file-lines"></i> HR Report
                </a>
              @endif
              @if(Auth::user()->isAccountsOfficer())
                <a href="{{ route('overtime.finance-report') }}" class="btn btn-outline-secondary btn-sm">
                  <i class="fa-solid fa-coins"></i> Finance Report
                </a>
              @endif
            </div>
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

          <form action="{{ route('overtime.create', $employee->emp_code) }}" method="GET" class="d-flex align-items-end flex-wrap gap-2 mt-4 mb-4">
            <div>
              <label for="month" class="form-label">Month</label>
              <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <button type="submit" class="btn btn-primary">View Month</button>
          </form>

          <div class="overtime-summary mb-4">
            <div class="row gy-3">
              <div class="col-md-4">
                <div>Employee</div>
                <strong>{{ capitalizeWords($employee->name) }} ({{ $employee->emp_code }})</strong>
              </div>
              <div class="col-md-4">
                <div>Designation / Department</div>
                <strong>{{ $employee->designation->desg_short ?? '-' }} / {{ $employee->department->dept_desc ?? '-' }}</strong>
              </div>
              <div class="col-md-4">
                <div>Application Month</div>
                <strong>{{ Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</strong>
              </div>
              <div class="col-md-4">
                <div>Eligible Days</div>
                <strong>{{ $summary['eligible_count'] }}</strong>
              </div>
              <div class="col-md-4">
                <div>Eligible Minutes</div>
                <strong>{{ $summary['total_eligible_minutes'] }} min</strong>
              </div>
              <div class="col-md-4">
                <div>Estimated Amount</div>
                <strong>PKR {{ number_format($summary['total_eligible_amount'], 2) }}</strong>
              </div>
            </div>
          </div>

          <div class="alert {{ $summary['eligible_count'] > 0 ? 'alert-primary' : 'alert-danger' }}">
            {{ $summary['message'] }}
          </div>

          <div class="table-responsive mb-4">
            <table class="table overtime-create-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Day</th>
                  <th>In</th>
                  <th>Out</th>
                  <th>Shift End</th>
                  <th>OT Minutes</th>
                  <th>Amount</th>
                  <th>Claim</th>
                </tr>
              </thead>
              <tbody>
                @forelse($eligibleRows as $row)
                  <tr>
                    <td>{{ Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td>{{ $row['day'] }}</td>
                    <td>{{ $row['time_in'] ? Carbon::parse($row['time_in'])->format('H:i') : '-' }}</td>
                    <td>{{ $row['time_out'] ? Carbon::parse($row['time_out'])->format('H:i') : '-' }}</td>
                    <td>{{ $row['shift_end'] ? Carbon::parse($row['shift_end'])->format('H:i') : '-' }}</td>
                    <td id="otMinutes{{ $loop->index }}">{{ $row['overtime_minutes'] }}</td>
                    <td id="otAmount{{ $loop->index }}">PKR {{ number_format($row['amount'], 0) }}</td>
                    <td id="otMinutes{{ $loop->index }}">{{ $row['overtime_minutes'] }}</td>
                    <td id="otAmount{{ $loop->index }}">PKR {{ number_format($row['amount'], 0) }}</td>
                    <td>
                      <form action="{{ route('overtime.store', $employee->emp_code) }}" method="POST">
                        @csrf
                        <input type="hidden" name="overtime_date" value="{{ $row['date'] }}">
                        <div class="mb-2">
                          <input 
                            type="number" 
                            name="overtime_minutes" 
                            class="form-control form-control-sm overtime-minutes-input" 
                            id="minutes{{ $loop->index }}"
                            min="60" 
                            max="{{ $row['overtime_minutes'] }}" 
                            value="{{ $row['overtime_minutes'] }}"
                            data-row-index="{{ $loop->index }}"
                            data-hourly-rate="{{ $row['hourly_rate'] }}"
                            required
                          >
                          <small class="form-text text-muted d-block mt-1">
                            Max: {{ $row['overtime_minutes'] }} min
                          </small>
                          <div class="amount-display" id="amountDisplay{{ $loop->index }}">
                            Amount: PKR {{ number_format($row['amount'], 0) }}
                          </div>
                        </div>
                        <div class="mb-2">
                          <input 
                            type="number" 
                            name="overtime_minutes" 
                            class="form-control form-control-sm overtime-minutes-input" 
                            id="minutes{{ $loop->index }}"
                            min="60" 
                            max="{{ $row['overtime_minutes'] }}" 
                            value="{{ $row['overtime_minutes'] }}"
                            data-row-index="{{ $loop->index }}"
                            data-hourly-rate="{{ $row['hourly_rate'] }}"
                            required
                          >
                          <small class="form-text text-muted d-block mt-1">
                            Max: {{ $row['overtime_minutes'] }} min
                          </small>
                          <div class="amount-display" id="amountDisplay{{ $loop->index }}">
                            Amount: PKR {{ number_format($row['amount'], 0) }}
                          </div>
                        </div>
                        <textarea name="remarks" class="form-control form-control-sm mb-2" rows="2" placeholder="Remarks" {{ $summary['gross_salary'] ? '' : 'disabled' }} required></textarea>
                        <button type="submit" class="btn btn-sm btn-success" {{ $summary['gross_salary'] ? '' : 'disabled' }}>
                          <i class="fa-solid fa-paper-plane"></i> Submit
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted">No eligible overtime days found for this month.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <h3 class="mt-5">Applications for {{ Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</h3>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Requested OT</th>
                  <th>Claimed Amount</th>
                  <th>Status</th>
                  <th>Approved OT Minutes</th>
                  <th>Approved Amount</th>
                  <th>Approved OT Minutes</th>
                  <th>Approved Amount</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications as $application)
                  <tr>
                    <td>{{ Carbon::parse($application->overtime_date)->format('d M Y') }}</td>
                    <td>{{ $application->overtime_minutes }} min</td>
                    <td>PKR {{ number_format($application->calculated_amount, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
                    <td>
                      @if($application->status === 'approved' && $application->sanctioned_minutes)
                        <span class="badge bg-success">{{ $application->sanctioned_minutes }} min</span>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if($application->status === 'approved' && $application->sanctioned_amount)
                        <strong style="color: #28a745;">PKR {{ number_format($application->sanctioned_amount, 2) }}</strong>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if($application->status === 'approved' && $application->sanctioned_minutes)
                        <span class="badge bg-success">{{ $application->sanctioned_minutes }} min</span>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if($application->status === 'approved' && $application->sanctioned_amount)
                        <strong style="color: #28a745;">PKR {{ number_format($application->sanctioned_amount, 2) }}</strong>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>{{ $application->remarks }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted">No overtime applications submitted for this month.</td>
                    <td colspan="7" class="text-center text-muted">No overtime applications submitted for this month.</td>
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

@push('scripts')

  document.querySelectorAll('.overtime-minutes-input').forEach(input => {
    input.addEventListener('input', function() {
      const rowIndex = this.dataset.rowIndex;
      const hourlyRate = parseFloat(this.dataset.hourlyRate);
      const minutes = parseInt(this.value) || 0;
      
      if (minutes >= 60) {
        // Calculate amount based on new minutes
        const amount = calculateOTAmount(hourlyRate, minutes);
        
        // Update the display
        document.getElementById(`otMinutes${rowIndex}`).textContent = minutes;
        document.getElementById(`otAmount${rowIndex}`).textContent = `PKR ${Math.round(amount).toLocaleString()}`;
        document.getElementById(`amountDisplay${rowIndex}`).textContent = `Amount: PKR ${Math.round(amount).toLocaleString()}`;
      }
    });
  });

  function calculateOTAmount(hourlyRate, minutes) {
    let hours = Math.floor(minutes / 60);
    let remainingMinutes = minutes % 60;
    
    if (remainingMinutes <= 25) {
      remainingMinutes = 0;
    } else if (remainingMinutes <= 45) {
      remainingMinutes = 30;
    } else {
      hours++;
      remainingMinutes = 0;
    }
    
    const payableMinutes = (hours * 60) + remainingMinutes;
    return (hourlyRate * payableMinutes) / 60;
  }

@endpush
@endsection
