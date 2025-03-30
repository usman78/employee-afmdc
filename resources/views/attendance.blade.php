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
                        <span class="badge badge-danger">Absent</span>
                        <a href={{route('apply-leave', $record['at_date'])}}>Click to Apply for Leave</a>
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