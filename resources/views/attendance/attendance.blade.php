@extends('layouts.app')

@php
  use Carbon\Carbon;
  $today = Carbon::today()->toDateString();
  $isTimeIn = false;
//   dd($attendance);
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
  .leave-link {
    color: #2196f3;
    font-size: 14px;
    margin-left: 15px;
  }
  .leave-link:hover {
    color: rgb(3 108 191);
  }
  td {
    font-size: 14px;
    vertical-align: middle;
  }
  .late-row td {
    background-color: #ffb6b6;
  }
  .employee-meta {
    margin: 8px 0 16px;
    font-size: 14px;
    color: #555;
  }
  .attendance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 10px;
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
          <h3>Attendance Information</h3>
          <div class="attendance-header">
            <p class="employee-meta mb-0">
              <strong>Employee:</strong> {{ $emp_name ?? 'Unknown Employee' }}
              <span class="mx-2">|</span>
              <strong>Code:</strong> {{ $emp_code ?? 'N/A' }}
              <span class="mx-2">|</span>
              <strong>Range:</strong>
              {{ Carbon::parse($report_start_date ?? Carbon::now()->startOfMonth()->toDateString())->format('j M Y') }}
              to
              {{ Carbon::parse($report_end_date ?? Carbon::today()->toDateString())->format('j M Y') }}
            </p>
            <form action="{{ route('attendance-report-download', ['emp_code' => $emp_code ?? '']) }}" method="POST">
              @csrf
              <input type="hidden" name="start_date" value="{{ $report_start_date ?? Carbon::now()->startOfMonth()->toDateString() }}">
              <input type="hidden" name="end_date" value="{{ $report_end_date ?? Carbon::today()->toDateString() }}">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-download"></i> Download Report as PDF
              </button>
            </form>
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
          <ul>
          @if(session('success'))
            <li class="mt-5">
              <span class="alert alert-success">{{session('success')}}</span>
            </li>
          @endif  
          @if(session('error'))
            <li class="mt-5">
              <span class="alert alert-warning">{{session('error')}}</span>
            </li>
          @endif
          </ul>
          <table class="table mt-2 mb-5">
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
                <tr class="{{ $record['late_coming'] ? 'late-row' : '' }}">
                  {{-- Date --}}
                  <td>{{ dateFormat($record['dated']) }}</td>
                  {{-- Time In / Out --}}
                  <td>{{ timeFormatFromString($record['in_date']) }} - {{ timeFormatFromString($record['out_date']) }}</td>
                  {{-- <td>
                      @if ($record['is_sunday'] || $record['is_holiday'])
                          <span class="badge badge-info">
                              {{ $record['is_holiday'] ? 'Holiday' : 'Sunday' }}
                          </span>
                      @else
                          @if (!empty($record['time_logs']))
                              @foreach ($record['time_logs'] as $log)
                                  @if ($log['timein'] && $log['timeout'])
                                      {{ Carbon::parse($log['timein'])->format('H:i') }}
                                      /
                                      {{ Carbon::parse($log['timeout'])->format('H:i') }}
                                      <br>
                                  @elseif ($log['timein'])
                                      {{ Carbon::parse($log['timein'])->format('H:i') }} / --:--
                                      <br>
                                      @php
                                        $isTimeIn = true;
                                      @endphp
                                  @endif
                              @endforeach
                          @else
                              <span class="badge badge-danger">Not timed in</span>
                          @endif
                      @endif
                  </td> --}}
                  {{-- Late Minutes (DAY LEVEL) --}}
                  <td>
                    {{ $record['late_coming'] === 0 ? '—' : $record['late_coming'] . ' mins' }}
                  </td>
                  {{-- <td>
                      @if (
                          !$record['is_sunday']
                          && !$record['is_holiday']
                          // && $record['at_date'] !== $today
                      )
                          @if (($record['late_minutes'] ?? 0) >= 10)
                              {{ intval($record['late_minutes'] ?? 0) }} mins
                          @else
                              —
                          @endif
                      @else
                          —
                      @endif
                  </td> --}}
                  {{-- Early Minutes (DAY LEVEL) --}}
                  <td>
                    {{ $record['early_leaving'] ?? '0 mins'}}
                  </td>
                      {{-- @if (
                          !$record['is_sunday']
                          && !$record['is_holiday']
                          // && $record['at_date'] !== $today
                      )
                          @if (($record['early_minutes'] ?? 0) > 0)
                              {{ round($record['early_minutes']) }} mins
                          @else
                              —
                          @endif
                      @else
                          —
                      @endif
                  </td> --}}
                  {{-- Status --}}
                  <td>
                    {{-- @if ($record['is_sunday'] || $record['is_holiday'])
                      <span class="badge badge-info">
                        {{ $record['is_holiday'] ? 'Holiday' : 'Sunday' }}
                      </span>
                    @elseif ($record['leave_type'])
                      <span class="badge badge-success">{{ $record['leave_type'] }}</span>  
                    @elseif(empty($record['time_logs']))
                      <span class="badge badge-danger">Absent</span>
                    @else                          
                      <span class="badge badge-success">
                        Present
                      </span>
                    @endif --}}
                    @if ($record['dated'])
                      <span class="badge badge-success">Present</span>
                    @else
                      <span class="badge badge-danger">Absent</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
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

      // Select all table rows except header
      document.querySelectorAll("table tbody tr").forEach(row => {

          // Adjust column indexes if needed
          let lateCell = row.cells[2];   // Late Mins column
          let earlyCell = row.cells[3];  // Early Mins column

          if (lateCell) {
              let lateText = lateCell.innerText.trim();
              let lateValue = parseInt(lateText);
              if (!isNaN(lateValue)) {
                  totalLate += lateValue;
              }
              // Count late days (considering only rows where late minutes are 10 or more)
              if (lateValue >= 11) {
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
      {{-- Calculate total effect --}}
      let total = totalLate + totalEarly;

      return {
          lateMinutes: totalLate,
          earlyMinutes: totalEarly,
          totalMins: total,
          lateDays: totalLateDays
      };
  }
  const totals = sumLateAndEarlyMinutes();
  const lateEl = document.querySelector('.late-mins');
  const lateDaysEl = document.querySelector('.late-days');
  const earlyEl = document.querySelector('.early-mins');
  const totalEl = document.querySelector('.total-mins');

  if (lateEl) {
    lateEl.textContent = totals.lateMinutes;
  }
  if (lateDaysEl) {
    lateDaysEl.textContent = totals.lateDays;
  }
  if (earlyEl) {
    earlyEl.textContent = totals.earlyMinutes;
  }
  if (totalEl) {
    totalEl.textContent = totals.totalMins;
  }
@endpush
