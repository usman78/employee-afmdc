@extends('layouts.app')
@push('styles')
    .portfolio-details .portfolio-info a.card
    {
        text-decoration: none;
    }
@endpush
@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>HR Reports</h3>
          <p class="text-muted mb-4">Quick access to all HR reports.</p>
          <div class="row">

            <!-- Total Strength -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Strength</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_strength ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Male Strength -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Male</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $male_strength ?? 0 }}</div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $male_percent ?? 0 }}%" aria-valuenow="{{ $male_percent ?? 0 }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-male fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Female Strength -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Female</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $female_strength ?? 0 }}</div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-danger" role="progressbar"
                                                style="width: {{ $female_percent ?? 0 }}%" aria-valuenow="{{ $female_percent ?? 0 }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-person-dress fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
          {{-- <hr>
          <div class="text-center"><h4>AFMDC Statistics</h4></div> --}}
          <div class="row">
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('attendance-present-report') }}" class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Present</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $present_count ?? 0 }}</div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                style="width: {{ $present_percent ?? 0 }}%" aria-valuenow="{{ $present_percent ?? 0 }}" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('attendance-late-report') }}" class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Late Coming
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $late_count ?? 0 }}</div>
                                    </div>
                                    <div class="col">
                                      <div class="progress progress-sm mr-2">
                                          <div class="progress-bar bg-warning" role="progressbar"
                                              style="width: {{ $late_percent ?? 0 }}%" aria-valuenow="{{ $late_percent ?? 0 }}" aria-valuemin="0"
                                              aria-valuemax="100"></div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('attendance-absent-report') }}" class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Absent/Leave
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $absent_leave_count ?? 0 }}</div>
                                    </div>
                                    <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-danger" role="progressbar"
                                            style="width: {{ $absent_leave_percent ?? 0 }}%" aria-valuenow="{{ $absent_leave_percent ?? 0 }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Please Note</h6>
                </div>
                <div class="card-body">
                    The Present, Absent/Leave and Late Coming numbers include only those who are working in Aziz Fatmiah Medical & Dental College (AFMDC), NOT those who are working in Aziz Fatmiah Hospital (AFH).
                </div>
            </div>

            <div class="row g-3 justify-content-center">
                <div class="col-md-6 col-lg-2">
                    <a href="{{ route('attendance-report') }}" class="btn btn-primary w-100 text-nowrap">
                    Attendance Report
                    </a>
                </div>

                <div class="col-md-6 col-lg-2">
                    <a href="{{ route('attendance-late-report') }}" class="btn btn-primary w-100 text-nowrap">
                    Late Report
                    </a>
                </div>

                <div class="col-md-6 col-lg-2">
                    <a href="{{ route('attendance-absent-report') }}" class="btn btn-primary w-100 text-nowrap">
                    Absent Report
                    </a>
                </div>

                <div class="col-md-6 col-lg-2">
                    <a href="{{ route('attendance-present-report') }}" class="btn btn-primary w-100 text-nowrap">
                    Present Report
                    </a>
                </div>

                <div class="col-md-6 col-lg-2">
                    <a href="{{ route('leave-report') }}" class="btn btn-primary w-100 text-nowrap">
                    Leave Report
                    </a>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
