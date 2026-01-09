@extends('layouts.app')

@php
  use Carbon\Carbon;
@endphp

@push('styles')
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
.leave-link {
  color: #2196f3;
  font-size: 14px;
  margin-left: 15px;
}
.leave-link:hover {
  color: rgb(3 108 191);
}
td {
  font-size: 14px;
}
@media (max-width: 768px) {
  .portfolio-details .portfolio-info {
    padding: 0 15px;
  }
}
{{-- Toggle Switch --}}
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 21px;
  }
  
  .switch input { 
    opacity: 0;
    width: 0;
    height: 0;
  }
  
  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
  }
  
  .slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
  }
  
  input:checked + .slider {
    background-color: #2196F3;
  }
  
  input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
  }
  
  input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
  }
  
  /* Rounded sliders */
  .slider.round {
    border-radius: 34px;
  }
  
  .slider.round:before {
    border-radius: 50%;
  }
@endpush

@section('content')
<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="portfolio-details mt-5 mb-5">
        <div class="portfolio-info pt-4">
          <h3>Subordinate Leave Approvals</h3>
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
                <th>Code</th>
                <th>Name</th>
                <th>Leave type</th>
                <th>Leave Date</th>
                <th>Stage</th>
                <th>Approve/Reject</th>
              </tr>
            </thead>
            <tbody>
                @if ($leaves && $leaves->isNotEmpty())
                  {{-- Loop through the leaves and display them in the table --}}
                  @foreach ($leaves as $leave)
                  <tr>
                    <td>{{$leave->emp_code}}</td>
                    <td>{{$leave->employee->name}}</td>
                    <td>
                        @switch($leave->leave_code)
                        @case(1)
                            Casual Leave
                            @break
    
                        @case(2)
                            Sick Leave
                            @break
    
                        @case(3)
                            Annual Leave
                            @break

                        @case(5)
                            Leave Without Pay
                            @break    
                        
                        @case(8)
                            Short Leave
                            @break
                        
                        @case(12)
                            Outdoor Duty
                            @break    
    
                        @default
                            Other
                    @endswitch
                    </td>
                    <td>{{dateAndTimeFormat($leave->from_date)}} / {{dateAndTimeFormat($leave->to_date)}}</td>
                    <td>
                        @if($leave->status == 1)
                            <span class="badge badge-info">Supervisor</span>
                        @elseif($leave->status == 3)
                            <span class="badge badge-info">HOD</span>
                        @endif        
                    </td>
                    <td>
                        <label class="switch">
                            <input type="checkbox" class="approve-leave" data-url="{{ route('approve-leave', $leave->leave_id) }}" data-urlreject="{{route('reject-leave', $leave->leave_id)}}" data-status="{{$leave->status}}" data-id="{{$leave->leave_id}}">
                            <span class="slider round"></span>
                        </label>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="6" class="text-center">No leave approvals available.</td>
                </tr>  
              @endif  
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @if($hr)
      @if ($hrApprovals && $hrApprovals->isNotEmpty())
      <div class="col-12">
        <div class="portfolio-details mt-5 mb-5">
          <div class="portfolio-info pt-4">
            <h3 class="d-flex justify-content-between align-items-center">
              HR Leave Approvals
              <button id="approveAllBtn" class="btn btn-success btn-sm">Approve All</button>
            </h3>
            <table class="table mt-5 mb-5">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Name</th>
                  <th>Leave type</th>
                  <th>Leave Date</th>
                  <th>Stage</th>
                  <th>Approve/Reject</th>
                </tr>
              </thead>
              <tbody>
                  
                      @foreach ($hrApprovals as $leave)
                        <tr>
                          <td>{{$leave->emp_code}}</td>
                          <td>{{$leave->employee->name}}</td>
                          <td>
                              @switch($leave->leave_code)
                              @case(1)
                                  Casual Leave
                                  @break
          
                              @case(2)
                                  Sick Leave
                                  @break
          
                              @case(3)
                                  Annual Leave
                                  @break

                              @case(5)
                                  Leave Without Pay
                                  @break    
                              
                              @case(8)
                                  Short Leave
                                  @break
                              
                              @case(12)
                                  Outdoor Duty
                                  @break    
          
                              @default
                                  Other
                          @endswitch
                          </td>
                          <td>{{dateAndTimeFormat($leave->from_date)}} / {{dateAndTimeFormat($leave->to_date)}}</td>
                          <td>
                              @if($leave->status == 5)
                                  <span class="badge badge-info">HR</span>
                              @endif        
                          </td>
                          <td>
                              <label class="switch">
                                  <input type="checkbox" class="approve-leave" data-url="{{ route('approve-leave', $leave->leave_id)}}" data-urlreject="{{route('reject-leave', $leave->leave_id)}}" data-status="{{$leave->status}}" data-id="{{$leave->leave_id}}">
                                  <span class="slider round"></span>
                              </label>
                          </td>
                        </tr>
                      @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @else
        <div class="col-12">
          <div class="portfolio-details mt-5 mb-5">
            <div class="portfolio-info">
              <h3>HR Leave Approvals</h3>
              <table class="table mt-5 mb-5">
                <tbody>
                  <tr>
                    <td colspan="6" class="text-center">No HR leave approvals available.</td>
                  </tr>  
                </tbody>
              </table>
            </div>
          </div>
        </div>
    @endif
    @else
    @endif  
  </div>
