@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Service Requests</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                {{-- <th>Requester</th> --}}
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
                {{-- <td>{{ $request->requester_id }}</td> --}}
                <td>{{ $request->description }}</td>
                <td>{{ $request->job_type_label }}</td>
                <td>{{ $request->priority }}</td>
                <td>{{ str_replace('_', ' ', $request->status) }}</td>
                <td>{{ \Carbon\Carbon::parse($request->created_at)->format('d-M-Y H:i') }}</td>
                <td>
                    {{-- <a href="{{ route('service-requests.show', $request->ID) }}" class="btn btn-sm btn-primary">View</a> --}}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No service requests found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
