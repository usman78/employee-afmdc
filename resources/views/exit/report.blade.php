@extends('layouts.app')
@push('styles')
    #dt-search-0 {
        border-radius: 7px;
        padding: 2px 5px;
        outline-offset: 0;
    }
    .dt-search label {
        margin-right: 10px;
    }
    .dt-layout-row {
        margin: 20px 0;
    }
@endpush
@section('content')
<div class="container-fluid">
    <h3>Exit Interview Report</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Sr</th>
                <th>Code</th>
                <th>Name</th>
                <th>Date</th>
                <th>Designation</th>
                <th>Separation Type</th>
                <th>Show to RO?</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($interviews as $interview)
                <tr>
                    <td>{{ $interview->id }}</td>
                    <td>{{ $interview->user->emp_code }}</td>
                    <td>{{ $interview->user->name }}</td>
                    <td>{{ $interview->created_at->format('d M Y') }}</td>
                    <td>{{ $interview->user->designation->desg_short }}</td>
                    <td>{{ $interview->separation_type }}</td>
                    <td>
                        @if($interview->share_with_ro)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('exit-interview.show', $interview->id) }}" class="btn btn-sm btn-primary">View Full</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No exit interviews found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        {{ $interviews->links() }}
    </div>
</div>
@endsection
@push('cdn-scripts')
    <script src="{{asset('js/DataTables.js')}}"></script>
@endpush
@push('scripts')
    $(document).ready(function () {
        $('.table').DataTable({
            "paging": false,
            "info": false,
            "searching": true,
            "ordering": true,
            "order": [[ 0, "desc" ]]
        });
    });
@endpush
