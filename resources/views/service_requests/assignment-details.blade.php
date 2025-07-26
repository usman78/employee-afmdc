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
                <div class="fw-bold">{{ $assignment->request->id }}</div>
            </div>
            </div>
            <div class="col-md-4">
            <div class="border p-3 rounded">
                <small class="text-muted">Requester</small>
                <div class="fw-bold">{{ $assignment->request->requester_name }}</div>
            </div>
            </div>
            <div class="col-md-4">
            <div class="border p-3 rounded">
                <small class="text-muted">Department</small>
                <div class="fw-bold">{{ $assignment->request->department_name }}</div>
            </div>
            </div>
        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            <div class="col-md-4">
                <div class="border p-3 rounded">
                    <small class="text-muted">Service Type</small>
                    <div class="fw-bold">{{ $assignment->request->job_type_label ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="border p-3 rounded">
                    <small class="text-muted">Service Description</small>
                    <div class="fw-bold">{{ $assignment->request->description }}</div>
                </div>
            </div>

        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            <div class="col-md-4">
                <div class="border p-3 rounded {{ $assignment->request->priority == 'URGENT' ? 'bg-urgent' : 'bg-normal' }}">
                    <small class="text-muted">Priority</small>
                    <div class="fw-bold">{{ $assignment->request->priority }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border p-3 rounded">
                    <small class="text-muted">Status</small>
                    <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $assignment->request->status)) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border p-3 rounded">
                    <small class="text-muted">Request Created At</small>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($assignment->request->created_at)->format('d-M-Y h:i A') }}</div>
                </div> 
            </div>
        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
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
                    <div class="fw-bold">
                        @foreach($assignment->approvals as $approval)
                            @if($approval->role == 'HOD')
                                {{ $approval->remarks }}
                            @endif    
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border p-3 rounded">
                    <small class="text-muted">HOD Remarks at</small>
                    <div class="fw-bold">
                        @foreach ($assignment->approvals as $approval)
                            @if($approval->role == 'HOD')
                                {{ \Carbon\Carbon::parse($approval->approval_date)->format('d-M-Y h:i A') }}
                            @endif    
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            <header id="header" class="relative">
                <div class="mt-0.5 space-y-2.5">
                    <div class="eyebrow h-5 text-fuchsia-800 text-sm font-semibold">Details of</div>
                    <div class="flex items-center relative gap-2">
                        <h2 id="page-title" class="inline-block text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight dark:text-gray-200">Assignment</h2>
                        <div id="page-context-menu" class="flex items-center justify-end shrink-0 ml-auto min-w-[156px]"></div>
                    </div>
                </div>
            </header>
            <div class="col-md-12">
                <div class="border p-3 rounded">
                    <small class="text-muted">IT HOD Remarks</small>
                    <div class="fw-bold">
                        @foreach ($assignment->approvals as $approval)
                            @if($approval->role == 'IT_HOD')
                                {{ $approval->remarks }}
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border p-3 rounded">
                    <small class="text-muted">Remarks at</small>
                    <div class="fw-bold">                           
                        @foreach ($assignment->approvals as $approval)
                            @if($approval->role == 'IT_HOD')
                                {{ \Carbon\Carbon::parse($approval->approval_date)->format('d-M-Y h:i A') }}
                            @endif
                        @endforeach 
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border p-3 rounded">
                    <small class="text-muted">Expected Completion Date</small>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($assignment->expected_completion_date)->format('d-M-Y') }}</div>
                </div>
            </div>
        </div>
        <div class="row g-3 shadow-sm p-4 rounded bg-white">
            <header id="header" class="relative">
                <div class="mt-0.5 space-y-2.5">
                    <div class="eyebrow h-5 text-fuchsia-800 text-sm font-semibold">Updates of</div>
                    <div class="flex items-center relative gap-2">
                        <h2 id="page-title" class="inline-block text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight dark:text-gray-200">Progress by IT team</h2>
                        <div id="page-context-menu" class="flex items-center justify-end shrink-0 ml-auto min-w-[156px]"></div>
                    </div>
                </div>
            </header>
            <div class="col-md-12">
                <div class="border p-3 rounded">
                    @if($assignment->updates && $assignment->updates->count())
                        <ul class="list-group">
                            @foreach($assignment->updates as $update)
                                <li class="list-group-item">
                                    <h2 class="mt-2 inline-flex items-center rounded-full px-4 py-1 text-blue-600 ring-1 ring-blue-600 ring-inset" id="table-of-contents-title">
                                        <span class="text-base font-medium tracking-tight">
                                            {{ $update->progress_status === 'completed' ? 'Completed' : ($update->progress_status === 'hold' ? 'On Hold' : 'In Progress')}}
                                        </span>
                                    </h2>
                                    <p class="font-display text-xl font-bold tracking-tight text-slate-900">{{ $update->comments }}</p> 
                                    <small class="text-muted">Updated by: {{ $update->updated_by }} on {{ \Carbon\Carbon::parse($update->updated_at)->format('d-M-Y H:i') }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted"><i class="fas fa-comment-slash"></i> No updates have been added yet.</p>
                    @endif
                </div>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateModal"><i class="fa-solid fa-plus"></i> Add Progress Update</button>
            <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('service-requests.add-update', $assignment->id) }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel">Add Progress Update</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="progress_status" class="form-label">Progress Status</label>
                                    <select class="form-select" id="progress_status" name="progress_status" required>
                                        <option value="" disabled selected>Select Status</option>
                                        <option value="in-progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="hold">On Hold</option>
                                    </select>    
                                </div>
                                <div class="mb-3">
                                    <label for="comments" class="form-label">Comment</label>
                                    <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>                          
        </div>

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