</div>
@endsection

@push('scripts')

$(".approve-leave").change(function () {
  var $checkbox = $(this);
  var leaveId = $checkbox.data("id");
  var status = $checkbox.data("status");
  let is_enable = $checkbox.is(':checked') ? 1 : 0;
  var $row = $checkbox.closest("tr");

  // Prevent unchecking once approved
  if (is_enable == 0) {
      $checkbox.prop("checked", true); // Re-check it immediately
      Swal.fire({
          title: "Already Approved!",
          text: "You won't be able to revert this!",
          icon: "warning",
      });
      return;
  }

  // Ask for confirmation before sending request
  Swal.fire({
      title: "Do you want to approve this leave?",
      showDenyButton: true,
      showCancelButton: true,
      icon: "question",
      confirmButtonText: "Yes, approve",
      denyButtonText: "No, reject",
  }).then((result) => {
      if (result.isConfirmed) {
        
          var url = $checkbox.data("url");
          $.ajax({
              url: url,
              type: "POST",
              data: {
                  _token: "{{ csrf_token() }}",
                  status: status,
              },
              success: function (response) {
                  if (response.success) {
                      Swal.fire({
                          title: "Leave Approved!",
                          text: "Leave has been approved successfully.",
                          icon: "success",
                      });

                      // Remove the row from the table
                      $row.fadeOut(300, function () {
                          $(this).remove();
                      });
                  } else {
                      Swal.fire({
                          title: "Failed to Update!",
                          text: response.error,
                          icon: "error",
                      });

                      $checkbox.prop("checked", false);
                  }
              },
              error: function () {
                  Swal.fire("An error occurred while updating the leave approval status.");
                  $checkbox.prop("checked", false);
              }
          });
      }
      else if (result.isDenied) {
          $.ajax({
              url: $checkbox.data("urlreject"),
              type: "POST",
              data: {
                  _token: "{{ csrf_token() }}",
                  status: status,
              },
              success: function (response) {
                  if (response.success) {
                      Swal.fire({
                          title: "Leave Rejected!",
                          text: "Leave has been rejected successfully.",
                          icon: "info",
                      });

                      // Remove the row from the table
                      $row.fadeOut(300, function () {
                          $(this).remove();
                      });
                  } else {
                      Swal.fire({
                          title: "Failed to Reject!",
                          text: response.error,
                          icon: "error",
                      });
                  }
              },
              error: function () {
                  Swal.fire("An error occurred while rejecting the leave.");
              }
          });
      }
      else {
          $checkbox.prop("checked", false);
      }
  });
});

// Approve all leaves in HR section
$("#approveAllBtn").click(function () {
    let leaveIds = [];

    // Get all HR approval checkboxes that are not yet checked
    $(".portfolio-details:contains('HR Leave Approvals') .approve-leave").each(function () {
        if (!$(this).is(':checked')) {
            leaveIds.push($(this).data("id"));
        }
    });

    if (leaveIds.length === 0) {
        Swal.fire("No pending leaves to approve.");
        return;
    }

    Swal.fire({
        title: `Approve all ${leaveIds.length} leaves?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, approve all"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('approve-all-leaves') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    leave_ids: leaveIds
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire("Approved!", response.message, "success");
                        // Remove rows of approved leaves
                        $(".portfolio-details:contains('HR Leave Approvals') .approve-leave").each(function () {
                            if (leaveIds.includes($(this).data("id"))) {
                                $(this).closest("tr").fadeOut(300, function () {
                                    $(this).remove();
                                });
                            }
                        });
                    } else {
                        Swal.fire("Failed", response.message || "Some error occurred.", "error");
                    }
                },
                error: function () {
                    Swal.fire("Error", "An error occurred while approving the leaves.", "error");
                }
            });
        }
    });
});

@endpush