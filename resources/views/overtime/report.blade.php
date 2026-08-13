@extends('layouts.app')

@php
  use App\Models\OvertimeApplication;
  use Carbon\Carbon;
@endphp

@push('styles')
  .table { border: 1px solid #ccc; }
  .table>:not(caption)>*>* { padding: .5rem .6rem; vertical-align: middle; }
  .overtime-report-table { font-size: 13px; }
  .overtime-report-table textarea { min-width: 130px; }
  .amount-display {
    font-weight: bold;
    color: #28a745;
    font-size: 12px;
    margin-top: 5px;
  }
  .amount-display {
    font-weight: bold;
    color: #28a745;
    font-size: 12px;
    margin-top: 5px;
  }
@endpush

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Overtime Report</h3>

          @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-warning mt-3">{{ session('error') }}</div>
          @endif

          <form action="{{ route('overtime.report') }}" method="GET" class="d-flex align-items-end gap-2 flex-wrap mt-4 mb-4">
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
                  <a class="dropdown-item" href="{{ route('overtime.approved-download', ['month' => $month, 'status' => 'all']) }}" target="_blank">All Approved</a>
                  <a class="dropdown-item" href="{{ route('overtime.approved-download', ['month' => $month, 'status' => OvertimeApplication::STATUS_HR_APPROVED]) }}" target="_blank">All HR Approved</a>
                  {{-- <a class="dropdown-item" href="#" data-toggle="modal" data-target="#filterByNameModal">Download By Name</a>
                  <a class="dropdown-item" href="#" data-toggle="modal" data-target="#filterByDateModal">Download by Finance Approval Date</a> --}}
              </div>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table overtime-report-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Date</th>
                  <th>OT Minutes</th>
                  <th>Claimed</th>
                  <th>Status</th>
                  <th>Remarks</th>
                  <th>HOD Remarks</th>
                  <th>HR Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($applications as $application)
                  @php
                    $canHrAct = $application->status === OvertimeApplication::STATUS_HOD_APPROVED;
                    $displayAmount = $application->sanctioned_amount ?? $application->calculated_amount;
                  @endphp
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ capitalizeWords($application->employee->name ?? '') }}</td>
                    <td>{{ $application->emp_code }}</td>
                    <td>{{ Carbon::parse($application->overtime_date)->format('d M Y') }}</td>
                    <td id="otMinutes{{ $loop->index }}">{{ formatMinutes($application->overtime_minutes) }}</td>
                    <td id="otAmount{{ $loop->index }}">PKR {{ number_format($displayAmount, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $application->status }}</span></td>
                    <td>{{ $application->remarks ?: '-' }}</td>
                    <td>{{ $application->hod_remarks ?: '-' }}</td>
                    <td>
                      @if($canHrAct)
                        <form action="{{ route('overtime.hr-decision', $application->id) }}" method="POST">
                          @csrf
                          <input
                            type="number"
                            name="sanctioned_minutes"
                            class="form-control form-control-sm mb-2 sanctioned-minutes-input"
                            id="minutes{{ $loop->index }}"
                            min="60"
                            max="{{ (int) $application->overtime_minutes }}"
                            value="{{ old('sanctioned_minutes', (int) $application->overtime_minutes) }}"
                            data-row-index="{{ $loop->index }}"
                            data-hourly-rate="{{ $application->hourly_rate }}"
                          >
                          <small class="form-text text-muted d-block">
                            Claimed: {{ $application->overtime_minutes }} min (PKR {{ number_format($application->calculated_amount, 2) }})
                          </small>
                          <div class="amount-display" id="amountDisplay{{ $loop->index }}">
                            Amount: PKR {{ number_format($application->calculated_amount, 2) }}
                          </div>
                          <textarea name="remarks" class="form-control form-control-sm mb-2" rows="2" placeholder="Remarks">{{ old('remarks') }}</textarea>
                          <div class="d-flex gap-1">
                            <button type="submit" name="decision" value="approve" class="btn btn-sm btn-success">Approve</button>
                            <button type="submit" name="decision" value="reject" class="btn btn-sm btn-danger">Reject</button>
                          </div>
                        </form>
                      @else
                        <small class="text-muted">{{ $application->hr_remarks ?: $application->hod_remarks ?: '-' }}</small>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center text-muted">No overtime applications found for this month.</td>
                  </tr>
                @endforelse

                @if($applications->count())
                  @php
                    $grandTotal = $applications->reduce(function ($carry, $application) {
                      // Adding only those amounts that have the HR approved status to the grand total
                      if ($application->status !== OvertimeApplication::STATUS_HR_APPROVED) {
                        return $carry;
                      }
                      $amount = number_format($application->sanctioned_amount ?? $application->calculated_amount, 2, '.', '');
                      return bcadd($carry, $amount, 2);
                    }, '0.00');
                  @endphp
                  <tr class="table-active">
                    <td colspan="5" class="text-end"><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>PKR {{ number_format($grandTotal, 2) }}</strong></td>
                    <td colspan="4"></td>
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

@push('scripts')

  document.querySelectorAll('.sanctioned-minutes-input').forEach(input => {
    input.addEventListener('input', function() {
      const rowIndex = this.dataset.rowIndex;
      const hourlyRate = parseFloat(this.dataset.hourlyRate);
      const minutes = parseInt(this.value) || 0;
      
      if (minutes >= 60) {
        // Calculate amount based on new minutes
        const amount = calculateOTAmount(hourlyRate, minutes);
        
        // Update the display
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
