@extends('layouts.app')

@php
  use Carbon\Carbon;
  $today = Carbon::today()->toDateString();
  $isTimeIn = false;
@endphp

@push('styles')
  .stats .stats-item {
    padding: 0;
  }
  .stats .stats-item span {
    margin-bottom: 10px;
    padding-bottom: 0px;
  }
  .portfolio .stats .stats-item.text-center.w-100.h-100 {
    background: gainsboro !important;
    border-radius: 10px !important;
    box-shadow: 6px 7px 5px gray !important;
  }
  .badge-success {
    background-color: #2196f3;
  }
  .badge-warning {
    background-color: #ff9800;
  }
  .badge-info {
    background-color: #4caf50;
  }
  .badge-danger {
    background-color: #f44336;
  }
  .table {
    border: 1px solid #ccc;
  }
  .table>:not(caption)>*>* {
    padding: .5rem .7rem;
  }
  td {
    font-size: 14px;
    vertical-align: middle;
  }
  .late-row td {
    background-color: #ffb6b6;
  }
  .swal-email-fields {
    text-align: left;
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .swal-email-fields label {
    font-weight: 600;
  }
  .swal-email-fields .swal2-textarea {
    margin: 0;
    width: 100%;
    box-sizing: border-box;
    font-size: 0.9em;
  }
  @media (max-width: 768px) {
    .portfolio-details .portfolio-info {
      padding: 0 15px;
    }
  }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h3>Attendance Report</h3>
          <h5 class="mt-3">Department-wise Attendance (Single Day)</h5>
          <form action="{{ route('attendance-report-department-data') }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-2 align-items-end">
              <div class="col-md-5">
                <label for="dept_code" class="form-label">Department</label>
                <select
                  id="dept_code"
                  name="dept_code"
                  class="form-control @error('dept_code') is-invalid @enderror"
                  required
                >
                  <option value="">Select department</option>
                  @foreach (($departments ?? collect()) as $department)
                    <option value="{{ $department->dept_code }}" {{ old('dept_code', $selected_dept_code ?? '') == $department->dept_code ? 'selected' : '' }}>
                      {{ $department->dept_desc }}
                    </option>
                  @endforeach
                </select>
                @error('dept_code')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <label for="dept_report_date" class="form-label">Date</label>
                <input
                  type="date"
                  id="dept_report_date"
                  name="dept_report_date"
                  class="form-control @error('dept_report_date') is-invalid @enderror"
                  value="{{ old('dept_report_date', $dept_report_date ?? Carbon::today()->toDateString()) }}"
                  required
                >
                @error('dept_report_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Get Department Report</button>
              </div>
            </div>
          </form>

          @if(isset($departmentAttendanceRows))
            <div class="mb-4" style="padding: 10px; border-radius: 10px; background-color: #cedaff;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">
                  {{ $selected_dept_desc ?? 'Department' }}
                  -
                  {{ Carbon::parse($dept_report_date ?? Carbon::today()->toDateString())->format('j M Y') }}
                </h6>
                <div class="d-flex gap-2">
                  <form action="{{ route('attendance-report-department-download') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="dept_code" value="{{ $selected_dept_code ?? old('dept_code') }}">
                    <input type="hidden" name="dept_report_date" value="{{ $dept_report_date ?? old('dept_report_date', Carbon::today()->toDateString()) }}">
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-download"></i> Download Report
                    </button>
                  </form>
                  <form id="dept-email-report-form" action="{{ route('attendance-report-department-email') }}" method="POST">
                    @csrf
                    <input type="hidden" name="dept_code" value="{{ $selected_dept_code ?? old('dept_code') }}">
                    <input type="hidden" name="dept_report_date" value="{{ $dept_report_date ?? old('dept_report_date', Carbon::today()->toDateString()) }}">
                    <input type="hidden" name="to_emails" id="dept_to_emails" value="">
                    <input type="hidden" name="cc_emails" id="dept_cc_emails" value="">
                    <button type="button" id="dept-email-report-btn" class="btn btn-success">
                      <i class="fas fa-envelope"></i> Send Email with Report
                    </button>
                  </form>
                </div>
              </div>
              <table class="table mt-2 mb-4">
                <thead>
                  <tr>
                    <th>Sr#</th>
                    <th>Employee Code</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Time Status</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($departmentAttendanceRows as $row)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $row['emp_code'] }}</td>
                      <td>{{ $row['name'] }}</td>
                      <td>{{ $row['designation'] }}</td>
                      <td>{{ $row['time_in'] }}</td>
                      <td>{{ $row['time_out'] }}</td>
                      <td>
                        @if($row['time_status'] === 'Late')
                          <span class="badge badge-danger">Late</span>
                        @elseif($row['time_status'] === 'On-time')
                          <span class="badge badge-success">On-time</span>
                        @else
                          <span class="badge badge-warning">--</span>
                        @endif
                      </td>
                      <td>
                        @if($row['status'] === 'Present')
                          <span class="badge badge-success">Present</span>
                        @elseif($row['status'] === 'Leave')
                          <span class="badge badge-warning">Leave</span>
                        @else
                          <span class="badge badge-danger">Absent</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="8" class="text-center">No employee record found for this department/date.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
      <div class="portfolio-details mb-5">
        <div class="portfolio-info">
          <h5 class="mt-3">Employee-wise Attendance (Date Range)</h5>
          <form action="{{ route('attendance-report-data') }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label for="emp_code" class="form-label">Employee Code</label>
                <input
                  type="text"
                  id="emp_code"
                  name="emp_code"
                  class="form-control @error('emp_code') is-invalid @enderror"
                  value="{{ old('emp_code', $searched_emp_code ?? '') }}"
                  placeholder="Enter employee code"
                  required
                >
                @error('emp_code')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input
                  type="date"
                  id="start_date"
                  name="start_date"
                  class="form-control @error('start_date') is-invalid @enderror"
                  value="{{ old('start_date', $searched_start_date ?? Carbon::now()->startOfMonth()->toDateString()) }}"
                  required
                >
                @error('start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input
                  type="date"
                  id="end_date"
                  name="end_date"
                  class="form-control @error('end_date') is-invalid @enderror"
                  value="{{ old('end_date', $searched_end_date ?? Carbon::today()->toDateString()) }}"
                  required
                >
                @error('end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Get Report</button>
              </div>
            </div>
          </form>

          @if(session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
          @endif
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          @if(isset($attendance))
          <div style="padding: 10px; border-radius: 10px; background-color: #cedaff;">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h5 class="mb-0">{{ $emp_name }}</h5>
                <small class="text-muted">
                  {{ Carbon::parse($report_start_date ?? ($searched_start_date ?? Carbon::now()->startOfMonth()->toDateString()))->format('j M Y') }}
                  to
                  {{ Carbon::parse($report_end_date ?? ($searched_end_date ?? Carbon::today()->toDateString()))->format('j M Y') }}
                </small>
              </div>
              <div class="d-flex gap-2">
                <form action="{{ route('attendance-report-download', ['emp_code' => $searched_emp_code ?? old('emp_code')]) }}" method="POST" target="_blank">
                  <input type="hidden" name="start_date" value="{{ $report_start_date ?? ($searched_start_date ?? Carbon::now()->startOfMonth()->toDateString()) }}">
                  @csrf
                  <input type="hidden" name="end_date" value="{{ $report_end_date ?? ($searched_end_date ?? Carbon::today()->toDateString()) }}">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download Report as PDF
                  </button>
                </form>
                <form id="email-report-form" action="{{ route('attendance-report-email', ['emp_code' => $searched_emp_code ?? old('emp_code')]) }}" method="POST">
                  @csrf
                  <input type="hidden" name="start_date" value="{{ $report_start_date ?? ($searched_start_date ?? Carbon::now()->startOfMonth()->toDateString()) }}">
                  <input type="hidden" name="end_date" value="{{ $report_end_date ?? ($searched_end_date ?? Carbon::today()->toDateString()) }}">
                  <input type="hidden" name="additional_to_emails" id="additional_to_emails" value="">
                  <input type="hidden" name="cc_emails" id="cc_emails" value="">
                  <button
                    type="button"
                    id="email-report-btn"
                    class="btn btn-success"
                    data-hod-email="{{ $hod_email ?? '' }}"
                  >
                    <i class="fas fa-envelope"></i> Email Report to HOD
                  </button>
                </form>
              </div>
            </div>

            <div class="row gy-4 stats">
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="0" class="purecounter late-days"></span>
                  <p>Late Coming Days</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="0" class="purecounter late-mins"></span>
                  <p>Late Coming Mins</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="0" class="purecounter early-mins"></span>
                  <p>Early Off Mins</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="0" class="purecounter total-mins"></span>
                  <p>Total Mins Effect</p>
                </div>
              </div>
            </div>

            <div class="row gy-4 stats mt-2">
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span>{{ $leave_counts['casual'] ?? 0 }}</span>
                  <p>Casual Leaves</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span>{{ $leave_counts['medical'] ?? 0 }}</span>
                  <p>Medical Leaves</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span>{{ $leave_counts['annual'] ?? 0 }}</span>
                  <p>Annual Leaves</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="stats-item text-center w-100 h-100">
                  <span>{{ $leave_counts['outdoor_duty'] ?? 0 }}</span>
                  <p>Outdoor Duty</p>
                </div>
              </div>
            </div>

            <table class="table mt-3 mb-5" id="employee-attendance-table">
              <thead>
                <tr>
                  <th>Sr#</th>
                  <th>Date</th>
                  <th>Time-In/Out</th>
                  <th>Late Mins</th>
                  <th>Early Mins</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($attendance as $record)
                  <tr class="{{ ($record['late_minutes'] ?? 0) >= 10 ? 'late-row' : '' }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ Carbon::parse($record['at_date'])->format('D, j M') }}</td>
                    <td>
                      @if ($record['is_sunday'] || $record['is_holiday'])
                        <span class="badge badge-info">
                          {{ $record['is_holiday'] ? 'Holiday' : 'Sunday' }}
                        </span>
                      @else
                        @if (!empty($record['time_logs']))
                          @foreach ($record['time_logs'] as $log)
                            @if ($log['timein'] && $log['timeout'])
                              {{ Carbon::parse($log['timein'])->format('H:i') }} / {{ Carbon::parse($log['timeout'])->format('H:i') }}<br>
                            @elseif ($log['timein'])
                              {{ Carbon::parse($log['timein'])->format('H:i') }} / --:--<br>
                              @php $isTimeIn = true; @endphp
                            @endif
                          @endforeach
                        @else
                          <span class="badge badge-danger">Not timed in</span>
                        @endif
                      @endif
                    </td>
                    <td>
                      @if (!$record['is_sunday'] && !$record['is_holiday'])
                        @if (($record['late_minutes'] ?? 0) >= 10)
                          {{ intval($record['late_minutes'] ?? 0) }} mins
                        @else
                          -
                        @endif
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      @if (!$record['is_sunday'] && !$record['is_holiday'])
                        @if (($record['early_minutes'] ?? 0) > 0)
                          {{ round($record['early_minutes']) }} mins
                        @else
                          -
                        @endif
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      @if ($record['is_sunday'] || $record['is_holiday'])
                        <span class="badge badge-info">
                          {{ $record['is_holiday'] ? 'Holiday' : 'Sunday' }}
                        </span>
                      @elseif ($record['leave_type'])
                        <span class="badge badge-success">{{ $record['leave_type'] }}</span>
                      @elseif(empty($record['time_logs']))
                        <span class="badge badge-danger">Absent</span>
                      @else
                        <span class="badge badge-success">Present</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  function sumLateAndEarlyMinutes() {
    let totalLate = 0;
    let totalEarly = 0;
    let totalLateDays = 0;

    const rows = document.querySelectorAll("#employee-attendance-table tbody tr");
    rows.forEach(row => {
      let lateCell = row.cells[3];
      let earlyCell = row.cells[4];

      if (lateCell) {
        let lateText = lateCell.innerText.trim();
        let lateValue = parseInt(lateText);
        if (!isNaN(lateValue)) {
          totalLate += lateValue;
        }
        if (lateValue >= 10) {
          totalLateDays += 1;
        }
      }

      if (earlyCell) {
        let earlyText = earlyCell.innerText.trim();
        let earlyValue = parseInt(earlyText);
        if (!isNaN(earlyValue)) {
          totalEarly += earlyValue;
        }
      }
    });

    return {
      lateMinutes: totalLate,
      earlyMinutes: totalEarly,
      totalMins: totalLate + totalEarly,
      lateDays: totalLateDays
    };
  }

  if (document.querySelector('#employee-attendance-table tbody tr')) {
    const totals = sumLateAndEarlyMinutes();
    const lateEl = document.querySelector('.late-mins');
    const lateDaysEl = document.querySelector('.late-days');
    const earlyEl = document.querySelector('.early-mins');
    const totalEl = document.querySelector('.total-mins');

    if (lateEl) lateEl.textContent = totals.lateMinutes;
    if (lateDaysEl) lateDaysEl.textContent = totals.lateDays;
    if (earlyEl) earlyEl.textContent = totals.earlyMinutes;
    if (totalEl) totalEl.textContent = totals.totalMins;
  }

  const emailBtn = document.getElementById('email-report-btn');
  const emailForm = document.getElementById('email-report-form');
  const additionalToEmailsInput = document.getElementById('additional_to_emails');
  const ccEmailsInput = document.getElementById('cc_emails');
  if (emailBtn && emailForm) {
    emailBtn.addEventListener('click', async function () {
      const hodEmail = (this.dataset.hodEmail || '').trim();

      if (!hodEmail) {
        await Swal.fire({
          title: 'HOD Email Not Found',
          text: 'email of the HOD is not in the records.',
          icon: 'warning'
        });
        return;
      }

      const result = await Swal.fire({
        title: 'Confirm Email',
        html: `
          <p>HOD email found:<br><strong>${hodEmail}</strong></p>
          <p>Do you want to send the report?</p>
          <div class="swal-email-fields">
            <label for="swal-to-emails">Additional To emails (optional)</label>
            <textarea id="swal-to-emails" class="swal2-textarea" placeholder="Enter emails separated by comma or new line"></textarea>
            <label for="swal-cc-emails">CC emails (optional)</label>
            <textarea id="swal-cc-emails" class="swal2-textarea" placeholder="Enter emails separated by comma or new line"></textarea>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Send',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
          const parseEmails = (value) => {
            if (!value || !value.trim()) {
              return [];
            }
            return [...new Set(
              value
                .split(/[\s,;]+/)
                .map(e => e.trim())
                .filter(Boolean)
            )];
          };

          const additionalTo = parseEmails(document.getElementById('swal-to-emails')?.value || '');
          const cc = parseEmails(document.getElementById('swal-cc-emails')?.value || '');

          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          const invalidTo = additionalTo.find(email => !emailRegex.test(email));
          if (invalidTo) {
            Swal.showValidationMessage(`Invalid additional To email: ${invalidTo}`);
            return false;
          }

          const invalidCc = cc.find(email => !emailRegex.test(email));
          if (invalidCc) {
            Swal.showValidationMessage(`Invalid CC email: ${invalidCc}`);
            return false;
          }

          return {
            additionalTo: additionalTo.join(','),
            cc: cc.join(',')
          };
        }
      });

      if (result.isConfirmed) {
        if (additionalToEmailsInput) {
          additionalToEmailsInput.value = result.value?.additionalTo || '';
        }
        if (ccEmailsInput) {
          ccEmailsInput.value = result.value?.cc || '';
        }
        emailForm.submit();
      }
    });
  }

  const deptEmailBtn = document.getElementById('dept-email-report-btn');
  const deptEmailForm = document.getElementById('dept-email-report-form');
  const deptToEmailsInput = document.getElementById('dept_to_emails');
  const deptCcEmailsInput = document.getElementById('dept_cc_emails');

  if (deptEmailBtn && deptEmailForm) {
    deptEmailBtn.addEventListener('click', async function () {
      const result = await Swal.fire({
        title: 'Send Department Report',
        html: `
          <div class="swal-email-fields">
            <label for="swal-dept-to-emails">To emails</label>
            <textarea id="swal-dept-to-emails" class="swal2-textarea" placeholder="Enter emails separated by comma or new line"></textarea>
            <label for="swal-dept-cc-emails">CC emails (optional)</label>
            <textarea id="swal-dept-cc-emails" class="swal2-textarea" placeholder="Enter emails separated by comma or new line"></textarea>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Queue Email',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
          const parseEmails = (value) => {
            if (!value || !value.trim()) {
              return [];
            }
            return [...new Set(
              value
                .split(/[\s,;]+/)
                .map(e => e.trim())
                .filter(Boolean)
            )];
          };

          const toEmails = parseEmails(document.getElementById('swal-dept-to-emails')?.value || '');
          const ccEmails = parseEmails(document.getElementById('swal-dept-cc-emails')?.value || '');

          if (toEmails.length === 0) {
            Swal.showValidationMessage('At least one To email is required.');
            return false;
          }

          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          const invalidTo = toEmails.find(email => !emailRegex.test(email));
          if (invalidTo) {
            Swal.showValidationMessage(`Invalid To email: ${invalidTo}`);
            return false;
          }

          const invalidCc = ccEmails.find(email => !emailRegex.test(email));
          if (invalidCc) {
            Swal.showValidationMessage(`Invalid CC email: ${invalidCc}`);
            return false;
          }

          return {
            to: toEmails.join(','),
            cc: ccEmails.join(',')
          };
        }
      });

      if (result.isConfirmed) {
        if (deptToEmailsInput) {
          deptToEmailsInput.value = result.value?.to || '';
        }
        if (deptCcEmailsInput) {
          deptCcEmailsInput.value = result.value?.cc || '';
        }
        deptEmailForm.submit();
      }
    });
  }
@endpush
