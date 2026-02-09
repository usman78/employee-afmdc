@extends('layouts.app')
@php
    use Carbon\Carbon;
@endphp
@section('content')
<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="text-white mb-0">EXIT INTERVIEW FORM (Confidential)</h4>
            <a href="{{ route('exit-interview.download-pdf', $interview->id) }}" target="_blank" class="btn btn-sm btn-light d-print-none">Print Report</a>
        </div>
        
        <div class="card-body">
            <h5 class="text-primary border-bottom pb-2">Employee Information</h5>
            <div class="row mb-4">
                <div class="col-md-4"><strong>Employee Code:</strong> {{ $interview->user->emp_code }}</div>
                <div class="col-md-4"><strong>Name:</strong> {{ $interview->user->name }}</div>
                <div class="col-md-4"><strong>Department:</strong> {{ $interview->user->department->dept_desc }}</div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4"><strong>Current Designation:</strong> {{ $interview->user->designation->desg_short }}</div>
                <div class="col-md-4"><strong>Designation at joining:</strong> {{ designationAtJoining($interview->user->emp_code) }}</div>
                <div class="col-md-4"><strong>Reporting Officer:</strong> {{ employeeName($interview->user->head_no) }}</div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4"><strong>Placement of Job:</strong> {{ jobPlacement($interview->user->loca_code) }}</div>
                <div class="col-md-4"><strong>Date of Joining:</strong> {{ dateFormat($interview->user->join_date) }}</div>
                <div class="col-md-4"><strong>Date of Leaving:</strong> {{ dateFormat($interview->leave_date) }}</div>
            </div>
            <div class="row mb-4">    
                <div class="col-md-4"><strong>Total period of association:</strong> 
                    @php
                        $diff = Carbon::parse($interview->user->join_date)->diff(Carbon::parse($interview->leave_date));
                    @endphp
                    {{ $diff->y }} years, {{ $diff->m }} months, {{ $diff->d }} days
                </div>
                <div class="col-md-4"><strong>Type of Separation:</strong> {{ $interview->separation_type }}</div>
                <div class="col-md-4"><strong>Submission Date:</strong> {{ $interview->created_at->format('d-M-Y') }}</div>
            </div>
            <h5 class="text-primary border-bottom pb-2">Reasons for Leaving</h5>
            <div class="row mb-4">
                <div class="col-12">
                    @if(!empty($reasons))
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($reasons as $reason)
                                <span class="badge bg-secondary p-2">{{ $reason }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No specific reasons selected.</p>
                    @endif
                </div>
            </div>

            <h5 class="text-primary border-bottom pb-2">General Feedback</h5>
            <div class="mb-3">
                <strong>What circumstances would have prevented departure?</strong>
                <p class="border p-2 rounded bg-light">{{ $interview->prevented_departure ?? 'N/A' }}</p>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Liked Most:</strong>
                    <p class="border p-2 rounded bg-light">{{ $interview->liked_most ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Liked Least:</strong>
                    <p class="border p-2 rounded bg-light">{{ $interview->liked_least ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="mb-4">
                <strong>Suggestions for Improvement:</strong>
                <p class="border p-2 rounded bg-light">{{ $interview->suggestions ?? 'N/A' }}</p>
            </div>

            <h5 class="text-primary border-bottom pb-2">Workload & Loyalty</h5>
            <div class="row mb-4 text-center">
                <div class="col-md-6">
                    <strong>Workload:</strong>
                    <div class="h5 text-info">{{ $interview->workload }}</div>
                </div>
                <div class="col-md-6">
                    <strong>Would Recommend Company:</strong>
                    <div class="h5 text-info">{{ $interview->recommend_friend }}</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-primary border-bottom pb-2">Reporting Officer Ratings</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr><th>Attribute</th><th>Rating</th></tr>
                        </thead>
                        <tbody>
                            @foreach($roRatings as $attr => $value)         
                                <tr><td>{{ $attr }}</td><td><strong>{{ $value }}</strong></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <h5 class="text-primary border-bottom pb-2">Company Environment Ratings</h5>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr><th>Attribute</th><th>Rating</th></tr>
                        </thead>
                        <tbody>
                            @foreach($companyRatings as $company_ratings => $value)
                            <tr><td>{{ $company_ratings }}</td><td><strong>{{ $value }}</strong></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="alert {{ $interview->share_with_ro ? 'alert-success' : 'alert-warning' }} mt-4">
                <strong>Permission:</strong> This employee has 
                <strong>{{ $interview->share_with_ro ? 'AUTHORIZED' : 'NOT AUTHORIZED' }}</strong> 
                sharing this feedback with their Reporting Officer.
            </div>
        </div>
        
        <div class="card-footer text-muted small text-center">
            Aziz Fatimah Medical & Dental College - HR Department
        </div>
    </div>
</div>
@endsection