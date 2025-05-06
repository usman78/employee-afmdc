@extends('layouts.app')

@php
  use Carbon\Carbon;
@endphp

@push('styles')
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
      <div class="portfolio-details mt-5 mb-5">
        <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
          <h3>Attendance Information</h3>
          <ul>
            <li><strong>Employee Code: </strong>{{ $emp_code }}</li>
            <li><strong>Employee Name: </strong>{{ capitalizeWords($emp_name) }}</li>
            <li><strong>Current month all attendance records.</strong></li>
            <li class="mt-5">
              @if(session('success'))
                <span class="alert alert-success">{{session('success')}}</span>
              @endif  
              @if(session('error'))
                <span class="alert alert-warning">{{session('error')}}</span>
              @endif
            </li>
          </ul>
          <table class="table mt-5 mb-5">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time-In/Out</th>
                <th>Work Mins</th>
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
                      @if ($record['timein'] && $record['timeout'])
                        {{ Carbon::parse($record['timein'])->format('h:i A') . " / " . Carbon::parse($record['timeout'])->format('h:i A') }}
                      @elseif ($record['timein'] && !$record['timeout'])
                        {{ Carbon::parse($record['timein'])->format('h:i A') . " / Not timed out" }}
                      @else
                        <span class="badge badge-danger">Not timed in</span>
                      @endif
                    @endif
                  </td>
                  <td>
                    @if($record['timein'] && $record['timeout'])
                      {{ $record['worked_minutes'] }} mins
                    @endif  
                  </td>
                  <td>
                    @if ($record['is_sunday'] || $record['is_holiday'])
                      <span class="badge badge-info">{{$record['is_holiday'] ? 'Holiday' : 'Sunday'}}</span>
                    @else
                      @if ($record['timein'] && $record['timeout'])
                        @if ($record['short_duty_status'] ?? false)
                          <span class="badge badge-warning">{{$record['short_duty_status']}}</span>
                          <a class="leave-link" href={{route('apply-leave-advance', $emp_code)}}><i class="fa-solid fa-person-walking-arrow-right"></i> Apply for Leave</a>
                          @else
                            <span class="badge badge-success">{{$record['is_leave'] ? $record['leave_type'] : 'Present' }}</span>
                        @endif
                      @elseif ($record['timein'] && !$record['timeout'])
                      <span class="badge badge-success">Present</span>           
                      @else
                        @php $leaveFound = false; 
                        foreach ($leaves as $leave) {
                          if ($record['at_date'] >= date('Y-m-d', strtotime($leave->from_date)) && $record['at_date'] <= date('Y-m-d', strtotime($leave->to_date))){
                           $leaveFound = true;
                            break;
                          }
                        }    
                        @endphp
                        @if ($leaveFound)
                          <span class="badge badge-success">Leave already applied</span>
                        @else
                          <span class="badge badge-danger">Absent</span>
                          <a class="leave-link" href={{route('apply-leave-advance', $emp_code)}}><i class="fa-solid fa-person-walking-arrow-right"></i> Apply for Leave</a>
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
@endpush