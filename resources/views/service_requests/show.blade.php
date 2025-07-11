@extends('layouts.app')
@push('styles')
    .border:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }
    .bg-urgent {
        background-color: palevioletred;
        color: white;
    }
    .bg-normal {
        background-color: lightcyan;
        color: black;
    }
@endpush
@section('content')
<div class="container mt-4">
    <h3>Service Request Details</h3>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            <div class="col-md-4">
            <div class="border p-3 rounded">
                <small class="text-muted">Request ID</small>
                <div class="fw-bold">{{ $request->id }}</div>
            </div>
            </div>
            <div class="col-md-4">
            <div class="border p-3 rounded">
                <small class="text-muted">Requester</small>
                <div class="fw-bold">{{ $request->requester_name ? $request->requester_name : 'N/A' }}</div>
            </div>
            </div>
            <div class="col-md-4">
            <div class="border p-3 rounded">
                <small class="text-muted">Department</small>
                <div class="fw-bold">{{ $request->department->dept_desc }}</div>
            </div>
            </div>
        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            <div class="col-md-4">
                <div class="border p-3 rounded">
                    <small class="text-muted">Service Type</small>
                    <div class="fw-bold">{{ $request->job_type_label ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="border p-3 rounded">
                    <small class="text-muted">Service Description</small>
                    <div class="fw-bold">{{ $request->description }}</div>
                </div>
            </div>

        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            <div class="col-md-4">
                <div class="border p-3 rounded {{ $request->priority == 'URGENT' ? 'bg-urgent' : 'bg-normal' }}">
                    <small class="text-muted">Priority</small>
                    <div class="fw-bold">{{ $request->priority }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border p-3 rounded">
                    <small class="text-muted">Status</small>
                    <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border p-3 rounded">
                    <small class="text-muted">Created At</small>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($request->created_at)->format('d-M-Y h:i A') }}</div>
                </div> 
            </div>
        </div>

    @if($request->approvals && $request->approvals->count())
        <h5>Approval History</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Role</th>
                    <th>Approved By</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Approval Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($request->approvals as $approval)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $approval->ROLE)) }}</td>
                    <td>{{ $approval->APPROVED_BY }}</td>
                    <td>{{ ucfirst($approval->STATUS) }}</td>
                    <td>{{ $approval->REMARKS }}</td>
                    <td>{{ \Carbon\Carbon::parse($approval->APPROVAL_DATE)->format('d-M-Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($request->assignment)
        <h5>IT Assignment</h5>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Assigned To:</strong> {{ $request->assignment->ASSIGNED_TO }}</p>
                <p><strong>Remarks:</strong> {{ $request->assignment->REMARKS }}</p>
                <p><strong>Assigned At:</strong> {{ \Carbon\Carbon::parse($request->assignment->ASSIGNED_AT)->format('d-M-Y') }}</p>
                <p><strong>Expected Completion:</strong> {{ \Carbon\Carbon::parse($request->assignment->EXPECTED_COMPLETION_DATE)->format('d-M-Y') }}</p>
            </div>
        </div>

        @if($request->assignment->updates && $request->assignment->updates->count())
            <h6>Progress Updates</h6>
            <ul class="list-group">
                @foreach($request->assignment->updates as $update)
                    <li class="list-group-item">
                        <strong>{{ $update->PROGRESS_STATUS }}</strong> - 
                        {{ $update->COMMENT }} 
                        <br><small class="text-muted">Updated by: {{ $update->UPDATED_BY }} on {{ \Carbon\Carbon::parse($update->UPDATED_AT)->format('d-M-Y H:i') }}</small>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

    <a href="{{ route('service-requests.index') }}" class="btn btn-secondary mt-4">Back to List</a>
</div>
@endsection
