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
.form-check {
  display: inline-block;
}
.alert-warning {
  --bs-alert-color: #ffffff;
  --bs-alert-bg: #e91e1e;
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
              <form action="{{ route('store-leave', ['emp_code' => $emp_code, 'leave_date' => $leave_date]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
                @endif
                <ul>
                  <li><strong>Employee Code: </strong>{{$emp_code}}</li>
                  <li><strong>Employee Name: </strong>{{capitalizeWords($employee->name)}}</li>
                  <li><strong>Leave Date: </strong>{{date('d-m-Y',  strtotime($leave_date)) }}</li>
                  <li><strong>Select Leave Type: </strong></li>
                  <div class="form-check mt-2">
                      <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault1" value="casual" checked>
                      <label class="btn btn-outline-primary" for="flexRadioDefault1">
                        Casual Leave
                      </label>
                  </div>
                  <div class="form-check">
                    <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault2" value="medical">
                    <label class="btn btn-outline-primary" for="flexRadioDefault2">
                      Medical Leave
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault3" value="annual">
                    <label class="btn btn-outline-primary" for="flexRadioDefault3">
                      Annual Leave
                    </label>
                  </div>
                  <li class="mt-2"><strong>Select Leave Duration: </strong></li>
                  <div class="form-check mt-2">
                      <input class="btn-check" type="radio" name="leave_duration" id="flexRadioDefault4" value="half" checked>
                      <label class="btn btn-outline-primary" for="flexRadioDefault4">
                        Half Leave
                      </label>
                  </div>
                  <div class="form-check">
                    <input class="btn-check" type="radio" name="leave_duration" id="flexRadioDefault5" value="full">
                    <label class="btn btn-outline-primary" for="flexRadioDefault5">
                      Full Leave
                    </label>
                  </div>
                  <li class="mt-2"><strong>Reason of Leave: </strong></li>
                  <input type="text" class="form-control mt-2" name="reason" id="reason" placeholder="Enter reason of leave" required>
                  <li style="margin-top: 20px;">
                    @if(session('success'))
                      <span class="alert alert-success">{{session('success')}}</span>
                    @endif  
                    @if(session('error'))
                      <span class="alert alert-warning">{{session('error')}}</span>
                    @endif
                  </li>
                </ul>
                <div class="row">
                    <div class="col-12 justify-content-center text-center mt-5">
                        <a href="{{ route('leaves', $emp_code) }}" class="btn btn-primary mt-3"><i class="fa-solid fa-backward"></i> Back</a>
                        <button type="submit" class="btn btn-success mt-3"><i class="fa-solid fa-person-walking-arrow-right"></i> Apply Leave</button>
                    </div>
                </div>
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

