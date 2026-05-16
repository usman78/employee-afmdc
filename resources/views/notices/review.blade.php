@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center mb-3">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="mb-0 text-white">Notice Review & Approval</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> Please fix the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Notice Details Section -->
                    <div class="mb-4">
                        <h4 class="mb-3 text-primary">Notice Details</h4>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Title</label>
                                <p class="form-control-plaintext">{{ $notice->title }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created By</label>
                                <p class="form-control-plaintext">
                                    {{ $notice->creator?->name ?? 'Unknown' }} ({{ $notice->created_by }})
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created Date</label>
                                <p class="form-control-plaintext">{{ $notice->created_at?->format('d-m-Y H:i:s') ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Current Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-warning">{{ ucfirst($approval->approval_status) }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Publish Start</label>
                                <p class="form-control-plaintext">{{ $notice->publish_starts_at?->format('d-m-Y H:i') ?? 'Immediately' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Removal Time</label>
                                <p class="form-control-plaintext">{{ $notice->publish_ends_at?->format('d-m-Y H:i') ?? 'No end time' }}</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Content</label>
                            <div class="border p-3 rounded bg-light" style="min-height: 200px;">
                                {!! nl2br(e($notice->content)) !!}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Approval Section -->
                    <div class="mb-4">
                        <h4 class="mb-3 text-primary">Approval Decision</h4>

                        @if ($approval->approval_status === 'pending')
                            <!-- Approve Form -->
                            <form action="{{ route('notices.approve', $notice->id) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="mb-3">
                                    <label for="approve_remarks" class="form-label">Remarks (Optional)</label>
                                    <textarea 
                                        class="form-control" 
                                        id="approve_remarks" 
                                        name="remarks" 
                                        placeholder="Add any remarks for approval"
                                        rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this notice?');">
                                    <i class="bi bi-check-circle"></i> Approve Notice
                                </button>
                            </form>

                            <!-- Reject Form -->
                            <form action="{{ route('notices.reject', $notice->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="reject_remarks" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea 
                                        class="form-control @error('remarks') is-invalid @enderror" 
                                        id="reject_remarks" 
                                        name="remarks" 
                                        placeholder="Please provide a reason for rejection"
                                        rows="3"
                                        required></textarea>
                                    @error('remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this notice?');">
                                    <i class="bi bi-x-circle"></i> Reject Notice
                                </button>
                            </form>
                        @else
                            <!-- Display Already Approved/Rejected Status -->
                            <div class="alert alert-info" role="alert">
                                <strong>Decision Already Made</strong><br>
                                Status: <strong>{{ ucfirst($approval->approval_status) }}</strong><br>
                                @if ($approval->remarks)
                                    Remarks: {{ $approval->remarks }}
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Back Button -->
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('home') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
