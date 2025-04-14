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
  padding: .5rem 2.5rem;
}
.leave-link {
  color: #2196f3;
  font-size: 14px;
  margin-left: 15px;
}
.leave-link:hover {
  color: rgb(3 108 191);
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
          </ul>
          <table class="table mt-5 mb-5">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($attendance as $record)
                <tr>
                  <td>{{ Carbon::parse($record['at_date'])->format('d-m-Y') }}</td>
                  <td>
                    @if ($record['is_sunday'] || $record['is_holiday'])
                      <span class="badge badge-info">{{$record['is_holiday'] ? 'Holiday' : 'Sunday'}}</span>
                    @else
                      @if ($record['timein'])
                        {{ Carbon::parse($record['timein'])->format('h:i A') }}
                      @else
                        <span class="badge badge-warning">Not timed in</span>
                      @endif
                    @endif
                  </td>
                  <td>
                    @if ($record['is_sunday'] || $record['is_holiday'])
                      <span class="badge badge-info">{{$record['is_holiday'] ? 'Holiday' : 'Sunday'}}</span>
                    @else
                      @if ($record['timeout'])
                        {{ Carbon::parse($record['timeout'])->format('h:i A') }}
                      @else
                        <span class="badge badge-warning">Not timed out</span>
                      @endif
                    @endif
                  </td>
                  <td>
                    @if ($record['is_sunday'] || $record['is_holiday'])
                      <span class="badge badge-info">{{$record['is_holiday'] ? 'Holiday' : 'Sunday'}}</span>
                    @else
                      @if ($record['timein'])
                        <span class="badge badge-success">{{$record['is_leave'] ? $record['leave_type'] : 'Present' }}</span>
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
                          <a class="leave-link" href={{route('apply-leave', ['emp_code' => $emp_code, 'leave_date' => $record['at_date']])}}><i class="fa-solid fa-person-walking-arrow-right"></i> Apply for Leave</a>
                        @endif  
                          {{-- @if ($record['at_date'] >= date('Y-m-d', strtotime($leave->from_date)) && $record['at_date'] <= date('Y-m-d', strtotime($leave->to_date)))
                            <span class="badge badge-success">Leave already applied</span>
                            @break
                          @else
                            <span class="badge badge-danger">Absent</span>
                            <a class="leave-link" href={{route('apply-leave', ['emp_code' => $emp_code, 'leave_date' => $record['at_date']])}}><i class="fa-solid fa-person-walking-arrow-right"></i> Apply for Leave</a>
                            @break
                          @endif
                          @break --}}
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