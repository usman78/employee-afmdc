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
.side-btn{
    position: fixed;
    top: 50%;
    right: 0;
    transform: translateY(-50%) translateX(0);
    z-index: 2000;
    transition: transform 0.3s ease;
    border-radius: 50%;
    width: 75px;
    height: 75px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
#accordionSidebar {
  z-index: 999999999;
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
                @else 
                  You have not showed up today. 
                @endif
              <li><a class="thick-underline" href="{{route('attendance', $employee->emp_code)}}">Check your current month attendance.</a></li>
              <li><a class="thick-underline" href="{{route('leaves', $employee->emp_code)}}">Check your leaves balance.</a></li>
              <li>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>Please Note!</strong> The Outdoor Duty (OD) can be applied through the portal now, except for AFH employees.
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
<button id="canvasBtn" class="btn btn-primary side-btn"
        data-bs-toggle="offcanvas"
        data-bs-target="#myOffcanvas">
    Notice Board
</button>

<div class="offcanvas offcanvas-end" tabindex="-1" id="myOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Notice Board</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        @if($notices->count() > 0)
            <div class="notices-list">
                @foreach($notices as $notice)
                    <div class="notice-item mb-4 pb-3" style="border-bottom: 1px solid #ddd;">
                        <h6 class="text-primary fw-bold">{{ $notice->title }}</h6>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-calendar"></i> 
                            {{ $notice->created_at->format('M d, Y') }}
                            @if($notice->creator)
                                <br>
                                <i class="bi bi-person"></i> 
                                {{ capitalizeWords($notice->creator->name) }}
                            @endif
                        </p>
                        <p class="mb-2">{{ Str::limit($notice->content, 150) }}</p>
                        @if($notice->attachment_path)
                            <div class="mb-2">
                                <a href="{{ asset('storage/' . $notice->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> {{ $notice->attachment_name }}
                                </a>
                            </div>
                        @endif
                        <small class="text-muted d-block">
                            <a href="javascript:void(0);" onclick="showNoticeModal('{{ $notice->title }}', `{{ $notice->content }}`)">Read more</a>
                        </small>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info" role="alert">
                <p class="mb-0">No notices at the moment. Check back later!</p>
            </div>
        @endif
    </div>
</div>
@endsection

<!-- Notice Modal -->
<div class="modal fade" id="noticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noticeTitle">Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="noticeContent"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
  const offcanvas = document.getElementById('myOffcanvas');
  const btn = document.getElementById('canvasBtn');

  offcanvas.addEventListener('show.bs.offcanvas', function () {
      btn.style.transform = "translateY(-50%) translateX(-400px)";
  });

  offcanvas.addEventListener('hide.bs.offcanvas', function () {
      btn.style.transform = "translateY(-50%) translateX(0)";
  });

  function showNoticeModal(title, content) {
      document.getElementById('noticeTitle').textContent = title;
      document.getElementById('noticeContent').textContent = content;
      const modal = new bootstrap.Modal(document.getElementById('noticeModal'));
      modal.show();
  }
@endpush





