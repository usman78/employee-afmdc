@extends('layouts.app')

@php
  use Carbon\Carbon;
  $today = Carbon::today()->toDateString();
  $isTimeIn = false;
  // dd($attendance);
@endphp

@push('styles')
  .stats .stats-item {
    padding: 0;
    background-color: gainsboro;
    border-radius: 10px;
    box-shadow: 1px 2px 4px 1px #a3a3a3;
  }
  .stats .stats-item span {
      margin-bottom: 10px;
      padding-bottom: 0px;
      font-size: 32px;
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
      padding: 15px 15px;
    }
  }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Attendance Discrepancy Report</h3>
          <div class="row">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Employee
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $emp_name ?? 'Unknown Employee' }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Employee Code</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $emp_code ?? 'N/A' }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-id-card fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Department</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dept->dept_desc ?? 'N/A' }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Designation</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $desg->desg_desc ?? 'N/A' }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-tag fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
          <div class="attendance-header mt-3">
            <form action="{{ route('att-discrepency') }}" method="POST" class="d-flex align-items-end flex-wrap gap-2">
              @csrf
              <div>
                <input type="hidden" name="emp_code" value="{{ $emp_code ?? '' }}">
                <label for="start_date" class="form-label mb-1">Start Date</label>
                <input
                  type="date"
                  id="start_date"
                  name="start_date"
                  class="form-control form-control-sm"
                  value="{{ $report_start_date ?? Carbon::now()->startOfMonth()->toDateString() }}"
                  required
                >
              </div>
              <div>
                <label for="end_date" class="form-label mb-1">End Date</label>
                <input
                  type="date"
                  id="end_date"
                  name="end_date"
                  class="form-control form-control-sm"
                  value="{{ $report_end_date ?? Carbon::today()->toDateString() }}"
                  required
                >
              </div>
              <button type="submit" class="btn btn-secondary btn-sm mt-4">
                Apply Range
              </button>
            </form>
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="downloadDropdownAttendance" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-download"></i> Download Report as PDF
              </button>
              <ul class="dropdown-menu" aria-labelledby="downloadDropdownAttendance">
                <li>
                  <form action="{{ route('att-discrepancy-report-download', ['emp_code' => $emp_code ?? '']) }}" method="POST" target="_blank" style="display: inline;">
                    <input type="hidden" name="start_date" value="{{ $report_start_date ?? Carbon::now()->startOfMonth()->toDateString() }}">
                    @csrf
                    <input type="hidden" name="end_date" value="{{ $report_end_date ?? Carbon::today()->toDateString() }}">
                    <input type="hidden" name="include_signatures" value="1">
                    <button type="submit" class="dropdown-item">
                      <i class="fas fa-pen"></i> With Signatures
                    </button>
                  </form>
                </li>
                <li>
                  <form action="{{ route('att-discrepancy-report-download', ['emp_code' => $emp_code ?? '']) }}" method="POST" target="_blank" style="display: inline;">
                    <input type="hidden" name="start_date" value="{{ $report_start_date ?? Carbon::now()->startOfMonth()->toDateString() }}">
                    @csrf
                    <input type="hidden" name="end_date" value="{{ $report_end_date ?? Carbon::today()->toDateString() }}">
                    <input type="hidden" name="include_signatures" value="0">
                    <button type="submit" class="dropdown-item">
                      <i class="fas fa-file"></i> Without Signatures
                    </button>
                  </form>
                </li>
              </ul>
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
            <div class="col-md-4">
              <div class="stats-item text-center w-100 h-100">
                <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="0" class="purecounter">{{ $total_leave_days_deducted ?? 0 }}</span>
                <p>Leave Days Deducted</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="stats-item text-center w-100 h-100">
                <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="0" class="purecounter present-days">{{ $total_present_days ?? 0 }}</span>
                <p>Total Present Days</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="stats-item text-center w-100 h-100">
                <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="0" class="purecounter absent-days">{{ $total_absent_days ?? 0 }}</span>
                <p>Total Absent Days</p>
              </div>
            </div>
          <ul>
          @if(session('success'))
            <ul>
                <li class="mt-5">
                <span class="alert alert-success">{{session('success')}}</span>
                </li>
            </ul>
          @endif  
          @if(session('error'))
            <ul>
                <li class="mt-5">
                <span class="alert alert-warning">{{session('error')}}</span>
                </li>
            </ul>
          @endif
          <div class="row">
            <div class="col">
                <div class="card mb-4 py-3 border-left-success">
                    <div class="card-body">
                        <strong>Report Range:</strong>
                        {{ Carbon::parse($report_start_date ?? Carbon::now()->startOfMonth()->toDateString())->format('j M Y') }}
                        to
                        {{ Carbon::parse($report_end_date ?? Carbon::today()->toDateString())->format('j M Y') }}
                    </div>
                </div>
            </div>
          </div>
          <table class="table mt-2 mb-5">
            <thead>
              <tr>
                <th>Date</th>
                <th>Duty Start/End</th>
                <th>Time-In/Out</th>
                <th>Late Mins</th>
                <th>Early Mins</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($attendance as $record)
                <tr class="{{ ($record['late_minutes'] ?? 0) >= 10 ? 'late-row' : '' }}">
                  {{-- Date --}}
                  <td>{{ Carbon::parse($record['at_date'])->format('D, j M') }}</td>
                    {{-- Duty Start/End --}}    
                    <td>
                        @if (!empty($record['duty_start']) && !empty($record['duty_end']))
                            {{ $record['duty_start'] }} / {{ $record['duty_end'] }}
                        @else
                            —
                        @endif
                    </td>                  
                  {{-- Time In / Out --}}
                  <td>
                      {{-- @if ($record['is_sunday'] || $record['is_holiday'] || ($record['is_weekly_rest'] ?? false))
                           <span class="badge badge-info">
                              {{ $record['is_holiday'] ? 'Holiday' : (($record['is_weekly_rest'] ?? false) ? 'Weekly Rest' : 'Sunday') }}
                           </span>
                      @else --}}
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
                      {{-- @endif --}}
                  </td>
                  {{-- Late Minutes (DAY LEVEL) --}}
                  <td>
                      @if (
                           !$record['is_sunday']
                           && !$record['is_holiday']
                           && !($record['is_weekly_rest'] ?? false)
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
                  </td>
                  {{-- Early Minutes (DAY LEVEL) --}}
                  <td>
                      @if (
                           !$record['is_sunday']
                           && !$record['is_holiday']
                           && !($record['is_weekly_rest'] ?? false)
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
                  </td>
                  {{-- Status --}}
                  <td>
                    @if ($record['is_sunday'] || $record['is_holiday'] || ($record['is_weekly_rest'] ?? false))
                      <span class="badge badge-info">
                        {{ $record['is_holiday'] ? 'Holiday' : (($record['is_weekly_rest'] ?? false) ? 'Weekly Rest' : 'Sunday') }}
                      </span>
                    @elseif ($record['leave_type'])
                      <span class="badge badge-success">{{ $record['leave_type'] }}</span>  
                    @elseif(empty($record['time_logs']))
                      <span class="badge badge-danger">Absent</span>
                    @else                          
                      <span class="badge badge-success">
                        Present
                      </span>
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
  function calculatePresentDays() {
      let presentDays = 0;
      document.querySelectorAll("table tbody tr").forEach(row => {
          const statusCell = row.cells[5]; // Status column
          if (statusCell) {
              const statusText = statusCell.innerText.trim();
              if (statusText !== "Absent") {
                  presentDays += 1;
              }
          }
      });
      return presentDays;
  }
  function calculateAbsentDays() {
      let absentDays = 0;
      document.querySelectorAll("table tbody tr").forEach(row => {
          const statusCell = row.cells[5]; // Status column
          if (statusCell) {
              const statusText = statusCell.innerText.trim();
              if (statusText === "Absent") {
                  absentDays += 1;
              }
          }
      });
      return absentDays;
  }

  function sumLateAndEarlyMinutes() {
      let totalLate = 0;
      let totalEarly = 0;
      let totalLateDays = 0;

      // Select all table rows except header
      document.querySelectorAll("table tbody tr").forEach(row => {

          // Adjust column indexes if needed
          let lateCell = row.cells[3];   // Late Mins column
          let earlyCell = row.cells[4];  // Early Mins column

          if (lateCell) {
              let lateText = lateCell.innerText.trim();
              let lateValue = parseInt(lateText);
              if (!isNaN(lateValue)) {
                  totalLate += lateValue;
              }
              // Count late days (considering only rows where late minutes are 10 or more)
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
  const presentDaysEl = document.querySelector('.present-days');
  const absentDaysEl = document.querySelector('.absent-days');

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
  if (presentDaysEl) {
    presentDaysEl.textContent = calculatePresentDays();
  }
  if (absentDaysEl) {
    absentDaysEl.textContent = calculateAbsentDays();
  }
@endpush
