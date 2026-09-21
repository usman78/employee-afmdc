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
            <h3>Attendance Discrepancy Report</h3>
            <p class="text-muted mb-2">Report includes job/roster timing and attendance information against it.</p>
            <form method="post" action="{{route('att-discrepency')}}">
                @csrf
                <div class="row">
                    <div class="col-md-12 form-group mt-3">
                        <label for="emp_code">Employee Code</label>
                        <select class="form-control" id="emp_code" name="emp_code">
                            @if(old('emp_code'))
                                <option value="{{ old('emp_code') }}" selected>{{ old('emp_code') }}</option>
                            @endif
                        </select>
                    </div>
                <button type="submit" class="btn btn-primary mt-3"><span class="fas fa-sync-alt me-2"></span>Generate Report</button>
            </form>
        </div>
    </div>  
</div>
@endsection
@push('cdn-scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>    
@endpush
@push('scripts')

    // initialize select2 for employee search
    $('#emp_code').select2({
        placeholder: "Search by employee code or name",
        allowClear: true,
        width: '100%',
        minimumInputLength: 2,
        ajax: {
            url: "{{ route('leave-report-employee-search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return data;
            },
            cache: true
        }
    });
@endpush
