@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Service Request #{{ $request->ID }}</h3>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Requester:</strong> {{ $request->REQUESTER_ID }}</p>
            <p><strong>Department:</strong> {{ $request->DEPARTMENT_ID }}</p>
            <p><strong>Job Type:</strong> {{ $request->JOB_TYPE }}</p>
            <p><strong>Priority:</strong> {{ $request->PRIORITY }}</p>
            <p><strong>Description:</strong> {{ $request->DESCRIPTION }}</p>
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $request->STATUS)) }}</p>
        </div>
    </div>

    @if($request->STATUS === 'pending_hod_approval')
    <form method="POST" action="{{ route('service-requests.hod.approve', $request->ID) }}">
        @csrf
        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" id="remarks" rows="3" required></textarea>
        </div>

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

        <button type="submit" class="btn btn-success">Submit</button>
    </form>
    @else
        <div class="alert alert-info">This request has already been reviewed.</div>
    @endif
</div>
@endsection
