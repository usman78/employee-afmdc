@extends('layouts.app')

@push('styles')
  .portfolio-details .portfolio-info a.card { text-decoration: none; }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Finance Reports</h3>
          <p class="text-muted mb-4">Quick access to finance approval reports.</p>

          <div class="row">
            <div class="col-xl-4 col-md-6 mb-4">
              <a href="{{ route('overtime.finance-report') }}" class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Overtime
                      </div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">Finance Approval</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
