@extends('layouts.app')

@push('styles')

    {{-- .inventory-report-card {
        border: none;
        border-left: 4px solid #2196f3;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    } --}}
    .inventory-report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
    }
    a.card.inventory-report-card:hover {
        text-decoration: none;
    }

@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="portfolio-details mb-5">
                <div class="portfolio-info">
                    <h3>Inventory Reports</h3>
                    <p class="text-muted mb-4">Choose an inventory report to continue.</p>

                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <a href="{{ route('inventory', Auth::user()->emp_code) }}" class="card inventory-report-card shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Store Issuance
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">My Inventory</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        @if (Auth::user()->isStoreOfficer())
                            <div class="col-xl-4 col-md-6 mb-4">
                                <a href="{{ route('inventory.store_report') }}" class="card inventory-report-card shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Store Issuance
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Detailed Report</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-xl-4 col-md-6 mb-4">
                                <a href="{{ route('inventory.indent_advise_tracking') }}" 
                                class="card inventory-report-card shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Indent To Advice Tracking
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Report</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
