@extends('layouts.app')

@push('styles')
    .admin-report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
    }
    a.card.admin-report-card:hover {
        text-decoration: none;
    }
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="portfolio-details mb-5">
                <div class="portfolio-info">
                    <h3>Admin Reports</h3>
                    <p class="text-muted mb-4">Choose an administrative report to continue.</p>

                    @php($reportAccess = app(\App\Services\ReportAccessService::class))
                    <div class="row">
                        @if($reportAccess->allowed(auth()->user(), 'admin-attendance-discrepancy'))
                            <div class="col-xl-4 col-md-6 mb-4">
                                <a href="{{ route('admin-reports.attendance-discrepancy') }}" class="card admin-report-card shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Attendance
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Discrepancy Report</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
