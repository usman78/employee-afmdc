@extends('layouts.app') {{-- Adjust if you're using a different layout --}}

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">IT Service Request Form</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('service-requests.store') }}" method="POST">
        @csrf

        <input type="hidden" name="REQUESTER_ID" value="{{ $user }}">
        <input type="hidden" name="DEPARTMENT_ID" value="{{ $dept }}">

        <div class="mb-3">
            <label for="service_type" class="form-label">Service Type</label>
            <select class="form-select" id="service_type" name="SERVICE_TYPE" required>
                <option value="">-- Select Service Type --</option>
                <option value="1">Software Service</option>
                <option value="2">Hardware Service</option>
            </select>
        </div>

        <div class="mb-3" id="software_section">
            <label for="software_job" class="form-label">Software Service</label>
            <select class="form-select" id="software_job">
                <option value="">-- Select Job Type --</option>
                <option value="1">Data Updation / Correction</option>
                <option value="2">User Creation / Password reset</option>
                <option value="3">New System / Sub system development</option>
                <option value="4">Modification in existing data entry Form</option>
                <option value="5">Modification in existing Report</option>
                <option value="6">New data entry Form Development</option>
                <option value="7">New Report Development</option>
                <option value="8">New Email Account</option>
                <option value="9">Special Web Site Permission</option>
                <option value="10">Windows Installation</option>
                <option value="11">Other Software Service</option>
            </select>
        </div>

        <div class="mb-3" id="hardware_section">
            <label for="hardware_job" class="form-label">Hardware Service</label>
            <select class="form-select" id="hardware_job">
                <option value="">-- Select Job Type --</option>
                <option value="12">Internet Access (Mobile)</option>
                <option value="13">Projector Deployment</option>
                <option value="14">Internet Access For Office</option>
                <option value="15">Printer Services / Installation</option>
                <option value="16">Windows Installation</option>
                <option value="17">Sharing & Mapping</option>
                <option value="18">Wireless Access Point Deployment</option>
                <option value="19">Hardware Maintenance</option>
                <option value="20">Other Hardware Service</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="priority" class="form-label">Priority</label>
            <select class="form-select" id="priority" name="PRIORITY" required>
                <option value="">-- Select Priority --</option>
                <option value="NORMAL">Normal</option>
                <option value="URGENT">Urgent</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Brief Description of the Job</label>
            <textarea class="form-control" id="description" name="DESCRIPTION" rows="4" required></textarea>
        </div>

        <input type="hidden" name="JOB_TYPE" id="job_type_hidden">

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>There were some problems with your input:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>
@endsection
@push('scripts')

    document.getElementById('software_section').style.display = 'none';
    document.getElementById('hardware_section').style.display = 'none';

    document.getElementById('software_job').required = false;
    document.getElementById('hardware_job').required = false;

    const jobTypeHidden = document.getElementById('job_type_hidden');

    function updateJobTypeValue() {
        const serviceType = document.getElementById('service_type').value;

        if (serviceType === '1') {
            jobTypeHidden.value = document.getElementById('software_job').value;
        } else if (serviceType === '2') {
            jobTypeHidden.value = document.getElementById('hardware_job').value;
        } else {
            jobTypeHidden.value = '';
        }
    }

    document.getElementById('service_type').addEventListener('change', function () {
        const type = this.value;

        if (type === '1') {
            document.getElementById('software_section').style.display = 'block';
            document.getElementById('hardware_section').style.display = 'none';
            document.getElementById('software_job').required = true;
            document.getElementById('hardware_job').required = false;
        }
        else if (type === '2') {
            document.getElementById('software_section').style.display = 'none';
            document.getElementById('hardware_section').style.display = 'block';
            document.getElementById('software_job').required = false;
            document.getElementById('hardware_job').required = true;
        } else {
            document.getElementById('software_section').style.display = 'none';
            document.getElementById('hardware_section').style.display = 'none';
            document.getElementById('software_job').required = false;
            document.getElementById('hardware_job').required = false;
        }

        updateJobTypeValue(); // update hidden field on type change
    });

    document.getElementById('software_job').addEventListener('change', updateJobTypeValue);
    document.getElementById('hardware_job').addEventListener('change', updateJobTypeValue);

  
@endpush
