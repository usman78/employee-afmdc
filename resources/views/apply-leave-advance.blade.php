@extends('layouts.app')

@push('styles')
.btn-check:checked+.btn {
    color: #fff;
    background-color: #2196f3;
    border-color: #2196f3;
}
.btn-check+.btn {
    color: #2196f3;
    background-color: #fff;
    border-color: #2196f3;
}
.btn-check+.btn:hover {
    color: #fff;
    background-color: #2196f3;
}
label.btn.btn-outline-primary {
  padding: 5px 5px;
  text-wrap: nowrap;
}
.leave-btns {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 70px;
  padding: 15px;
}
.form-check {
  display: inline-block;
  padding-left: 0.5em;
}
.portfolio-info li {
    margin-bottom: 15px;
}
li.error-msg {
  margin-bottom: 0;
}
ul.error-msg{
  margin-bottom: 0;
}
.alert-warning {
  --bs-alert-color: #ffffff;
  --bs-alert-bg: #e91e1e;
}
{{-- time picker css --}}

.timerangepicker-container {
    display:flex;
    position: absolute;
  }
  .timerangepicker-label {
    display: block;
    line-height: 2em;
    background-color: #c8c8c880;
    padding-left: 1em;
    border-bottom: 1px solid grey;
    margin-bottom: 0.75em;
  }
  
  .timerangepicker-from,
  .timerangepicker-to {
    border: 1px solid grey;
    padding-bottom: 0.75em;
  }
  .timerangepicker-from {
    border-right: none;
  }
  .timerangepicker-display {
    box-sizing: border-box;
    display: inline-block;
    width: 2.5em;
    height: 2.5em;
    border: 1px solid grey;
    line-height: 2.5em;
    text-align: center;
    position: relative;
    margin: 1em 0.175em;
  }
  .timerangepicker-display .increment,
  .timerangepicker-display .decrement {
    cursor: pointer;
    position: absolute;
    font-size: 1.5em;
    width: 1.5em;
    text-align: center;
    left: 0;
  }
  
  .timerangepicker-display .increment {
    margin-top: -0.25em;
    top: -1em;
  }
  
  .timerangepicker-display .decrement {
    margin-bottom: -0.25em;
    bottom: -1em;
  }
  
  .timerangepicker-display.hour {
    margin-left: 1em;
  }
  .timerangepicker-display.period {
    margin-right: 1em;
  }
  .timerangepicker-container {
    background-color: #9bd3ff;
    color: #4e4e4e;
  }

