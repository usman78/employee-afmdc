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
          <h3>Leaves information</h3>
          <ul>
            <li><strong>Employee Code: </strong>{{$leaves->emp_code}}</li>
            <li><strong>Employee Name: </strong>{{$leaves->emp_name}}</li>
          </ul>
        </div>
      </div>
        <table class="table mt-5 mb-5">
            <thead>
                <tr>
                    <th>Leave Code</th>
                    <th>Leaves Open</th>
                    <th>Leave Credits</th>
                    <th>Leaves Taken</th>
                    <th>Leaves Credited</th>
                    <th>Leaves Encashed</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaves as $leave)
                    <tr>
                        <td>{{ $leave->leav_code }}</td>
                        <td>{{ $leave->leav_open }}</td>
                        <td>{{ $leave->leav_credit }}</td>
                        <td>{{ $leave->leav_taken }}</td>
                        <td>{{ $leave->leaves_credited }}</td>
                        <td>{{ $leave->leave_encashed }}</td>
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