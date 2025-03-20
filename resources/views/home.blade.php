@extends('layouts.app')

@push('styles')
img {
  max-width: 100px;
  border-radius: 50%;
  max-height: 100px;
  object-fit: cover;
  width: 100%;
  border: 5px solid #2196f3;
}
ul {
  list-style: none;
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
i.bi {
  color: #150E56;
}
.resume .resume-item::before {
  border: 2px solid #2196F3;
}
.resume .resume-item {
  border-left: 2px solid #2196F3;
}
.services .service-item p {
  font-size: 18px;
  color: #2196f3;
}
.services .service-item h3 {
  margin: 30px 0 15px 0;
}
.services .service-item {
  min-width: 300px;
}
@endpush

@section('content')
<div class="container">
  <div class="row gy-4 justify-content-center mt-5">
      <div class="services col-lg-4 justify-content-center d-flex">
        <div class="service-item item-cyan position-relative">
          <div class="icon">
            <img src="{{asset('pictures') . "/" . $employee->pic_name}}">
          </div>
          <a href="#" class="stretched-link">
            <h3>{{capitalizeWords($employee->name)}}</h3>
          </a>
          <p>{{capitalizeWords($employee->designation->desg_short)}}</p>
          {{-- <p style="visibility: hidden;"><em>from initial concept to final, polished deliverable.</em></p> --}}

        </div>
        {{-- <div class="service-item item-cyan position-relative">
          <div class="icon">
            <img src="{{asset('pictures') . "/" . $employee->pic_name}}" class="img-fluid" alt="">
          </div>
        </div> --}}
      </div>
      <div class="col-lg-8 content">
        {{-- <p class="fst-italic py-3">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
          magna aliqua.
        </p> --}}
        <div class="row resume">
          <div class="col-lg-6">
            <div class="resume-item pb-0">
              <p>Name</p>
              <h4>{{capitalizeWords($employee->name)}}</h4>
              
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
              <p>Designation:</p>
              <h4>{{capitalizeWords($employee->designation->desg_short)}}</h4>
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
        {{-- <p class="py-3">
          Officiis eligendi itaque labore et dolorum mollitia officiis optio vero. Quisquam sunt adipisci omnis et ut. Nulla accusantium dolor incidunt officia tempore. Et eius omnis.
          Cupiditate ut dicta maxime officiis quidem quia. Sed et consectetur qui quia repellendus itaque neque.
        </p> --}}
      </div>
    </div>
</div>
@endsection

@push('scripts')
  
@endpush




