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
  padding: 5px 2px;
}
.form-check {
  display: inline-block;
  padding-left: 0.5em;
}
li {
  margin-bottom: 15px;
  list-style-type: none;
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

@endpush
@section('content')
<div class="container">
    <div class="row mb-3">
      <div class="col-lg-12 justify-content-center mx-auto">
        <div class="portfolio-details">
          <div class="portfolio-info">
            <h3>OD Leave Application</h3>
            <div class="row">
              <form action="{{ route('store-od-leave', $emp_code) }}" 
                method="POST" enctype="multipart/form-data" 
                id="odLeaveForm">
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
                  <div style="margin-bottom: 15px;" class="form-check">
                    <input class="btn-check" type="radio" name="leave_duration" id="full-day" value="full">
                    <label class="btn btn-outline-primary" for="full-day" style="width: 70px;">
                      Full
                    </label>
                  </div>
                  <div class="form-check mt-2">
                    <input class="btn-check" type="radio" name="leave_duration" id="half-day" value="half">
                    <label class="btn btn-outline-primary" for="half-day" style="width: 70px;">
                      Half
                    </label>
                  </div>
                
                  <!-- Full Day Section -->
                  <li id="leave-date-section" class="mt-2" style="display: none;">
                    <strong>From Date: </strong>
                    <input type="text" name="leave_from_date" class="form-control pull-right mb-4" style="margin-top: 15px;">
                    <strong>To Date: </strong>
                    <input type="text" name="leave_to_date" class="form-control pull-right" style="margin-top: 15px;">
                  </li>
                  <!-- Single Date Leave -->
                  <li id="single-date-section" class="mt-2" style="display: none;">
                    <strong>Leave Date: </strong>
                    <input type="text" name="single_leave_date" class="form-control pull-right" style="margin-top: 15px;">
                  </li>
                
                  <!-- Half Day Section -->
                  <li id="leave-interval-section" class="mt-2" style="display: none;">
                    <strong>Select Leave Interval: </strong>
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
                  <div class="col-12 justify-content-center text-center mt-5">
                      <a href="{{ route('leaves', $emp_code) }}" class="btn btn-primary mt-3"><i class="fa-solid fa-backward"></i> Back</a>
                      <button type="submit" class="btn btn-success mt-3"><i class="fa-solid fa-person-walking-arrow-right"></i> Apply Leave</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection

@push('scripts')
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

    {{-- displays the appropriate input for leave --}}
    const halfDay = document.getElementById('half-day');
    const fullDay = document.getElementById('full-day');

    const leaveDateSection = document.getElementById('leave-date-section');
    const leaveIntervalSection = document.getElementById('leave-interval-section');
    const halfCustomTimeSection = document.getElementById('half-custom-time-section');
    const singleDateSection = document.getElementById('single-date-section');
    const leaveIntervalFirst = document.getElementById('leave-interval-first');
    const leaveIntervalSecond = document.getElementById('leave-interval-second');
    const leaveIntervalCustom = document.getElementById('leave-interval-custom');

    {{-- Declare these variables FIRST before they're used in functions --}}
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
  
    function updateDisplay() {
      if (halfDay.checked) {
        leaveIntervalSection.style.display = 'block';
        leaveDateSection.style.display = 'none';
        singleDateSection.style.display = 'block';
        leaveDateSection.disabled = true;
        updateHalfCustomDisplay();
      } else if (fullDay.checked) {
        leaveDateSection.style.display = 'block';
        leaveIntervalSection.style.display = 'none';
        singleDateSection.style.display = 'none';
        leaveIntervalSection.disabled = true;
        singleDateSection.disabled = true;
        halfCustomTimeSection.style.display = 'none';
        resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);
      } else {
        leaveIntervalSection.style.display = 'none';
        leaveDateSection.style.display = 'none';
        halfCustomTimeSection.style.display = 'none';
        singleDateSection.style.display = 'none';
        resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);
      }
    }

    function updateHalfCustomDisplay() {
      if (leaveIntervalCustom.checked) {
        halfCustomTimeSection.style.display = 'block';
      } else {
        halfCustomTimeSection.style.display = 'none';
        resetCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning);
      }
    }
  
    // Add event listeners
    halfDay.addEventListener('change', updateDisplay);
    fullDay.addEventListener('change', updateDisplay);
    leaveIntervalFirst.addEventListener('change', updateHalfCustomDisplay);
    leaveIntervalSecond.addEventListener('change', updateHalfCustomDisplay);
    leaveIntervalCustom.addEventListener('change', updateHalfCustomDisplay);

    // Initial state
    updateDisplay();

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

        endInput.value = minutesToTime(startMinutes + durationMinutes);
      });
    }

    bindCalculatedTime(halfCustomStartTime, halfCustomEndTime, halfCustomWarning, halfDayDurationMinutes, (startMinutes, durationMinutes) => {
      if (startMinutes < employeeStartMinutes) {
        return "Custom half leave cannot start before your office start time.";
      }

      if (startMinutes + durationMinutes > employeeEndMinutes) {
        return "Custom half leave must end within your office timing.";
      }

      return null;
    });

    // Initial state
    updateDisplay();

@endpush
