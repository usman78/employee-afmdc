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
  padding: .5rem 0.5rem;
}

@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mt-5">
        <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
          <h3>Leaves Balance</h3>
          <ul>
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
                    <th>Leave Type</th>
                    <th>Balance</th>
                    <th>Pending Approval</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaves as $leave)
                    <tr>
                        <td>{{ $leave->leave_type }}</td>
                        <td style="color: #2196F3"><strong>{{ $leave->leav_open + $leave->leav_credit - $leave->leav_taken - $leave->leave_encashed }}</strong></td>
                        <td>
                          @if ($leave->leav_code == 1)
                            {{ $pendingLeaves['casual_leave'] ?? 0 }}
                          @elseif ($leave->leav_code == 2) 
                            {{ $pendingLeaves['medical_leave'] ?? 0 }}
                          @elseif ($leave->leav_code == 3)   
                            {{ $pendingLeaves['annual_leave'] ?? 0 }}
                          @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
          <div class="row mt-5">
            <div class="col-12" style="text-align: center;">
              <a class="btn btn-primary" href="{{route('check-if-any-leave', parameters: $leaves->emp_code)}}"><i class="fa-solid fa-house-person-leave"></i>Apply For Leave</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
  
@endpush