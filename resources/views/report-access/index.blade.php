@extends('layouts.app')
@push('cdn-styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />    
@endpush
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Report Access Control</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('report-access.index') }}" class="row align-items-end">
                <div class="col-md-8 col-lg-6">
                    <label for="emp_code" class="form-label">Employee</label>
                    <select class="form-control" id="emp_code" name="emp_code" required>
                        <option value="">Select an employee</option>
                        @foreach ($employees as $candidate)
                            <option value="{{ $candidate->emp_code }}" @selected((string) optional($employee)->emp_code === (string) $candidate->emp_code)>
                                {{ $candidate->emp_code }} - {{ $candidate->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary">Manage Access</button>
                </div>
            </form>
        </div>
    </div>

    @if ($employee)
        <form method="POST" action="{{ route('report-access.update', $employee->emp_code) }}">
            @csrf
            @method('PUT')

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h2 class="h5 mb-1">{{ $employee->name }}</h2>
                    <div class="text-muted">{{ $employee->emp_code }}</div>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    Save Access
                </button>
            </div>

            <div class="row">
                @foreach ($reportsByGroup as $group => $reports)
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">{{ $group }} Reports</h6>
                            </div>
                            <div class="card-body">
                                @foreach ($reports as $key => $report)
                                    <div class="custom-control custom-switch mb-3">
                                        <input
                                            class="custom-control-input"
                                            type="checkbox"
                                            id="report-{{ $key }}"
                                            name="reports[{{ $key }}]"
                                            value="1"
                                            @checked($permissionStates[$key]['allowed'] ?? false)
                                        >
                                        <label class="custom-control-label" for="report-{{ $key }}">
                                            {{ $report['label'] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </form>
    @endif
</div>
@endsection
@push('cdn-scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>    
@endpush
@push('scripts')
    // initialize select2 for employee passed from controller
    $('#emp_code').select2({
        placeholder: "Select an employee",
        allowClear: true,
        width: '100%'
    });
@endpush
