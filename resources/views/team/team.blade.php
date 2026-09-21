@extends('layouts.app')
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
    padding: 20px;
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
    @if ($dgm)
    <div class="row">
      <div class="col-12">
        <div class="portfolio-details mt-5 mb-5">
          <div class="portfolio-info">
            <h3>Department Wise Or Employee Specific</h3>
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
            <form action="{{ route('dgm-team-filter') }}" method="GET" class="p-3 border rounded bg-light shadow-sm">
                <div class="row gy-3 gx-2 align-items-center">
                    <div class="col-12 col-md-auto">
                        <label for="filter_type" class="form-label fw-semibold mb-1">Filter Type</label>
                        <select name="filter_type" id="filter_type" class="form-select" required>
                            <option value="">Select Filter Type</option>
                            <option value="department">Department Wise</option>
                            <option value="employee">Employee Specific</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto d-none" id="department_select_div">
                        <label for="dept_code" class="form-label fw-semibold mb-1">Department</label>
                        <select name="dept_code" id="dept_code" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->dept_code }}">{{ capitalizeWords($dept->dept_desc) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-auto d-none" id="employee_select_div">
                        <label for="emp_code" class="form-label fw-semibold mb-1">Employee Code</label>
                        <input type="text" name="emp_code" id="emp_code" class="form-control" placeholder="Enter Employee Code" required>
                    </div>

                    <div class="col-12 col-md-auto text-center text-md-start">
                        <label class="form-label fw-semibold mb-1 d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100 w-md-auto">
                            <i class="bi bi-funnel me-1"></i> Apply Filter
                        </button>
                    </div>
                </div>
            </form>

          </div>
        </div>
      </div>
    </div>
    @endif
    <div class="row">
        <div class="col-12">
        <div class="portfolio-details mt-3 mb-5">
            <div class="portfolio-info">
            <h3>Team Members</h3>
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
                    <th>Today's In</th>
                    <th>Status</th>
                    <th>Filter</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($team as $t)
                      <tr>
                        <td>{{ $t->emp_code }}</td>
                        <td>{{ capitalizeWords($t->name) }}</td>
                        @if ($t->attendance_today?->timein)
                          <td>{{date('H:i',strtotime($t->attendance_today?->timein))}}</td>
                          <td class="table-primary">Present</td>
                        @else  
                          <td>Not Signed In</td>
                          <td class="table-danger">Absent</td>
                        @endif
                        <td>
                          <div class="reportrange" data-emp="{{ $t->emp_code }}" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display: inline-block;">
                              <i class="fa fa-calendar"></i>&nbsp;
                              <span></span> <i class="fa fa-caret-down"></i>
                          </div>
                          <button class="btn btn-primary btn-sm ml-2 filter-btn" data-emp="{{ $t->emp_code }}">
                              <i class="fa fa-filter"></i> Filter 
                          </button>
                        </td>
                      </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="clear-filter text-center">
                <a href="{{ route('team' ) }}" class="btn btn-primary">Clear Filter</a>
            </div>
        </div>
        </div> 
      
  </div>
@endsection
@push('scripts')
  $(function () {
    $('.reportrange').each(function () {
        let $this = $(this);
        let start = moment().subtract(29, 'days');
        let end = moment();

        function cb(start, end) {
            $this.find('span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
            $this.data('start', start.format('YYYY-MM-DD'));
            $this.data('end', end.format('YYYY-MM-DD'));
        }

        $this.daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);
    });

    $('.filter-btn').on('click', function () {
        let empCode = $(this).data('emp');
        let $rangeDiv = $('.reportrange[data-emp="' + empCode + '"]');
        let startDate = $rangeDiv.data('start');
        let endDate = $rangeDiv.data('end');

        // Format date_range as "YYYY-MM-DD_to_YYYY-MM-DD"
        let dateRange = `${startDate}_to_${endDate}`;

        // Construct the full URL
        let url = `/attendance-filter/${empCode}/${dateRange}`;

        // Redirect
        window.location.href = url;
    });
});
  $('#filter_type').on('change', function() {
    let filterType = $(this).val();
    if (filterType === 'department') {
        $('#department_select_div').removeClass('d-none');
        $('#employee_select_div').addClass('d-none');
        $('#emp_code').removeAttr('required');
        $('#dept_code').attr('required', 'required');
    } else if (filterType === 'employee') {
        $('#employee_select_div').removeClass('d-none');
        $('#department_select_div').addClass('d-none');
        $('#dept_code').removeAttr('required');
        $('#emp_code').attr('required', 'required');
    }
});
@endpush