@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
  <div class="row">
    <div class="col-12">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <div class="row align-items-center">
            <div class="col">
              <h6 class="m-0 font-weight-bold text-primary">Pending Leaves Report</h6>
              <small class="text-muted d-block mt-1">Showing leaves for <strong>{{ $monthName }}</strong></small>
            </div>
            <div class="col text-end">
              <button class="btn btn-sm btn-outline-secondary" id="print-pending-report">
                <i class="fas fa-print"></i> Print
              </button>
              <a href="{{ route('hr-reports') }}" class="btn btn-sm btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <!-- Month Selection -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="form-group">
                <label for="monthSelect" class="form-label">Select Month</label>
                <input type="month" id="monthSelect" class="form-control" value="{{ $currentMonth }}">
              </div>
            </div>
          </div>

          <!-- Tabs Navigation -->
          <ul class="nav nav-tabs mb-4" role="tablist">
            @foreach($statusMap as $status => $stageName)
              <li class="nav-item" role="presentation">
                <button 
                  class="nav-link {{ $loop->first ? 'active' : '' }}" 
                  id="tab-{{ $status }}-btn" 
                  data-bs-toggle="tab" 
                  data-bs-target="#tab-{{ $status }}"
                  type="button" 
                  role="tab"
                >
                  {{ $stageName }}
                  <span class="badge bg-secondary ms-1">
                    {{ count($leavesData[$status] ?? []) }}
                  </span>
                </button>
              </li>
            @endforeach
          </ul>

          <!-- Tab Content -->
          <div class="tab-content">
            @foreach($statusMap as $status => $stageName)
              <div 
                class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                id="tab-{{ $status }}"
                role="tabpanel"
              >
                @php
                  $leaves = $leavesData[$status] ?? [];
                @endphp

                @if(count($leaves) > 0)
                  <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm table-hover">
                      <thead class="table-light">
                        <tr>
                          <th>Emp Code</th>
                          <th>Name</th>
                          <th>Designation</th>
                          <th>Department</th>
                          <th>Leave Type</th>
                          <th>Applied Date</th>
                          <th>From Date</th>
                          <th>To Date</th>
                          <th>Days</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($leaves as $leave)
                          <tr>
                            <td>{{ $leave['emp_code'] }}</td>
                            <td>{{ $leave['name'] }}</td>
                            <td>{{ $leave['designation'] }}</td>
                            <td>{{ $leave['department'] }}</td>
                            <td><span class="badge bg-secondary">{{ $leave['leave_type'] }}</span></td>
                            <td>{{ $leave['applied_date'] }}</td>
                            <td>{{ $leave['from_date'] }}</td>
                            <td>{{ $leave['to_date'] }}</td>
                            <td>{{ $leave['days'] }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @else
                  <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    No leaves found for this stage.
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  document.getElementById('monthSelect').addEventListener('change', function() {
    const month = this.value;
    if (month) {
      window.location.href = "{{ route('pending-leaves-report-view') }}?month=" + month;
    }
  });

  document.getElementById('print-pending-report').addEventListener('click', function() {
    window.print();
  });

  // Enhance print styles
  window.addEventListener('beforeprint', function() {
    document.body.style.backgroundColor = '#fff';
  });
@endpush

@push('styles')
  @media print {
    .btn, .form-group, .btn-outline-secondary, a.btn {
      display: none !important;
    }
    .table-responsive {
      overflow: visible;
    }
    .table {
      font-size: 11px;
    }
    .card {
      box-shadow: none !important;
      border: 1px solid #dee2e6;
    }
  }
@endpush
