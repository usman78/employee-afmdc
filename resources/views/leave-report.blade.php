@extends('layouts.app')
@push('cdn-styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />    
@endpush
@push('styles')
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 6px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 33px;
    }
    .select2-container .select2-selection--single {
        height: 38px;
    }
    .fa-caret-down {
        position: absolute;
        right: 20px;
        top: 42px;
    }
    .form-control#filter {
        background: #4e73df;
        color: #fff;
    }
@endpush
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h3>Leave Report</h3>
            <form method="post" action="{{ route('leave-report-data') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 form-group mt-3">
                        <label for="dateRange">Date Range</label>
                        <div id="reportrange" style="background: #fff; cursor: pointer; padding: 6px 10px; border: 1px solid #ccc; width: 100%; border-radius: 6px;">
                            <i class="fa fa-calendar"></i>&nbsp;
                            <span></span> <i class="fa fa-caret-down"></i>
                            <input type="hidden" name="start_date" id="start_date">
                            <input type="hidden" name="end_date" id="end_date">
                        </div>
                    </div>
                    {{-- Select filter option --}}
                    <div class="col-md-6 form-group mt-3">
                        <label for="filter">Filter By</label>
                        <select class="form-control" id="filter" name="filter" required>
                            <option value="">Select Filter</option>
                            <option value="department">Department</option>
                            <option value="designation">Designation</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group mt-3">
                        <label for="department">Department</label>
                        <select class="form-control" id="department" name="department" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->dept_code }}">{{ capitalizeWords($department->dept_desc) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 form-group mt-3">
                        <label for="employee">Employee (Optional)</label>
                        <select class="form-control" id="designation" name="designation">
                            <option value="">All Employees</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->desg_code }}">{{ $designation->desg_short }}</option>
                            @endforeach
                        </select>
                    </div>
                <button type="submit" class="btn btn-primary mt-3">Generate Report</button>
            </form>
        </div>
    </div>  
</div>
@endsection
@push('cdn-scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>    
@endpush
@push('scripts')

    // script to handle filter by department or designation
    $('#filter').on('change', function() {
        var filter = $(this).val();
        if (filter === 'department') {
            $('#department').prop('disabled', false);
            $('#designation').prop('disabled', true);
        } else if (filter === 'designation') {
            $('#department').prop('disabled', true);
            $('#designation').prop('disabled', false);
        } else {
            $('#department').prop('disabled', false);
            $('#designation').prop('disabled', false);
        }
    });
    // initialize select2 for department and designation dropdowns
    $('#designation').select2({
        placeholder: "Select an employee designation",
        allowClear: true,
        width: '100%'
    });
    $('#department').select2({
        placeholder: "Select a department",
        allowClear: true,
        width: '100%'
    });
    // initialize date range picker
    $(function () {
        var start = moment().subtract(29, 'days');
        var end = moment();
        function cb(start, end) {
            $('#reportrange span').html(
                start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY')
            );
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        }
        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'),
                            moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
        cb(start, end); // initial load
    });
@endpush