@endpush
@section('content')
<div class="container">
    <div class="row mb-3">
      <div class="col-lg-6 justify-content-center mx-auto">
        <div class="portfolio-details">
          <div class="portfolio-info">
            <h3>Leave Application</h3>
            <div class="row">
              <form action="{{ route('store-leave-advance', $emp_code) }}" method="POST" id="leaveForm" enctype="multipart/form-data">
                @csrf
                @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul class="error-msg">
                          @foreach ($errors->all() as $error)
                            <li class="error-msg">{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
                @endif
                <ul>
                  <li class="mt-2"><strong>Select Leave Duration: </strong></li>
                  <div style="margin-bottom: 15px;" class="form-check {{ $shortLeaveOnly ? 'd-none' : '' }}">
                    <input class="btn-check" type="radio" name="leave_duration" id="full-day" value="full">
                    <label class="btn btn-outline-primary" for="full-day" style="width: 70px;">
                      Full
                    </label>
                  </div>
                  <div class="form-check mt-2 {{ $shortLeaveOnly ? 'd-none' : '' }}">
                    <input class="btn-check" type="radio" name="leave_duration" id="half-day" value="half">
                    <label class="btn btn-outline-primary" for="half-day" style="width: 70px;">
                      Half
                    </label>
                  </div>
                  <div style="margin-bottom: 15px;" class="form-check">
                    <input class="btn-check" type="radio" name="leave_duration" id="short-day" value="short">
                    <label class="btn btn-outline-primary" for="short-day" style="width: 70px;">
                      Short
                    </label>
                  </div>
                  <div id="leave-type-section">
                    <li><strong>Select Leave Type: </strong></li>
                      <div style="margin-bottom: 15px;" class="form-check mt-2">
                        <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault1" value="1">
                        <label class="btn btn-outline-primary" for="flexRadioDefault1" style="width: 75px;">
                          Casual
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault2" value="2">
                        <label class="btn btn-outline-primary" for="flexRadioDefault2" style="width: 75px;">
                          Medical
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault3" value="3">
                        <label class="btn btn-outline-primary" for="flexRadioDefault3" style="width: 75px;">
                          Annual
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault4" value="{{ \App\Models\Leave::CPL }}">
                        <label class="btn btn-outline-primary" for="flexRadioDefault4" style="width: 75px;">
                          CPL
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault5" value="5">
                        <label class="btn btn-outline-primary" for="flexRadioDefault5" style="width: 75px;">
                          W/O Pay
                        </label>
                      </div>
                      // excluding hospital employees with exception of principal from OD application
                      @if($employee->loca_code == 1 || $employee->emp_code == 325)
                      <div class="form-check">
                        <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault12" value="12">
                        <label class="btn btn-outline-primary" for="flexRadioDefault12" style="width: 75px;">
                          OD
                        </label>
                      </div>
                      @endif     
                  </div>  
                  <!-- Full Day Section -->
                  <li id="leave-date-section" class="mt-2" style="display: none;">
                    <strong>From Date: </strong>
                    <input type="text" name="leave_from_date" class="form-control pull-right mb-4" style="margin-top: 15px;">
                    <strong>To Date: </strong>
                    <input type="text" name="leave_to_date" class="form-control pull-right" style="margin-top: 15px;">
                  </li>
                  <!-- Half Day Leave -->
                  <li id="single-date-section" class="mt-2" style="display: none;">
                    <strong>Leave Date: </strong>
                    <input type="text" name="single_leave_date" class="form-control pull-right" style="margin-top: 15px;">
                  </li>

                  <!-- Short Day Section -->
                  <li id="short-time-section" class="mt-2" style="display: none;">
                    <strong>Leave Time: </strong>
                    <div class="row mt-2">
                        <div class="col">
                          <label for="start-time">Start Time:</label>
                          <input type="time" name="start_time" id="start-time" class="form-control" min="10:00">
                        </div>
                        <div class="col">
                          <label for="end-time">End Time:</label>
                          <input type="time" name="end_time" id="end-time" class="form-control" min="10:00" readonly>
                        </div>
                      </div>
                      <div id="time-warning" class="text-danger mt-2" style="display: none;"></div>
                  </li>
                
                  <!-- Half Day Section -->
                  <li id="leave-interval-section" class="mt-2" style="display: none;">
                    <strong style="margin-bottom: 15px;">Select Leave Interval: </strong><br>
                    <span class="alert alert-secondary mb-0 d-inline-block mt-3" style="font-size: 12px;">
                      <i class="bi bi-info-circle"></i> 
                      Custom Time option is only available for OD half day leave and allows you to select any custom time range within your office timings.
                    </span>
                    <div class="form-check mt-2">
                      <input class="btn-check" type="radio" name="leave_interval" id="leave-interval-first" value="1">
                      <label class="btn btn-outline-primary" for="leave-interval-first">
                        First Half
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="btn-check" type="radio" name="leave_interval" id="leave-interval-second" value="2">
                      <label class="btn btn-outline-primary" for="leave-interval-second">
                        Second Half
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="btn-check" type="radio" name="leave_interval" id="leave-interval-custom" value="3">
                      <label class="btn btn-outline-primary" for="leave-interval-custom">
                        Custom Half
                      </label>
                    </div>
                    <div class="form-check" id="leave-interval-custom-time-wrap" style="display: none;">
                      <input class="btn-check" type="radio" name="leave_interval" id="leave-interval-custom-time" value="4">
                      <label class="btn btn-outline-primary" for="leave-interval-custom-time">
                        Custom Time
                      </label>
                    </div>
                  </li>
                  <li id="half-custom-time-section" class="mt-2" style="display: none;">
                    <strong>Custom Half Day Time: </strong>
                    <div class="row mt-2">
                      <div class="col">
                        <label for="half-custom-start-time">Start Time:</label>
                        <input
                          type="time"
                          name="half_custom_start_time"
                          id="half-custom-start-time"
                          class="form-control"
                          min="{{ substr((string) $employee->st_time, 0, 5) }}"
                          max="{{ substr((string) $employee->end_time, 0, 5) }}"
                        >
                      </div>
                      <div class="col">
                        <label for="half-custom-end-time">End Time:</label>
                        <input
                          type="time"
                          name="half_custom_end_time"
                          id="half-custom-end-time"
                          class="form-control"
                          min="{{ substr((string) $employee->st_time, 0, 5) }}"
                          max="{{ substr((string) $employee->end_time, 0, 5) }}"
                          readonly
                        >
                      </div>
                    </div>
                    <div id="half-custom-time-warning" class="text-danger mt-2" style="display: none;"></div>
                  </li>
                  <li class="mt-2"><strong>Reason of Leave: </strong></li>
                  <input type="text" class="form-control mt-2" name="reason" id="reason" placeholder="Enter reason of leave" required>
                  <li style="margin-top: 20px;">
                    @if(session('success'))
                      <span class="alert alert-success">{{session('success')}}</span>
                    @endif  
                    @if(session('error'))
                      <span class="alert alert-warning">{{session('error')}}</span>
                    @endif
                  </li>
                </ul>
                <div class="row">
                    <div class="col-12 d-flex justify-content-between mt-5 gap-2">
                        <a href="{{ route('leaves', $emp_code) }}" class="btn btn-secondary leave-btns"><i class="fa-solid fa-backward"></i> Cancel</a>
                        <a class="btn btn-primary leave-btns" id="leaves-applied" data-emp-code="{{ $emp_code }}" href="{{route('leaves-applied', $emp_code)}}">
                          <i class="fa-solid fa-check"></i>
                          Leaves Status
                        </a>
                        <button type="submit" class="btn btn-success leave-btns" id="submitBtn">
                          <i class="fa-solid fa-person-walking-arrow-right me-1" aria-hidden="true"></i>
                          <span id="btnLabel">Apply Leave</span>
                          <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
              </form>
          </div>
        </div>
      </div>
      </div>
      <div class="col-lg-4 justify-content-center mx-auto">
        <div class="portfolio-details">
          <div class="portfolio-info">
            <h3>Leave Balance</h3>
            <div class="row">
              <div class="col-12">
                <ul>
                  @foreach ($employee->leavesBalance as $balance)
                    @if ($balance->leav_code == \App\Models\Leave::CASUAL)
                      <li>
                        <strong>Casual Leaves: </strong>{{$balance->leav_open + $balance->leav_credit - $balance->leav_taken - $balance->leave_encashed}}
                        ({{isset($pendingLeaves['casual_leave']) ? 'Approval Pending '.$pendingLeaves['casual_leave'] : 'No Pending Leave'}})
                      </li>
                    @elseif ($balance->leav_code == \App\Models\Leave::MEDICAL)
                      <li>
                        <strong>Medical Leaves: </strong>{{$balance->leav_open + $balance->leav_credit - $balance->leav_taken - $balance->leave_encashed}}
                        ({{isset($pendingLeaves['medical_leave']) ? 'Approval Pending '.$pendingLeaves['medical_leave'] : 'No Pending Leave'}})
                      </li>
                    @elseif ($balance->leav_code == \App\Models\Leave::ANNUAL)
                      <li>
                        <strong>Annual Leaves: </strong>{{$balance->leav_open + $balance->leav_credit - $balance->leav_taken - $balance->leave_encashed}}
                        ({{isset($pendingLeaves['annual_leave']) ? 'Approval Pending '.$pendingLeaves['annual_leave'] : 'No Pending Leave'}})
                      </li> 
                    @elseif ($balance->leav_code == \App\Models\Leave::CPL)
                      <li>
                        <strong>CPL Leaves: </strong>{{$balance->leav_open + $balance->leav_credit - $balance->leav_taken - $balance->leave_encashed}}
                        ({{isset($pendingLeaves['compensatory_leave']) ? 'Approval Pending '.$pendingLeaves['compensatory_leave'] : 'No Pending Leave'}})
                      </li>
                    @endif
                  @endforeach
                </ul>
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

    const formEl     = document.getElementById('leaveForm');
    const btn        = document.getElementById('submitBtn');
    const btnLabel   = document.getElementById('btnLabel');
    const btnSpinner = document.getElementById('btnSpinner');

    function setSubmitting(on) {
      btn.disabled = on;
      if (on) {
        btnSpinner.classList.remove('d-none');
        btnLabel.textContent = 'Submitting...';
      } else {
        btnSpinner.classList.add('d-none');
        btnLabel.textContent = 'Apply Leave';
      }
    }
    {{-- date range picker --}}
    $(function() {
      $('input[name="leave_from_date"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true
      });
    });

    $(function() {
      $('input[name="leave_to_date"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true
      });
    });

    {{-- single date picker --}}
    $(function() {
      $('input[name="single_leave_date"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true
      });
    });

    {{-- displays the appropraite input for leave --}}
    const halfDay = document.getElementById('half-day');
    const fullDay = document.getElementById('full-day');
    const shortDay = document.getElementById('short-day');

    const leaveDateSection = document.getElementById('leave-date-section');
    const leaveIntervalSection = document.getElementById('leave-interval-section');
    const shortTimeSection = document.getElementById('short-time-section');
    const halfCustomTimeSection = document.getElementById('half-custom-time-section');
    const leaveTypeSection = document.getElementById('leave-type-section');
    const singleDateSection = document.getElementById('single-date-section');
    const leaveIntervalFirst = document.getElementById('leave-interval-first');
    const leaveIntervalSecond = document.getElementById('leave-interval-second');
    const leaveIntervalCustom = document.getElementById('leave-interval-custom');
    const leaveIntervalCustomTimeWrap = document.getElementById('leave-interval-custom-time-wrap');
    const leaveIntervalCustomTime = document.getElementById('leave-interval-custom-time');
    const odLeaveType = document.querySelector('input[name="leave_type"][value="12"]');
  
    function updateDisplay() {
      if (halfDay.checked) {
        leaveIntervalSection.style.display = 'block';
        leaveDateSection.style.display = 'none';
        shortTimeSection.style.display = 'none';
        leaveTypeSection.style.display = 'block';
        singleDateSection.style.display = 'block';
        updateHalfCustomDisplay();
        leaveDateSection.disabled = true;
        shortTimeSection.disabled = true;
      } else if (fullDay.checked) {
        leaveDateSection.style.display = 'block';
        leaveIntervalSection.style.display = 'none';
        shortTimeSection.style.display = 'none';
        halfCustomTimeSection.style.display = 'none';
        leaveTypeSection.style.display = 'block';
        singleDateSection.style.display = 'none';
        resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);
        leaveIntervalSection.disabled = true;
        shortTimeSection.disabled = true;
        singleDateSection.disabled = true;
      } else if (shortDay.checked) {
        leaveIntervalSection.style.display = 'none';
        leaveDateSection.style.display = 'none';
        shortTimeSection.style.display = 'block';
        halfCustomTimeSection.style.display = 'none';
        leaveTypeSection.style.display = 'none';
        singleDateSection.style.display = 'block';
        resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);
        leaveIntervalSection.disabled = true;
        leaveDateSection.disabled = true;
        leaveTypeSection.disabled = true;
      }
       else {
        leaveIntervalSection.style.display = 'none';
        leaveDateSection.style.display = 'none';
        shortTimeSection.style.display = 'none';
        halfCustomTimeSection.style.display = 'none';
        singleDateSection.style.display = 'none';
        leaveTypeSection.style.display = 'none';
        resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);

      }
    }

    function updateHalfCustomDisplay() {
      const isOdHalfLeave = halfDay.checked && odLeaveType && odLeaveType.checked;
      leaveIntervalCustomTimeWrap.style.display = isOdHalfLeave ? 'inline-block' : 'none';

      if (!isOdHalfLeave && leaveIntervalCustomTime.checked) {
        leaveIntervalCustomTime.checked = false;
      }

      if (halfDay.checked && (leaveIntervalCustom.checked || leaveIntervalCustomTime.checked)) {
        const isCustomTime = leaveIntervalCustomTime.checked;
        halfCustomTimeSection.querySelector('strong').innerText = isCustomTime ? 'Custom OD Time: ' : 'Custom Half Day Time: ';
        halfCustomEndTime.readOnly = !isCustomTime;
        halfCustomTimeSection.style.display = 'block';
      } else {
        halfCustomTimeSection.style.display = 'none';
        halfCustomEndTime.readOnly = true;
        resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);
      }
    }
  
    // Add event listeners
    halfDay.addEventListener('change', updateDisplay);
    fullDay.addEventListener('change', updateDisplay);
    shortDay.addEventListener('change', updateDisplay);
    leaveIntervalFirst.addEventListener('change', updateHalfCustomDisplay);
    leaveIntervalSecond.addEventListener('change', updateHalfCustomDisplay);
    leaveIntervalCustom.addEventListener('change', () => {
      resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);
      updateHalfCustomDisplay();
    });
    leaveIntervalCustomTime.addEventListener('change', () => {
      resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);
      updateHalfCustomDisplay();
    });
    document.querySelectorAll('input[name="leave_type"]').forEach((leaveTypeInput) => {
      leaveTypeInput.addEventListener('change', updateHalfCustomDisplay);
    });
  
    {{-- time picker --}}
    const startTime = document.getElementById("start-time");
    const endTime = document.getElementById("end-time");
    const warning = document.getElementById("time-warning");
    const halfCustomStartTime = document.getElementById("half-custom-start-time");
    const halfCustomEndTime = document.getElementById("half-custom-end-time");
    const halfCustomWarning = document.getElementById("half-custom-time-warning");
    const employeeStartMinutes = timeToMinutes("{{ substr((string) $employee->st_time, 0, 5) }}");
    const employeeEndMinutes = timeToMinutes("{{ substr((string) $employee->end_time, 0, 5) }}");
    const halfDayDurationMinutes = Math.round((employeeEndMinutes - employeeStartMinutes) / 2);
  
    function timeToMinutes(t) {
      const [hours, minutes] = t.split(":").map(Number);
      return hours * 60 + minutes;
    }
  
    function minutesToTime(minutes) {
      const h = Math.floor(minutes / 60).toString().padStart(2, "0");
      const m = (minutes % 60).toString().padStart(2, "0");
      return `${h}:${m}`;
    }

    function resetCalculatedTime(startInput, endInput, warningEl) {
      startInput.value = "";
      endInput.value = "";
      warningEl.style.display = "none";
      warningEl.innerText = "";
    }

    function bindCalculatedTime(startInput, endInput, warningEl, durationMinutes, validation) {
      startInput.addEventListener("change", function () {
        warningEl.style.display = "none";
        warningEl.innerText = "";
        const start = startInput.value;

        if (!start) {
          endInput.value = "";
          return;
        }

        const startMinutes = timeToMinutes(start);
        const validationMessage = validation(startMinutes, durationMinutes);

        if (validationMessage) {
          warningEl.style.display = "block";
          warningEl.innerText = validationMessage;
          startInput.value = "";
          endInput.value = "";
          return;
        }

        if (leaveIntervalCustomTime.checked) {
          return;
        }

        endInput.value = minutesToTime(startMinutes + durationMinutes);
      });
    }

    function validateCustomTimeRange() {
      if (!leaveIntervalCustomTime.checked) {
        return true;
      }

      halfCustomWarning.style.display = "none";
      halfCustomWarning.innerText = "";

      if (!halfCustomStartTime.value || !halfCustomEndTime.value) {
        return true;
      }

      const customStartMinutes = timeToMinutes(halfCustomStartTime.value);
      const customEndMinutes = timeToMinutes(halfCustomEndTime.value);

      let message = "";
      if (customStartMinutes < employeeStartMinutes) {
        message = "Custom OD time cannot start before your office start time.";
      } else if (customEndMinutes > employeeEndMinutes) {
        message = "Custom OD time must end within your office timing.";
      } else if (customEndMinutes <= customStartMinutes) {
        message = "Custom OD end time must be after the start time.";
      }

      if (message) {
        halfCustomWarning.style.display = "block";
        halfCustomWarning.innerText = message;
        return false;
      }

      return true;
    }
  
    bindCalculatedTime(startTime, endTime, warning, 120, (startMinutes, durationMinutes) => {
      if (startMinutes <= 600) {
        return "Start time must be after 10:00 AM or later.";
      }

      if (startMinutes + durationMinutes >= 1440) {
        return "Leave time cannot go past midnight.";
      }

      return null;
    });

    bindCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning, halfDayDurationMinutes, (startMinutes, durationMinutes) => {
      if (startMinutes < employeeStartMinutes) {
        return "Custom half leave cannot start before your office start time.";
      }

      if (!leaveIntervalCustomTime.checked && startMinutes + durationMinutes > employeeEndMinutes) {
        return "Custom half leave must end within your office timing.";
      }

      return null;
    });

    halfCustomEndTime.addEventListener("change", validateCustomTimeRange);

    // Initial state
    updateDisplay();

  formEl.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validateCustomTimeRange()) {
      return;
    }
    setSubmitting(true);

    try {
      const previewRes = await fetch("{{ route('leave.preview') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        accept: 'application/json',
        body: new FormData(formEl)
      });
      // Handle session expiration BEFORE parsing JSON
      if (previewRes.status === 401 || previewRes.status === 419) {
          await Swal.fire(
              'Session Expired',
              'Your session has expired. Please login again.',
              'warning'
          );
          window.location.href = "{{ route('login') }}";
          return;
      }
      const preview = await previewRes.json();
      if (preview.sandwich === true) {
        const r = await Swal.fire({
          title: 'Heads up!',
          text: `Your rest day (${preview.rest_day}) will also be counted as leave due to sandwich leave policy.`,
          icon: 'info',
          showCancelButton: true,
          confirmButtonText: 'Apply Leave'
        });
        if (!r.isConfirmed) { setSubmitting(false); return; }
      }

      // New FormData for the actual save (avoids any reuse issues)
      const saveRes = await fetch("{{ route('store-leave-advance', $emp_code) }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        'Accept': 'application/json',
        body: new FormData(formEl)
      });

      const data = await saveRes.json();

      if (data.error) {
        console.log(data.error);
        await Swal.fire('Error', data.error, 'error');
        return;
      }

      await Swal.fire('Success', data.message, 'success');
      formEl.reset();
      updateDisplay();
    } catch (err) {
      console.error(err);
      await Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
    } finally {
      setSubmitting(false); // always restore button for next submission
      formEl.reset();
      updateDisplay();
    }
  });

  document.getElementById('leaves-applied').addEventListener('click', async function(event) {
    event.preventDefault();
    const url = this.href;
    const empCodeDefault = (this.dataset.empCode || '').trim();
    const now = new Date();
    const monthDefault = now.toISOString().slice(0, 7);

    const { value: formValues } = await Swal.fire({
      title: 'Leaves Applied Report',
      html: `
        <div class="text-start">
          <label for="swal-emp-code" class="form-label">Employee Code</label>
          <input id="swal-emp-code" class="form-control" value="${empCodeDefault}" disabled>
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
        console.log(response);
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
