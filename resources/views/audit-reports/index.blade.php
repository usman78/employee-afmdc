@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Audit Reports</h1>
    </div>

    @php($reportAccess = app(App\Services\ReportAccessService::class))
    <div class="row">
        @if($reportAccess->allowed(Auth::user(), 'audit-advance-salary'))
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('audit-reports.advance-salary') }}" class="card border-left-primary shadow h-100 py-2 text-decoration-none">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Advance Salary</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Audit Report</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-money-check-alt fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
        @if($reportAccess->allowed(Auth::user(), 'audit-overtime'))
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('audit-reports.overtime') }}" class="card border-left-success shadow h-100 py-2 text-decoration-none">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Overtime</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Audit Report</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
