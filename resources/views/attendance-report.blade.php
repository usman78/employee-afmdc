@extends('layouts.app')

@php
  use Carbon\Carbon;
  $today = Carbon::today()->toDateString();
  $isTimeIn = false;
@endphp

@push('styles')
  .stats .stats-item {
    padding: 0;
  }
  .stats .stats-item span {
    margin-bottom: 10px;
    padding-bottom: 0px;
  }
  .portfolio .stats .stats-item.text-center.w-100.h-100 {
    background: gainsboro !important;
    border-radius: 10px !important;
    box-shadow: 6px 7px 5px gray !important;
  }
  .badge-success {
    background-color: #2196f3;
  }
  .badge-warning {
    background-color: #ff9800;
  }
  .badge-info {
    background-color: #4caf50;
  }
  .badge-danger {
    background-color: #f44336;
  }
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
  .late-row td {
    background-color: #ffb6b6;
  }
  @media (max-width: 768px) {
    .portfolio-details .portfolio-info {
      padding: 0 15px;
    }
  }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Attendance Report</h3>
          <form action="{{ route('attendance-report-data') }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label for="emp_code" class="form-label">Employee Code</label>
                <input
                  type="text"
                  id="emp_code"
                  name="emp_code"
                  class="form-control @error('emp_code') is-invalid @enderror"
                  value="{{ old('emp_code', $searched_emp_code ?? '') }}"
                  placeholder="Enter employee code"
                  required
                >
                @error('emp_code')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input
                  type="date"
                  id="start_date"
                  name="start_date"
                  class="form-control @error('start_date') is-invalid @enderror"
                  value="{{ old('start_date', $searched_start_date ?? \Carbon\Carbon::now()->startOfMonth()->toDateString()) }}"
                  required
                >
                @error('start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input
                  type="date"
                  id="end_date"
                  name="end_date"
                  class="form-control @error('end_date') is-invalid @enderror"
                  value="{{ old('end_date', $searched_end_date ?? \Carbon\Carbon::today()->toDateString()) }}"
                  required
                >
                @error('end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Get Report</button>
              </div>
            </div>
          </form>

          @if(session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
          @endif
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          @if(isset($attendance))
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h5 class="mb-0">{{ $emp_name }}</h5>
                <small class="text-muted">
                  {{ \Carbon\Carbon::parse($report_start_date ?? ($searched_start_date ?? \Carbon\Carbon::now()->startOfMonth()->toDateString()))->format('j M Y') }}
                  to
                  {{ \Carbon\Carbon::parse($report_end_date ?? ($searched_end_date ?? \Carbon\Carbon::today()->toDateString()))->format('j M Y') }}
                </small>
              </div>
              <div class="d-flex gap-2">
                <form action="{{ route('attendance-report-download', ['emp_code' => $searched_emp_code ?? old('emp_code')]) }}" method="POST">
                  @csrf
                  <input type="hidden" name="start_date" value="{{ $report_start_date ?? ($searched_start_date ?? \Carbon\Carbon::now()->startOfMonth()->toDateString()) }}">
                  <input type="hidden" name="end_date" value="{{ $report_end_date ?? ($searched_end_date ?? \Carbon\Carbon::today()->toDateString()) }}">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download Report as PDF
                  </button>
                </form>
                <form id="email-report-form" action="{{ route('attendance-report-email', ['emp_code' => $searched_emp_code ?? old('emp_code')]) }}" method="POST">
                  @csrf
                  <input type="hidden" name="start_date" value="{{ $report_start_date ?? ($searched_start_date ?? \Carbon\Carbon::now()->startOfMonth()->toDateString()) }}">
                  <input type="hidden" name="end_date" value="{{ $report_end_date ?? ($searched_end_date ?? \Carbon\Carbon::today()->toDateString()) }}">
                  <button
                    type="button"
                    id="email-report-btn"
                    class="btn btn-success"
                    data-hod-email="{{ $hod_email ?? '' }}"
                  >
                    <i class="fas fa-envelope"></i> Email Report to HOD
                  </button>
                </form>
              </div>
            </div>

            <div class="row gy-4 stats">
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="0" class="purecounter late-days"></span>
                  <p>Late Coming Days</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="0" class="purecounter late-mins"></span>
                  <p>Late Coming Mins</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="0" class="purecounter early-mins"></span>
                  <p>Early Off Mins</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="0" class="purecounter total-mins"></span>
                  <p>Total Mins Effect</p>
                </div>
              </div>
            </div>

            <table class="table mt-3 mb-5">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Time-In/Out</th>
                  <th>Late Mins</th>
                  <th>Early Mins</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($attendance as $record)
                  <tr class="{{ ($record['late_minutes'] ?? 0) >= 10 ? 'late-row' : '' }}">
                    <td>{{ Carbon::parse($record['at_date'])->format('D, j M') }}</td>
                    <td>
                      @if ($record['is_sunday'] || $record['is_holiday'])
                        <span class="badge badge-info">
                          {{ $record['is_holiday'] ? 'Holiday' : 'Sunday' }}
                        </span>
                      @else
                        @if (!empty($record['time_logs']))
                          @foreach ($record['time_logs'] as $log)
                            @if ($log['timein'] && $log['timeout'])
                              {{ Carbon::parse($log['timein'])->format('H:i') }} / {{ Carbon::parse($log['timeout'])->format('H:i') }}<br>
                            @elseif ($log['timein'])
                              {{ Carbon::parse($log['timein'])->format('H:i') }} / --:--<br>
                              @php $isTimeIn = true; @endphp
                            @endif
                          @endforeach
                        @else
                          <span class="badge badge-danger">Not timed in</span>
                        @endif
                      @endif
                    </td>
                    <td>
                      @if (!$record['is_sunday'] && !$record['is_holiday'])
                        @if (($record['late_minutes'] ?? 0) >= 10)
                          {{ intval($record['late_minutes'] ?? 0) }} mins
                        @else
                          -
                        @endif
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      @if (!$record['is_sunday'] && !$record['is_holiday'])
                        @if (($record['early_minutes'] ?? 0) > 0)
                          {{ round($record['early_minutes']) }} mins
                        @else
                          -
                        @endif
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      @if ($record['is_sunday'] || $record['is_holiday'])
                        <span class="badge badge-info">
                          {{ $record['is_holiday'] ? 'Holiday' : 'Sunday' }}
                        </span>
                      @elseif ($record['leave_type'])
                        <span class="badge badge-success">{{ $record['leave_type'] }}</span>
                      @elseif(empty($record['time_logs']))
                        <span class="badge badge-danger">Absent</span>
                      @else
                        <span class="badge badge-success">Present</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  function sumLateAndEarlyMinutes() {
    let totalLate = 0;
    let totalEarly = 0;
    let totalLateDays = 0;

    document.querySelectorAll("table tbody tr").forEach(row => {
      let lateCell = row.cells[2];
      let earlyCell = row.cells[3];

      if (lateCell) {
        let lateText = lateCell.innerText.trim();
        let lateValue = parseInt(lateText);
        if (!isNaN(lateValue)) {
          totalLate += lateValue;
        }
        if (lateValue >= 10) {
          totalLateDays += 1;
        }
      }

      if (earlyCell) {
        let earlyText = earlyCell.innerText.trim();
        let earlyValue = parseInt(earlyText);
        if (!isNaN(earlyValue)) {
          totalEarly += earlyValue;
        }
      }
    });

    return {
      lateMinutes: totalLate,
      earlyMinutes: totalEarly,
      totalMins: totalLate + totalEarly,
      lateDays: totalLateDays
    };
  }

  if (document.querySelector('table tbody tr')) {
    const totals = sumLateAndEarlyMinutes();
    const lateEl = document.querySelector('.late-mins');
    const lateDaysEl = document.querySelector('.late-days');
    const earlyEl = document.querySelector('.early-mins');
    const totalEl = document.querySelector('.total-mins');

    if (lateEl) lateEl.textContent = totals.lateMinutes;
    if (lateDaysEl) lateDaysEl.textContent = totals.lateDays;
    if (earlyEl) earlyEl.textContent = totals.earlyMinutes;
    if (totalEl) totalEl.textContent = totals.totalMins;
  }

  const emailBtn = document.getElementById('email-report-btn');
  const emailForm = document.getElementById('email-report-form');
  if (emailBtn && emailForm) {
    emailBtn.addEventListener('click', async function () {
      const hodEmail = (this.dataset.hodEmail || '').trim();

      if (!hodEmail) {
        await Swal.fire({
          title: 'HOD Email Not Found',
          text: 'email of the HOD is not in the records.',
          icon: 'warning'
        });
        return;
      }

      const result = await Swal.fire({
        title: 'Confirm Email',
        html: `HOD email found:<br><strong>${hodEmail}</strong><br><br>Do you want to send the report?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Send',
        cancelButtonText: 'Cancel'
      });

      if (result.isConfirmed) {
        emailForm.submit();
      }
    });
  }
@endpush
