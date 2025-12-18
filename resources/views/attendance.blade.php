@extends('layouts.app')

@php
  use Carbon\Carbon;
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
{{-- .remaining-negative {
  color: #f44336 !important;
} --}}
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
      <div class="portfolio-details mt-5 mb-5">
        <div class="portfolio-info aos-init aos-animate pt-4" data-aos="fade-up" data-aos-delay="200">
          <h3>Attendance Information</h3>
          <div class="row gy-4 stats">
            <div class="col-md-4">
              <div class="stats-item text-center w-100 h-100">
                <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="0" class="purecounter late-mins"></span>
                <p>Late Coming Mins</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="stats-item text-center w-100 h-100">
                <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="0" class="purecounter early-mins"></span>
                <p>Early Off Mins</p>
              </div>
            </div>
            <div class="col-md-4">
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
                <tr>
                  <td>{{ Carbon::parse($record['at_date'])->format('j M') }}</td>
                  <td>
                    @if ($record['is_sunday'] || $record['is_holiday'])
                      <span class="badge badge-info">{{$record['is_holiday'] ? 'Holiday' : 'Sunday'}}</span>
                    @else
                      @if(!empty($record['time_logs']))
                        {{-- Loop through each time log and display them --}}
                        @foreach ($record['time_logs'] as $logs)
                          @if ($logs['timein'] && $logs['timeout'])
                            {{ Carbon::parse($logs['timein'])->format('H:i') . " / " . Carbon::parse($logs['timeout'])->format('H:i') }} <br>
                            @php
                             $lateMins = $logs['late_minutes'] ?? 0;
                             $earlyMins = $logs['early_minutes'] ?? 0;
                            @endphp 
                          @elseif ($logs['timein'] && !$logs['timeout'])
                            {{ Carbon::parse($logs['timein'])->format('H:i') . " / --:--" }} <br>
                          @else
                            <span class="badge badge-danger">Not timed in</span>
                          @endif
                        @endforeach
                      @else
                        <span class="badge badge-danger">Not timed in</span>
                      @endif    
                    @endif
                  </td>
                  <td>
                    @if($record['timein'] && $record['timeout'])
                      @foreach ($record['time_logs'] as $logs)
                        @if ($lateMins >= 10)
                          {{ $lateMins }} mins 
                        @endif
                        <br>
                        @break
                      @endforeach
                    @endif  
                  </td>
                  <td>
                    @if($record['timein'] && $record['timeout'])
                      @foreach ($record['time_logs'] as $logs)
                        {{-- @if ($earlyMins > 0) --}}
                          {{ $earlyMins }} mins
                        {{-- @endif --}}
                        <br>
                        @break
                      @endforeach
                    @endif
                  </td>
                  <td>
                    @php 
                      $leaveFound = false; 
                      foreach ($leaves as $leave) {
                        if ($record['at_date'] >= date('Y-m-d', strtotime($leave->from_date)) && $record['at_date'] <= date('Y-m-d', strtotime($leave->to_date))){
                        $leaveFound = true;
                          break;
                        }
                      }    
                    @endphp
                    @if ($record['is_sunday'] || $record['is_holiday'])
                      <span class="badge badge-info">{{$record['is_holiday'] ? 'Holiday' : 'Sunday'}}</span>
                    @else
                      @if ($record['timein'] && $record['timeout'])
                        @if ($record['short_duty_status'] ?? false)
                          @if($leaveFound)
                            <span class="badge badge-success">Leave already applied</span>
                          @else 
                          <span class="badge badge-warning">{{$record['short_duty_status']}}</span>
                          @endif
                        @else
                          <span class="badge badge-success">{{$record['is_leave'] ? $record['leave_type'] : 'Present' }}</span>
                        @endif
                      @elseif ($record['timein'] && !$record['timeout'])
                        @if ($record['is_leave'])
                          <span class="badge badge-success">{{$record['leave_type']}}</span>
                          
                        @endif
                        <span class="badge badge-success">Present</span>           
                      @else

                        @if ($leaveFound)
                          <span class="badge badge-success">{{$record['leave_type']}}</span>
                        @else
                          <span class="badge badge-danger">Absent</span>
                        @endif
                      @endif
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
          totalMins: total
      };
  }
  const totals = sumLateAndEarlyMinutes();
  const lateEl = document.querySelector('.late-mins');
  const earlyEl = document.querySelector('.early-mins');
  const totalEl = document.querySelector('.total-mins');

  if (lateEl) {
    lateEl.setAttribute('data-purecounter-end', totals.lateMinutes);
  }
  if (earlyEl) {
    earlyEl.setAttribute('data-purecounter-end', totals.earlyMinutes);
  }
  if (totalEl) {
    totalEl.setAttribute('data-purecounter-end', totals.totalMins);
  }
@endpush