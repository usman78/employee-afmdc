@extends('layouts.app')

@push('styles')
.badge-success {
  background-color: #2196f3;
}
.badge-warning {
  background-color: #ff9800;
}
.table {
  border: 1px solid #ccc;
} 
.table>:not(caption)>*>* {
  padding: .5rem 0.5rem;
}

@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details">
        <div class="portfolio-info">
          <h3>Leaves Balance</h3>
          <ul>
            <li class="mt-5">
              @if(session('success'))
                <span class="alert alert-success">{{session('success')}}</span>
              @endif  
              @if(session('error'))
                <span class="alert alert-warning">{{session('error')}}</span>
              @endif
            </li>
          </ul>
          <table class="table mt-5 mb-5">
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Balance</th>
                    <th>Pending Approval</th>
                </tr>
            </thead>
            <tbody>
                @php
                  $leaveTypes = [
                    1 => 'Casual Leave',
                    2 => 'Medical Leave',
                    3 => 'Annual Leave',
                    4 => 'Compensatory Leave',
                    // 5 => 'Leave Without Pay',
                    // 12 => 'Outdoor Duty (OD)',
                  ];

                  $pendingLeaveKeys = [
                    1 => 'casual_leave',
                    2 => 'medical_leave',
                    3 => 'annual_leave',
                    4 => 'compensatory_leave',
                    // 12 => 'od_leave',
                  ];

                  $leavesByCode = $leaves->keyBy('leav_code');
                @endphp

                @foreach ($leaveTypes as $leaveCode => $leaveType)
                    @php
                      $leave = $leavesByCode->get($leaveCode);
                      $balance = 0;
                      if ($leave) {
                        $balance = $leave->leav_open + $leave->leav_credit - $leave->leav_taken - $leave->leave_encashed;
                        // $balance = $leaveCode == 12
                          // ? $leave->leav_taken
                          // : ($leave->leav_open + $leave->leav_credit - $leave->leav_taken - $leave->leave_encashed);
                      }
                      $pendingKey = $pendingLeaveKeys[$leaveCode] ?? null;
                    @endphp
                    <tr>
                        <td>{{ $leaveType }}</td>
                        <td style="color: #2196F3"><strong>{{ $balance }}</strong></td>
                        <td>
                          {{ $pendingKey ? ($pendingLeaves[$pendingKey] ?? 0) : 0 }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Leaves Taken in Current Year</h3>
        <p class="mb-2">
          {{ \Carbon\Carbon::parse($yearlyLeaveSummary['from'])->format('j M Y') }}
          to
          {{ \Carbon\Carbon::parse($yearlyLeaveSummary['to'])->format('j M Y') }}
        </p>
        <table class="table mt-2 mb-5">
          <thead>
            <tr>
              <th>Leave Type</th>
              <th>Leaves Taken</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Outdoor Duty (OD)</td>
              <td style="color: #2196F3"><strong>{{ $yearlyLeaveSummary['od'] }}</strong></td>
            </tr>
            <tr>
              <td>Leave Without Pay</td>
              <td style="color: #2196F3"><strong>{{ $yearlyLeaveSummary['without_pay'] }}</strong></td>
            </tr>
          </tbody>
        </table>
        {{-- show total od leaves taken up until now --}}
        {{-- @php
          $totalOdLeaves = 0;
          foreach ($leaves as $leave) {
            if ($leave->leav_code == 12) {
              $totalOdLeaves = $leave->leav_taken;
              break;
            }
          }
        @endphp --}}
        {{-- <div class="row mt-3">
          <div class="col-12">
            <p class="text-center"><strong>Total Outdoor Duty (OD) Leaves Taken until now: {{ $totalOdLeaves }}</strong></p>
          </div>
        </div> --}}
          <div class="row mt-5">
            <div class="col-12 d-flex justify-content-around gap-2" style="text-align: center;">
              <a class="btn btn-primary" id="leaves-applied" href="{{route('leaves-applied', $leaves->emp_code)}}">
                <i class="fa-solid fa-check"></i>
                Leaves Status
              </a>
              <a class="btn btn-success" id="apply-leave" href="{{route('check-if-any-leave', $leaves->emp_code)}}">
                <i class="fa-solid fa-person-walking-arrow-right me-1" aria-hidden="true"></i>
                Apply Leave
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

  document.getElementById('apply-leave').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent the default link behavior
    // send ajax request to check if any leave available
    const url = this.href; // Get the URL from the link's href attribute
    $.ajax({
      url: url,
      type: 'GET',
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
        if (response.has_no_leaves) {
            console.log("he has no leaves");     // <-- no need for response.data
          if (response.has_no_short_leave) {
            console.log("he has short leaves");  
            Swal.fire({
              title: 'Unpaid Leave or Short Leave',
              text: 'You have no leaves available. You can apply for Short Leave or Unpaid Leave only.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Apply Short Leave',
              cancelButtonText: 'Unpaid Leave'
            }).then((result) => {
              if (result.isConfirmed) {
                // send a flag to disable buttons except short leave                                                                                                                      
                window.location.href = "{{ route('apply-leave-advance', ['emp_code' => $leaves->emp_code, 'shortLeaveOnly' => true])}}";
              } else {
                window.location.href = "{{ route('apply-unpaid-leave', ['emp_code' => $leaves->emp_code]) }}";
              }
            });
          } else {
            window.location.href = "{{ route('apply-unpaid-leave', ['emp_code' => $leaves->emp_code]) }}";
          }
        } else {
          window.location.href = "{{ route('apply-leave-advance', ['emp_code' => $leaves->emp_code, 'shortLeaveOnly' => false]) }}";
        }
      },
      error: function() {
        Swal.fire({
          title: 'Error',
          text: 'Could not process your request.',
          icon: 'error'
        });
      }
    });
  });  

  document.getElementById('leaves-applied').addEventListener('click', function(event) {
    event.preventDefault();
    const url = this.href;
    $.ajax({
      url: url,
      type: 'GET',
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
          Swal.fire({
            width: 900,
            draggable: true,
            title: 'Leaves Applied (current month)',
            html: response.html,
          });
        } else {
          Swal.fire({
            title: 'Error',
            text: 'Could not fetch leaves applied.',
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
