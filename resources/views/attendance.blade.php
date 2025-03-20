@extends('layouts.app')

@push('styles')
.badge-success {
  background-color: #2196f3;
}
.badge-warning {
  background-color: #ff9800;
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
      <div class="portfolio-details mt-5">
        <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
          <h3>Attendance information</h3>
          <ul>
            <li><strong>Employee Code: </strong>{{$attendance->emp_code}}</li>
            <li><strong>Employee Name: </strong>{{$attendance->emp_name}}</li>
            <li><strong>Last 30 attendance records.</li>
          </ul>
        </div>
      </div>
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
                @foreach ($attendance as $att)
                    <tr>
                        <td>{{ date('d-m-Y',strtotime($att->at_date)) }}</td>
                        <td>{{ date('h:i A',strtotime($att->timein)) }}</td>
                        <td>
                          @if($att->timeout == null) <span class="badge badge-warning">Not Timed Out</span> @else {{date('h:i A',strtotime($att->timeout))}} @endif
                        </td>
                        <td>
                            @if($att->timein == null) <span class="badge badge-danger">Absent</span> @else <span class="badge badge-success">Present</span> @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')

@endpush



