@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="mb-1">IT Service Requests</h3>
            <p class="text-muted mb-0">Submit a new request and track the progress of your service requests in one place.</p>
        </div>
        <a href="{{ route('service-requests.create') }}" class="btn btn-primary mt-3 mt-md-0">
            <i class="fa-solid fa-plus"></i> New Request
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Initiate a new service request</h5>

            <form action="{{ route('service-requests.store') }}" method="POST">
                @csrf

                <input type="hidden" name="REQUESTER_ID" value="{{ auth()->user()->emp_code }}">
                <input type="hidden" name="DEPARTMENT_ID" value="{{ auth()->user()->dept_code }}">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="service_type" class="form-label">Service Type</label>
                        <select class="form-select" id="service_type" name="SERVICE_TYPE" required onchange="toggleServiceSections(this.value)">
                            <option value="">-- Select Service Type --</option>
                            <option value="1">Software Service</option>
                            <option value="2">Hardware Service</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="PRIORITY" required>
                            <option value="">-- Select Priority --</option>
                            <option value="NORMAL">Normal</option>
                            <option value="URGENT">Urgent</option>
                        </select>
                    </div>

                    <div class="col-md-6" id="software_section" style="display:none;">
                        <label for="software_job" class="form-label">Software Service</label>
                        <select class="form-select" id="software_job" onchange="updateJobTypeHiddenValue()">
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

                    <div class="col-md-6" id="hardware_section" style="display:none;">
                        <label for="hardware_job" class="form-label">Hardware Service</label>
                        <select class="form-select" id="hardware_job" onchange="updateJobTypeHiddenValue()">
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

                    <div class="col-12">
                        <label for="description" class="form-label">Brief Description of the Job</label>
                        <textarea class="form-control" id="description" name="DESCRIPTION" rows="4" required></textarea>
                    </div>

                    <input type="hidden" name="JOB_TYPE" id="job_type_hidden">
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <strong>There were some problems with your input:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary mt-3">Submit Request</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Your request history</h5>
                <span class="text-muted small">{{ $requests->count() }} request(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Job Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($request->description, 90) }}</td>
                            <td>{{ $request->job_type_label }}</td>
                            <td>
                                <span class="badge {{ $request->priority == 'URGENT' ? 'bg-danger' : 'bg-info text-dark' }}">
                                    {{ $request->priority }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ str_replace('_', ' ', $request->status) }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($request->created_at)->format('d-M-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('service-requests.show', $request->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No service requests found yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateJobTypeHiddenValue() {
    const serviceType = document.getElementById('service_type');
    const softwareJob = document.getElementById('software_job');
    const hardwareJob = document.getElementById('hardware_job');
    const jobTypeHidden = document.getElementById('job_type_hidden');

    if (!serviceType || !jobTypeHidden) {
        return;
    }

    if (serviceType.value === '1') {
        jobTypeHidden.value = softwareJob ? softwareJob.value : '';
    } else if (serviceType.value === '2') {
        jobTypeHidden.value = hardwareJob ? hardwareJob.value : '';
    } else {
        jobTypeHidden.value = '';
    }
}

function toggleServiceSections(type) {
    const softwareSection = document.getElementById('software_section');
    const hardwareSection = document.getElementById('hardware_section');
    const softwareJob = document.getElementById('software_job');
    const hardwareJob = document.getElementById('hardware_job');

    if (!softwareSection || !hardwareSection) {
        return;
    }

    softwareSection.style.display = type === '1' ? 'block' : 'none';
    hardwareSection.style.display = type === '2' ? 'block' : 'none';

    if (softwareJob) {
        softwareJob.required = type === '1';
    }

    if (hardwareJob) {
        hardwareJob.required = type === '2';
    }

    updateJobTypeHiddenValue();
}

document.addEventListener('DOMContentLoaded', function () {
    const serviceType = document.getElementById('service_type');
    if (serviceType) {
        toggleServiceSections(serviceType.value);
    }
});
</script>
@endpush
