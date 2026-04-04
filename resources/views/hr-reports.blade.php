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

                <div class="col-md-6 col-lg-2">
                    <a href="{{ route('hr-leaves-applied') }}" class="btn btn-primary w-100 text-nowrap" id="hr-leaves-applied">
                    Leaves Status
                    </a>
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
@endpush
