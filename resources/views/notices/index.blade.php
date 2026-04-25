@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h3>All Notices</h3>
            <p class="text-muted">View all previous notices, their approval status, and feedback</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('notices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Notice
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($notices->count() > 0)
        <div class="row">
            @foreach ($notices as $notice)
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <!-- Notice Header -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">{{ $notice->title }}</h5>
                                @php
                                    $approval = $notice->approvals->first();
                                    $statusClass = match($approval?->approval_status ?? 'pending') {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning'
                                    };
                                    $statusIcon = match($approval?->approval_status ?? 'pending') {
                                        'approved' => 'check-circle-fill',
                                        'rejected' => 'x-circle-fill',
                                        default => 'clock-fill'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    <i class="bi bi-{{ $statusIcon }}"></i> {{ ucfirst($approval?->approval_status ?? 'pending') }}
                                </span>
                            </div>

                            <!-- Creator Info -->
                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>Created by:</strong> {{ $notice->creator?->name ?? 'Unknown' }} ({{ $notice->created_by }})<br>
                                    <strong>Date:</strong> {{ $notice->created_at?->format('d-m-Y H:i') ?? 'N/A' }}
                                </small>
                            </div>

                            <!-- Notice Content Preview -->
                            <div class="mb-3">
                                <p class="card-text text-truncate" style="max-height: 60px; overflow: hidden;">
                                    {{ substr($notice->content, 0, 150) }}{{ strlen($notice->content) > 150 ? '...' : '' }}
                                </p>
                            </div>

                            <!-- Approval Details -->
                            @if ($approval)
                                <div class="border-top pt-3 mb-3">
                                    <small>
                                        <strong>Approver:</strong> {{ $approval->approver?->name ?? 'Unknown' }}<br>
                                        @if ($approval->updated_at && $approval->updated_at != $approval->created_at)
                                            <strong>Decision Date:</strong> {{ $approval->updated_at?->format('d-m-Y H:i') ?? 'N/A' }}<br>
                                        @endif
                                    </small>

                                    <!-- COO Remarks/Comments -->
                                    @if ($approval->remarks)
                                        <div class="alert alert-light border border-secondary mt-2 py-2 px-3 mb-0">
                                            <small class="fw-bold">COO Comments:</small><br>
                                            <small class="text-secondary">{{ $approval->remarks }}</small>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('notices.review', $notice->id) }}" class="btn btn-sm btn-info flex-grow-1">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                                @if ($approval?->approval_status === 'pending' && Auth::user()->emp_code === $approval->approver_id)
                                    <a href="{{ route('notices.review', $notice->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Review
                                    </a>
                                @endif
                            </div>

                            <!-- Published Status -->
                            @if ($notice->is_published)
                                <div class="mt-2">
                                    <span class="badge bg-success">
                                        <i class="bi bi-eye"></i> Published on Notice Board
                                    </span>
                                </div>
                            @else
                                <div class="mt-2">
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-eye-slash"></i> Not Published
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $notices->links() }}
        </div>
    @else
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-info-circle" style="font-size: 2rem;"></i><br><br>
            <h5>No Notices Found</h5>
            <p class="text-muted">There are no notices yet. <a href="{{ route('notices.create') }}">Create the first notice</a></p>
        </div>
    @endif
</div>
@endsection
