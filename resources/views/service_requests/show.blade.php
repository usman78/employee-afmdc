@extends('layouts.app')
@push('styles')
    .border:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }
    .bg-urgent {
        background-color: palevioletred;
    }
    .bg-normal {
        background-color: lightcyan;
    }
@endpush
@section('content')
<div class="container mt-4">
    <header id="header" class="relative">
        <div class="mt-0.5 space-y-2.5">
            <div class="eyebrow h-5 text-fuchsia-800 text-sm font-semibold">Details of</div>
            <div class="flex items-center relative gap-2">
                <h1 id="page-title" class="inline-block text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight dark:text-gray-200">Service Request</h1>
                <div id="page-context-menu" class="flex items-center justify-end shrink-0 ml-auto min-w-[156px]"></div>
            </div>
        </div>
    </header>
        <div class="row g-3 shadow-sm p-3 rounded bg-white">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="col-md-4">
            <div class="border p-3 rounded">
                <small class="text-muted">Request ID</small>
                <div class="fw-bold">{{ $request->id }}</div>
            </div>
            </div>
            <div class="col-md-4">
            <div class="border p-3 rounded">
                <small class="text-muted">Requester</small>
                <div class="fw-bold">{{ $requester_name->name ? capitalizeWords($requester_name->name) : 'N/A' }}</div>
            </div>
            </div>
            <div class="col-md-4">
            <div class="border p-3 rounded">
                <small class="text-muted">Department</small>
                <div class="fw-bold">{{ $request->department->dept_desc }}</div>
            </div>
            </div>
        </div>
        <div class="row g-3 shadow-sm p-3 rounded bg-white">
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
        <div class="row g-3 shadow-sm p-3 rounded bg-white">
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
        @if ($request->assignment)
            <div class="row g-3 shadow-sm p-3 rounded bg-white">
                <div class="col-md-12">
                    <header id="header" class="relative">
                        <div class="mt-0.5 space-y-2.5">
                            <div class="eyebrow h-5 text-fuchsia-800 text-sm font-semibold">Assignment of Request to</div>
                            <div class="flex items-center relative gap-2">
                                <h2 id="page-title" class="inline-block text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight dark:text-gray-200">IT Department</h2>
                                <div id="page-context-menu" class="flex items-center justify-end shrink-0 ml-auto min-w-[156px]"></div>
                            </div>
                        </div>
                    </header>
                </div>
                <div class="col-md-4">   
                    <div class="border p-3 rounded">
                        <small class="text-muted">Assigned To</small>
                        <div class="fw-bold">{{ capitalizeWords($request->assignment?->assignee?->name) }}</div>
                    </div>
                </div>
                <div class="col-md-8">    
                    <div class="border p-3 rounded">    
                        <small class="text-muted">Remarks</small>
                        <div class="fw-bold">{{ $request->assignment->remarks }}</div>
                    </div>
                </div>
                <div class="col-md-6">    
                    <div class="border p-3 rounded">  
                        <small class="text-muted">Assigned At</small>
                        <div class="fw-bold">{{ \Carbon\Carbon::parse($request->assignment->assigned_at)->format('d-M-Y h:i A') }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border p-3 rounded">
                        <small class="text-muted">Expected Completion Date</small>
                        <div class="fw-bold">{{ \Carbon\Carbon::parse($request->assignment->expected_completion_date)->format('d-M-Y') }}</div>
                    </div>
                </div>            
            </div>
        @endif

    @if(auth()->user()->emp_code === hisBoss($request->requester_id))
        @if ($request->status === 'PENDING_HOD_APPROVAL')
            <form method="POST" action="{{ route('service-requests.approve', $request->id) }}">
                @csrf
                <input type="text" name="requester_id" value="{{ $request->requester_id }}" hidden>
                <div class="row g-3 shadow-sm px-4 rounded bg-white">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="remarks" rows="3"></textarea>
                        </div>
                    </div>
                </div> 
                <div class="row g-3 shadow-sm px-4 rounded bg-white">   
                    <div class="col-md-12">           
                        <div class="mb-3">
                            <label class="form-label">Decision</label><br>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="decision" id="approve" value="approved" class="form-check-input" required>
                                <label for="approve" class="form-check-label">Approve</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" name="decision" id="reject" value="rejected" class="form-check-input" required>
                                <label for="reject" class="form-check-label">Reject</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row shadow-sm p-4 rounded bg-white">
                    <div class="col-md-12 d-flex justify-content-center">
                        <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-circle-check"></i> Save Decision</button>
                    </div>
                </div>
            </form>
        @else
        <div class="col-md-12 mt-3">
            <span class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-cyan-600 rounded-full">
                <i class="fa-solid fa-check"></i>
                This service request has already been reviewed.
            </span>
        </div>
        @endif
    @endif    

    {{-- @if($request->approvals && $request->approvals->count())
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
    @endif --}}

    {{-- @if($request->assignment)
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
    @endif --}}

    <a href="{{ route('service-requests.index') }}" class="my-3 btn btn-secondary mt-4"><i class="fa-solid fa-backward"></i> Back to List</a>
</div>
@endsection
