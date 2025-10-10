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
      <div class="portfolio-details mt-5">
        <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
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
                @foreach ($leaves as $leave)
                    <tr>
                        <td>{{ $leave->leave_type }}</td>
                        <td style="color: #2196F3"><strong>{{ $leave->leav_open + $leave->leav_credit - $leave->leav_taken - $leave->leave_encashed }}</strong></td>
                        <td>
                          @if ($leave->leav_code == 1)
                            {{ $pendingLeaves['casual_leave'] ?? 0 }}
                          @elseif ($leave->leav_code == 2) 
                            {{ $pendingLeaves['medical_leave'] ?? 0 }}
                          @elseif ($leave->leav_code == 3)   
                            {{ $pendingLeaves['annual_leave'] ?? 0 }}
                          @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
          <div class="row mt-5">
            <div class="col-12" style="text-align: center;">
              <a class="btn btn-primary" id="apply-leave" href="{{route('check-if-any-leave', $leaves->emp_code)}}"><i class="fa-solid fa-house-person-leave"></i>Apply For Leave</a>
              <a class="btn btn-success" id="leaves-applied" href="{{route('leaves-applied', $leaves->emp_code)}}">
                <i class="fa-solid fa-check"></i>
                Leaves Applied
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
    $.get(url).then(response => {
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

    });
  });  

  document.getElementById('leaves-applied').addEventListener('click', function(event) {
    event.preventDefault();
    const url = this.href;
    // make a post request along with emp_code
    $.get(url).then(response => {
      console.log(response);
      // handle the response as needed
      if(response.success) {
        // perhaps redirect to a new page or update the UI
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
    });
  });
  
@endpush