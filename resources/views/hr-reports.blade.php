@extends('layouts.app')
@push('styles')
    .portfolio-details .portfolio-info a.card
    {
        text-decoration: none;
    }

    .report-actions {
        border: 0;
        border-radius: .75rem;
    }

    .report-actions .card-body {
        padding: 1.5rem;
    }

    .report-action {
        display: flex;
        align-items: center;
        min-height: 68px;
        padding: .85rem 1rem;
        border: 1px solid #dbe5f1;
        border-radius: .6rem;
        background: #fff;
        color: #344767;
        font-weight: 600;
        line-height: 1.25;
        text-align: left;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background-color .2s ease;
    }

    .report-action:hover,
    .report-action:focus {
        border-color: #4e73df;
        background: #f4f7ff;
        box-shadow: 0 .4rem 1rem rgba(78, 115, 223, .15);
        color: #2e59d9;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .report-action i:first-child {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        margin-right: .75rem;
        border-radius: .5rem;
        background: #e8efff;
        color: #4e73df;
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
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{!!  ($total_strength_afmdc ?? 0) . ' <small>(AFMDC)</small> + ' . ($total_strength_afh ?? 0) . ' <small>(AFH)</small>' !!}</div>
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
                    The Present, Absent/Leave and Late Coming numbers include only those who are working in Aziz Fatimah Medical & Dental College (AFMDC), NOT those who are working in Aziz Fatimah Hospital (AFH).
                </div>
            </div>

            @php($reportAccess = app(\App\Services\ReportAccessService::class))
            <div class="report-actions card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="mb-1 font-weight-bold text-primary">Reports</h5>
                            <p class="mb-0 text-muted small">Select a report to view its details.</p>
                        </div>
                    </div>
                    <div class="row">
                @if($reportAccess->allowed(Auth::user(), 'attendance'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('attendance-report') }}" class="report-action h-100 w-100">
                    <i class="fas fa-calendar-check me-1" aria-hidden="true"></i> Attendance Report
                    </a>
                </div>
                @endif

                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('attendance-late-report') }}" class="report-action h-100 w-100">
                    <i class="fas fa-clock" aria-hidden="true"></i> Late Report
                    </a>
                </div>

                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('attendance-absent-report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-times" aria-hidden="true"></i> Absent Report
                    </a>
                </div>

                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('attendance-present-report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-check-circle" aria-hidden="true"></i> Present Report
                    </a>
                </div>

                @if($reportAccess->allowed(Auth::user(), 'manual-attendance'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('manual-attendance-report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-clipboard-check me-1" aria-hidden="true"></i> Manual Attendance
                    </a>
                </div>
                @endif

                @if($reportAccess->allowed(Auth::user(), 'individual-leave-report'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('individual-leave-report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-user-clock me-1" aria-hidden="true"></i> Individual Leave Report
                    </a>
                </div>
                @endif

                @if($reportAccess->allowed(Auth::user(), 'leave'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('leave-report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-plane-departure me-1" aria-hidden="true"></i> Leave Report
                    </a>
                </div>
                @endif

                @if($reportAccess->allowed(Auth::user(), 'leave'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('hr-leaves-applied') }}" class="report-action h-100 w-100" id="hr-leaves-applied">
                        <i class="fas fa-list-alt me-1" aria-hidden="true"></i> Leaves Status
                    </a>
                </div>
                @endif

                @if($reportAccess->allowed(Auth::user(), 'leave'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <button type="button" class="report-action h-100 w-100" id="pending-leaves-report-btn">
                        <i class="fas fa-hourglass-half me-1" aria-hidden="true"></i> Pending Leaves Report
                    </button>
                </div>
                @endif

                @if($reportAccess->allowed(Auth::user(), 'department-strength'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('department-strength-report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-building me-1" aria-hidden="true"></i> Department Strength
                    </a>
                </div>
                @endif

                @if($reportAccess->allowed(Auth::user(), 'advance-salary-hr'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('advance-salary.report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-money-check-alt me-1" aria-hidden="true"></i> Advance Salary Report
                    </a>
                </div>
                @endif
                @if($reportAccess->allowed(Auth::user(), 'exit-interview'))

                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('exit-interview.report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-door-open me-1" aria-hidden="true"></i> Exit Interview Report
                    </a>
                </div>
                @endif

                @if($reportAccess->allowed(Auth::user(), 'overtime-hr'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('overtime.report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-business-time me-1" aria-hidden="true"></i> Overtime Report
                    </a>
                </div>
                @endif

                @if($reportAccess->allowed(Auth::user(), 'overtime-eligibility'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('overtime.eligibility-report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-user-check me-1" aria-hidden="true"></i> Overtime Eligibility
                    </a>
                </div>
                @endif
                @if($reportAccess->allowed(Auth::user(), 'attendance-discrepancy'))
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <a href="{{ route('att-discrepancy-report') }}" class="report-action h-100 w-100">
                        <i class="fas fa-exclamation-triangle me-1" aria-hidden="true"></i> Attendance Discrepancy Report
                    </a>
                </div>
                @endif
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  function printLeavesReport(title, subtitle, tableHtml) {
    const printWindow = window.open('', 'leaves-report');
    if (!printWindow) {
      return;
    }
    printWindow.document.write(`
      <html>
        <head>
          <title>${title}</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 16px; color: #111; }
            h2 { margin: 0 0 4px; }
            .subtitle { margin: 0 0 12px; color: #555; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
            th { background: #f5f5f5; }
            .badge { padding: 2px 6px; border-radius: 4px; color: #fff; font-size: 12px; }
            .bg-warning { background: #ff9800; }
            .bg-info { background: #2196f3; }
            .bg-primary { background: #0d6efd; }
            .bg-success { background: #4caf50; }
            .bg-danger { background: #f44336; }
            .bg-secondary { background: #6c757d; }
          </style>
        </head>
        <body>
          <h2>${title}</h2>
          <div class="subtitle">${subtitle || ''}</div>
          ${tableHtml}
        </body>
      </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
  }

  document.getElementById('hr-leaves-applied').addEventListener('click', async function(event) {
    event.preventDefault();
    const url = this.href;
    const now = new Date();
    const monthDefault = now.toISOString().slice(0, 7);

    const { value: formValues } = await Swal.fire({
      title: 'Leaves Applied Report',
      html: `
        <div class="text-start">
          <label for="swal-emp-code" class="form-label">Employee Code</label>
          <input id="swal-emp-code" class="form-control" placeholder="e.g. 12345">
        </div>
        <div class="text-start mt-2">
          <label for="swal-month" class="form-label">Month</label>
          <input id="swal-month" type="month" class="form-control" value="${monthDefault}">
        </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'View Report',
      preConfirm: () => {
        const empCode = document.getElementById('swal-emp-code').value.trim();
        const month = document.getElementById('swal-month').value;
        if (!empCode) {
          Swal.showValidationMessage('Employee code is required.');
          return false;
        }
        if (!month) {
          Swal.showValidationMessage('Month is required.');
          return false;
        }
        return { empCode, month };
      }
    });

    if (!formValues) {
      return;
    }

    $.ajax({
      url: url,
      type: 'GET',
      data: {
        emp_code: formValues.empCode,
        month: formValues.month
      },
      statusCode: {
        401: function() {
          Swal.fire({
            title: 'Session Expired',
            text: 'Your session has expired. Please login again.',
            icon: 'warning'
          }).then(() => {
            window.location.href = "{{ route('login') }}";
          });
        },
        419: function() {
          Swal.fire({
            title: 'Session Expired',
            text: 'Your session has expired. Please login again.',
            icon: 'warning'
          }).then(() => {
            window.location.href = "{{ route('login') }}";
          });
        }
      },
      success: function(response) {
        if(response.success) {
          const modalHtml = `
            <div class="text-muted mb-2"><small>${response.subtitle || ''}</small></div>
            <div class="d-flex justify-content-end mb-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="print-leaves-report">Print</button>
            </div>
            ${response.html}
          `;
          Swal.fire({
            width: 900,
            draggable: true,
            title: response.title || 'Leaves Applied',
            html: modalHtml,
            didOpen: () => {
              const btn = document.getElementById('print-leaves-report');
              if (btn) {
                btn.addEventListener('click', () => {
                  printLeavesReport(response.title || 'Leaves Applied', response.subtitle || '', response.html);
                });
              }
            }
          });
        } else {
          Swal.fire({
            title: 'Error',
            text: response.message || 'Could not fetch leaves applied.',
            icon: 'error'
          });
        }
      },
      error: function() {
        Swal.fire({
          title: 'Error',
          text: 'Could not fetch leaves applied.',
          icon: 'error'
        });
      }
    });
  });

  // Pending Leaves Report functionality
  document.getElementById('pending-leaves-report-btn').addEventListener('click', async function() {
    const now = new Date();
    const monthDefault = now.toISOString().slice(0, 7);

    const { value: month } = await Swal.fire({
      title: 'Pending Leaves Report',
      html: `
        <div class="text-start">
          <label for="swal-month" class="form-label">Select Month</label>
          <input id="swal-month" type="month" class="form-control" value="${monthDefault}">
        </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: 'View Report',
      preConfirm: () => {
        const selectedMonth = document.getElementById('swal-month').value;
        if (!selectedMonth) {
          Swal.showValidationMessage('Month is required.');
          return false;
        }
        return selectedMonth;
      }
    });

    if (!month) {
      return;
    }

    // Navigate to dedicated view
    window.location.href = "{{ route('pending-leaves-report-view') }}?month=" + month;
  });
@endpush
