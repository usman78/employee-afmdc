@extends('layouts.app')

@push('styles')
    .img-fluid {
      max-width: 200px;
    }
    ul {
      list-style: none;
    }
    h2 {
      color: #7B113A;
    }
    strong {
      color: #150E56;
    }
    i.bi {
      color: #150E56;
    }
  
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif   
                    {{ __('You are logged in!') }}                    
                </div>
            </div>
        </div>
    </div>
    <div class="row gy-4 justify-content-center mt-5">
        <div class="col-lg-4 justify-content-center d-flex">
          <img src="{{asset('pictures') . "/" . $employee->pic_name}}" class="img-fluid" alt="">
        </div>
        <div class="col-lg-8 content">
          <h2>{{capitalizeWords($employee->name)}}</h2>
          {{-- <p class="fst-italic py-3">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
            magna aliqua.
          </p> --}}
          <div class="row mt-5">
            <div class="col-lg-6">
              <ul>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Designation:</strong> <span>{{capitalizeWords($employee->designation->desg_short)}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Department:</strong> <span>{{capitalizeWords($employee->department->dept_desc)}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Phone:</strong> <span>{{$employee->phone}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>City:</strong> <span>{{capitalizeWords($employee->nic_iss_plc)}}</span></li>
              </ul>
            </div>
            <div class="col-lg-6">
              <ul>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Employee Code:</strong> <span>{{$employee->emp_code}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>CNIC:</strong> <span>{{$employee->nic_num}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Email:</strong> <span>{{strtolower($employee->emp_email)}}</span></li>
                <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>PMDC No.:</strong> <span>{{$employee->pmdc_no}}</span></li>
              </ul>
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
