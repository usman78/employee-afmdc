@extends('layouts.app')
@php
  use Carbon\Carbon;
@endphp
@push('styles')
p {
  color: #973594;
  font-weight: 600;
}
ul {
  list-style: none;
}
.resume {
  margin-left: 50px;
}
.resume .resume-item h4 {
  color: #2196f3;
  text-transform: none;
}
.resume .resume-item:not(:first-child) {
  margin-top: 20px;
}
strong {
  color: #150E56;
}
.resume .resume-item::before {
  border: 2px solid #973594;
}
.resume .resume-item {
  border-left: 2px solid #973594;
}
.services .service-item p {
  font-size: 18px;
  color: #000;
}
.services .service-item h3 {
  margin: 30px 0 15px 0;
  color: #973594;
}
.services .service-item {
  min-width: 300px;
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
.portfolio-info .links {
  color: #fff;
  font-weight: 600;
}
.portfolio-info h3 {
    color: #973594;
}
.btn-log-out{
  color: #fff;
  background: #f44336;
  text-transform: uppercase;
  font-weight: 600;
  font-size: 12px;
  letter-spacing: 1px;
  display: inline-block;
  padding: 12px 40px;
  border-radius: 50px;
  transition: 0.5s;
  margin-top: 30px;
  cursor: pointer;
}
@endpush

@section('content')
<div class="container">
  <div class="row gy-4 justify-content-center mt-5">
      <div class="services col-md-4 justify-content-center d-flex">
        <div class="service-item item-cyan position-relative">
          <div class="icon">
            <img style="max-width: 100px; object-fit: cover; width: 100%; border: 4px solid #973594;" src="{{asset('pictures') . "/" . getProfilePicName($employee->emp_code)}}">
          </div>
          <a href="#" class="stretched-link">
            <h3>{{capitalizeWords($employee->name)}}</h3>
          </a>
          <p>{{$employee->designation->desg_short}}</p>
          <small class="d-inline-flex mt-3 px-2 py-1 fw-semibold text-success-emphasis bg-success-subtle border border-success-subtle rounded-2">{{$employeeStatus}}</small>
        </div>
      </div>
      <div class="col-md-5 d-block mx-auto">
    
        <div class="portfolio-details">
          <div class="portfolio-info">
            <h3>Dashboard</h3>
            <ul>
              <li><strong>Today's Time </strong>
                @if ($today && $today->timein != null && $today->timeout == null) 
                  {{ Carbon::parse($today->timein)->format('h:i A') }} 
              </li>
              <li><strong>Status: </strong>
                <span class="badge badge-success">In the Office</span> 
              </li>
                @elseif ($today && $today->timein != null && $today->timeout != null)
                  {{ Carbon::parse($today->timein)->format('h:i A') . " - " . Carbon::parse($today->timeout)->format('h:i A') }}
                </li>
                <li><strong>Your Status </strong>
                  <span class="badge badge-success">You have clocked out.</span> 
                </li>  
                @else You have not showed up today. 
                @endif

              {{-- </li>
              <li><strong>Status: </strong>
                @if ($today && $today->timein != null) 
                <span class="badge badge-success">In the Office</span> 
                @else 
                <span class="badge badge-danger">Out of office</span> 
                @endif
              </li> --}}
              <li><a class="thick-underline" href="{{route('attendance', $employee->emp_code)}}">Check your current month attendance.</a></li>
              <li><a class="thick-underline" href="{{route('leaves', $employee->emp_code)}}">Check your leaves balance.</a></li>
              <li>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                  <strong>Please Note!</strong> The Outdoor Duty (OD) leaves should be applied manually through HR Approval.
                </div>
              </li>
              <li><a class="btn-log-out" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
              </form>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="row resume mt-4">
          <div class="col-lg-12">
            <div class="resume-item pb-0">
              <p>Date of Joining</p>
              <h4>{{dateFormat($employee->join_date)}}</h4>
              
              {{-- <p><em>Innovative and deadline-driven Graphic Designer with 3+ years of experience designing and developing user-centered digital/print marketing material from initial concept to final, polished deliverable.</em></p> --}}
              {{-- <ul>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Designation:</strong> <span>{{capitalizeWords($employee->designation->desg_short)}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Department:</strong> <span>{{capitalizeWords($employee->department->dept_desc)}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Phone:</strong> <span>{{$employee->phone}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>City:</strong> <span>{{capitalizeWords($employee->nic_iss_plc)}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Employee Code:</strong> <span>{{$employee->emp_code}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>CNIC:</strong> <span>{{$employee->nic_num}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Email:</strong> <span>{{strtolower($employee->emp_email)}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>PMDC No.:</strong> <span>{{$employee->pmdc_no}}</span></li>
              </ul> --}}
            </div>
            <div class="resume-item pb-0">
              <p>Service Years</p>
              <h4>
                  @php $diff = Carbon::parse($employee->join_date)->diff(Carbon::now()) @endphp
                  {{ $diff->y }} Years
                  {{ $diff->m }} Months
                  {{ $diff->d }} Days
              </h4>
              {{-- <h4>{{ capitalizeAbbreviation( capitalizeWords($employee->designation->desg_short))}}</h4> --}}
            </div>
            <div class="resume-item pb-0">
              <p>Employee Code</p>
              <h4>{{$employee->emp_code}}</h4>
            </div>
            <div class="resume-item pb-0">
              <p>Department:</p>
              <h4>{{capitalizeWords($employee->department->dept_desc)}}</h4>
            </div>
          </div>
        </div>
      </div>

  </div>
  {{-- <div class="row">
    <div class="col-lg-8 content">

      <div class="row resume">
        <div class="col-lg-6">
          <div class="resume-item pb-0">
            <p>Date of Joining</p>
            <h4>{{dateFormat($employee->join_date)}}</h4>
          </div>
          <div class="resume-item pb-0">
            <p>Service Years</p>
            <h4>
                @php $diff = Carbon::parse($employee->join_date)->diff(Carbon::now()) @endphp
                {{ $diff->y }} Years
                {{ $diff->m }} Months
                {{ $diff->d }} Days
            </h4>
          </div>
          <div class="resume-item pb-0">
            <p>Employee Code</p>
            <h4>{{$employee->emp_code}}</h4>
          </div>
          <div class="resume-item pb-0">
            <p>Department:</p>
            <h4>{{capitalizeWords($employee->department->dept_desc)}}</h4>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="resume-item pb-0">
            <p>Mobile Number:</p>
            <h4>{{capitalizeWords($employee->phone)}}</h4>
          </div>
          <div class="resume-item pb-0">
            <p>Email Address:</p>
            <h4>{{strtolower($employee->emp_email)}}</h4>
          </div>
          <div class="resume-item pb-0">
            <p>CNIC Number:</p>
            <h4>{{capitalizeWords($employee->nic_num)}}</h4>
          </div>
          <div class="resume-item pb-0">
            <p>City:</p>
            <h4>{{capitalizeWords($employee->nic_iss_plc)}}</h4>
          </div>
        </div>
      </div>
    </div>

  </div> --}}
</div>
@endsection

@push('scripts')
  
@endpush




