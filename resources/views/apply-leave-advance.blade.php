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
    <div class="row">
      <div class="col-lg-6 justify-content-center mx-auto">
        <div class="portfolio-details mt-5">
          <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
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
                  {{-- <li><strong>Employee Code: </strong>{{$emp_code}}</li>
                  <li><strong>Employee Name: </strong>{{capitalizeWords($employee->name)}}</li> --}}
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
                  <div style="margin-bottom: 15px;" class="form-check">
                    <input class="btn-check" type="radio" name="leave_duration" id="short-day" value="short">
                    <label class="btn btn-outline-primary" for="short-day" style="width: 70px;">
                      Short
                    </label>
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
                  </li>
                  <div id="leave-type-section">
                    <li><strong>Select Leave Type: </strong></li>
                      <div style="margin-bottom: 15px;" class="form-check mt-2">
                        <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault1" value="1">
                        <label class="btn btn-outline-primary" for="flexRadioDefault1" style="width: 70px;">
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
                        <label class="btn btn-outline-primary" for="flexRadioDefault3" style="width: 70px;">
                          Annual
                        </label>
                      </div>
                      <div class="form-check">
                        <input class="btn-check" type="radio" name="leave_type" id="flexRadioDefault4" value="5">
                        <label class="btn btn-outline-primary" for="flexRadioDefault4" style="width: 70px;">
                          W/O Pay
                        </label>
                      </div>    
                  </div>  
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
      <div class="col-lg-4 justify-content-center mx-auto">
        <div class="portfolio-details mt-5">
          <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
            <h3>Leave Balance</h3>
            <div class="row">
              <div class="col-12">
                <ul>
                  @foreach ($employee->leavesBalance as $balance)
                    @if ($balance->leav_code == 1)
                      <li>
                        <strong>Casual Leaves: </strong>{{$balance->leav_open + $balance->leav_credit - $balance->leav_taken - $balance->leave_encashed}}
                        ({{isset($pendingLeaves['casual_leave']) ? 'Approval Pending '.$pendingLeaves['casual_leave'] : 'No Pending Leave'}})
                      </li>
                    @elseif ($balance->leav_code == 2)
                      <li>
                        <strong>Medical Leaves: </strong>{{$balance->leav_open + $balance->leav_credit - $balance->leav_taken - $balance->leave_encashed}}
                        ({{isset($pendingLeaves['medical_leave']) ? 'Approval Pending '.$pendingLeaves['medical_leave'] : 'No Pending Leave'}})
                      </li>
                    @elseif ($balance->leav_code == 3)
                      <li>
                        <strong>Annual Leaves: </strong>{{$balance->leav_open + $balance->leav_credit - $balance->leav_taken - $balance->leave_encashed}}
                        ({{isset($pendingLeaves['annual_leave']) ? 'Approval Pending '.$pendingLeaves['annual_leave'] : 'No Pending Leave'}})
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
    const leaveTypeSection = document.getElementById('leave-type-section');
    const singleDateSection = document.getElementById('single-date-section');
  
    function updateDisplay() {
      if (halfDay.checked) {
        leaveIntervalSection.style.display = 'block';
        leaveDateSection.style.display = 'none';
        shortTimeSection.style.display = 'none';
        leaveTypeSection.style.display = 'block';
        singleDateSection.style.display = 'block';
        leaveDateSection.disabled = true;
        shortTimeSection.disabled = true;
      } else if (fullDay.checked) {
        leaveDateSection.style.display = 'block';
        leaveIntervalSection.style.display = 'none';
        shortTimeSection.style.display = 'none';
        leaveTypeSection.style.display = 'block';
        singleDateSection.style.display = 'none';
        leaveIntervalSection.disabled = true;
        shortTimeSection.disabled = true;
        singleDateSection.disabled = true;
      } else if (shortDay.checked) {
        leaveIntervalSection.style.display = 'none';
        leaveDateSection.style.display = 'none';
        shortTimeSection.style.display = 'block';
        leaveTypeSection.style.display = 'none';
        singleDateSection.style.display = 'block';
        leaveIntervalSection.disabled = true;
        leaveDateSection.disabled = true;
        leaveTypeSection.disabled = true;
      }
       else {
        leaveIntervalSection.style.display = 'none';
        leaveDateSection.style.display = 'none';

      }
    }
  
    // Add event listeners
    halfDay.addEventListener('change', updateDisplay);
    fullDay.addEventListener('change', updateDisplay);
    shortDay.addEventListener('change', updateDisplay);
  
    // Initial state
    updateDisplay();

    {{-- time picker --}}
    const startTime = document.getElementById("start-time");
    const endTime = document.getElementById("end-time");
    const warning = document.getElementById("time-warning");
  
    function timeToMinutes(t) {
      const [hours, minutes] = t.split(":").map(Number);
      return hours * 60 + minutes;
    }
  
    function minutesToTime(minutes) {
      const h = Math.floor(minutes / 60).toString().padStart(2, "0");
      const m = (minutes % 60).toString().padStart(2, "0");
      return `${h}:${m}`;
    }
  
    startTime.addEventListener("change", function () {
      warning.style.display = "none";
      const start = startTime.value;
  
      if (!start) {
        endTime.value = "";
        return;
      }
  
      const startMinutes = timeToMinutes(start);
  
      if (startMinutes <= 600) { // 10:00 AM = 600 minutes
        warning.style.display = "block";
        warning.innerText = "Start time must be after 10:00 AM or later.";
        startTime.value = "";
        endTime.value = "";
        return;
      }
  
      const endMinutes = startMinutes + 120; // 2 hours later
  
      if (endMinutes >= 1440) { // past midnight
        warning.style.display = "block";
        warning.innerText = "Leave time cannot go past midnight.";
        startTime.value = "";
        endTime.value = "";
        return;
      }
  
      endTime.value = minutesToTime(endMinutes);
    });

  
  document.getElementById('leaveForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const form = e.target;
      const formData = new FormData(form);

      fetch("{{ route('leave.preview') }}", {
          method: 'POST',
          headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
          },
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          if (data.sandwich === true) {
              Swal.fire({
                  title: 'Heads up!',
                  text: `Your rest day (${data.rest_day}) will also be counted as leave due to sandwich leave policy.`,
                  icon: 'info',
                  confirmButtonText: 'OK'
              }).then(() => {
                  submitLeave(formData); // call actual save function
              });
          } else {
              submitLeave(formData);
          }
      })
      .catch(error => {
          console.error('Error:', error);
      });

      function submitLeave(formData) {
          fetch("{{ route('store-leave-advance', $emp_code) }}", {
              method: 'POST',
              headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
              },
              body: formData
          })
          .then(response => response.json())
          .then(data => {
              Swal.fire('Success', data.message, 'success');
              document.getElementById('leaveForm').reset();
          });
      }
  });


@endpush
