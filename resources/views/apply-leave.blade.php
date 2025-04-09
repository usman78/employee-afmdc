@extends('layouts.app')

@push('styles')
.btn-check:checked+.btn {
    color: #fff;
    background-color: #2196f3;
    border-color: #2196f3;
}
.btn-check+.btn {
    color: #2196f3;
    background-color: #fff;
    border-color: #2196f3;
}
.btn-check+.btn:hover {
    color: #fff;
    background-color: #2196f3;
}
@endpush
@section('content')
<div class="container">
    <div class="row">
      <div class="col-6 justify-content-center mx-auto">
        <div class="portfolio-details mt-5">
          <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
            <h3>Leave Application</h3>
            <div class="row">
                {{-- <div class="col-6"> --}}
                    <ul>
                        <li><strong>Employee Code: </strong>{{$emp_code}}</li>
                        <li><strong>Employee Name: </strong>{{capitalizeWords($emp_name)}}</li>
                        <li><strong>Leave Date: </strong>{{date('d-m-Y',  strtotime($leave_date)) }}</li>
                    {{-- </ul> --}}
                {{-- </div> --}}
                {{-- <div class="col-6"> --}}
                    {{-- <ul> --}}
                        <li><strong>Select Leave Type: </strong></li>
                        <div class="form-check mt-2">
                            <input class="btn-check" type="radio" name="flexRadioDefault" id="flexRadioDefault1" checked>
                            <label class="btn btn-outline-primary" for="flexRadioDefault1">
                              Casual Leave
                            </label>
                          </div>
                          <div class="form-check">
                            <input class="btn-check" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                            <label class="btn btn-outline-primary" for="flexRadioDefault2">
                              Medical Leave
                            </label>
                          </div>
                          <div class="form-check">
                            <input class="btn-check" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
                            <label class="btn btn-outline-primary" for="flexRadioDefault3">
                              Annual Leave
                            </label>
                          </div>
                          <div class="form-check">
                            <input class="btn-check" type="radio" name="flexRadioDefault" id="flexRadioDefault4">
                            <label class="btn btn-outline-primary" for="flexRadioDefault4">
                              OD (Outdoor Duty)
                            </label>
                          </div>
                    </ul>
                {{-- </div> --}}
            {{-- </div> --}}
            <div class="row">
                <div class="col-12 justify-content-center text-center mt-5">
                    <a href="{{ route('leaves', $emp_code) }}" class="btn btn-primary mt-3"><i class="fa-solid fa-backward"></i> Back</a>
                    <a href="{{ route('apply-leave', ['emp_code' => $emp_code, 'leave_date' => $leave_date]) }}" class="btn btn-success mt-3"><i class="fa-solid fa-person-walking-arrow-right"></i> Apply Leave</a>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

