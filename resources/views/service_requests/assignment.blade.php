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
    .card {
        padding: 0;
    }
    .input-group-text{
        padding: 1.375rem 1.75rem;
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
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>There were some problems with your input:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 shadow-sm p-4 rounded bg-white">
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
                    <small class="text-muted">Request Created At</small>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($request->created_at)->format('d-M-Y h:i A') }}</div>
                </div> 
            </div>
        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            {{-- <h5>HOD Approval</h5> --}}
            <header id="header" class="relative">
                <div class="mt-0.5 space-y-2.5">
                    <div class="eyebrow h-5 text-fuchsia-800 text-sm font-semibold">Details of</div>
                    <div class="flex items-center relative gap-2">
                        <h2 id="page-title" class="inline-block text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight dark:text-gray-200">HOD Approval</h2>
                        <div id="page-context-menu" class="flex items-center justify-end shrink-0 ml-auto min-w-[156px]"></div>
                    </div>
                </div>
            </header>
            <div class="col-md-8">
                <div class="border p-3 rounded">
                    <small class="text-muted">HOD Remarks</small>
                    <div class="fw-bold">{{ $approval->remarks }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border p-3 rounded">
                    <small class="text-muted">HOD Remarks at</small>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($approval->approval_date)->format('d-M-Y h:i A') }}</div>
                </div>
            </div>
        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            <div class="col-md-6 d-flex justify-content-center">
                <button id="showRejectBtn" class="btn btn-danger rounded-pill text-center">
                    <i class="fa-regular fa-circle-xmark"></i> Reject Request With Remarks
                </button>
            </div>
            <div class="col-md-6 d-flex justify-content-center">
                <button id="showApproveBtn" class="btn btn-success rounded-pill text-center">
                    <i class="fa-regular fa-circle-check"></i> Approve And Assign Request
                </button>
            </div>
        </div>

        <!-- Reject Section -->
        <div id="rejectSection" class="row mt-3 d-none">
            <div class="card shadow-sm">
                <h5 class="card-header">Reject Request</h5>
                <div class="card-body">
                    <form method="POST" action="{{route('service-requests.reject_assign', $request->id)}}">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Remarks of rejection</span>
                            </div>
                            <textarea name="rejection_remarks" class="form-control" aria-label="With textarea" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger mt-3"><i class="bi bi-bookmark-check"></i> Save Rejection</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Approve Section -->
        <form method="POST" action="{{ route('service-requests.approve_assign', $request->id) }}" id="approveForm">
            @csrf
            <div id="approveSection" class="row mt-3 d-none">
                <div class="card shadow-sm">
                    <h5 class="card-header">Approve Request</h5>
                    <div class="card-body">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Remarks of approval</span>
                            </div>
                            <textarea name="approval_remarks" class="form-control" aria-label="With textarea" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mt-3">
                                    <label for="selection">Assign the request to team</label>
                                    <select id="selection" name="team_emp_code" class="form-select" aria-label="Default select example" required>
                                        <option value="" disabled selected>Select the team member</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->emp_code }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">    
                                <div class="form-group mt-3">
                                    <label for="expected_completion_date">Expected Completion Date</label>
                                    <input type="date" name="expected_completion_date" id="expected_completion_date"
                                        class="form-control" min="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>        
                        <button type="submit" class="btn btn-success mt-3"><i class="bi bi-bookmark-check"></i> Save Approval</button>
                    </div>
                </div>
            </div>
        </form>


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
@push('scripts')

    document.getElementById('showRejectBtn').addEventListener('click', function () {
        document.getElementById('rejectSection').classList.remove('d-none');
        document.getElementById('approveSection').classList.add('d-none');
    });

    document.getElementById('showApproveBtn').addEventListener('click', function () {
        document.getElementById('approveSection').classList.remove('d-none');
        document.getElementById('rejectSection').classList.add('d-none');
    });

    
@endpush