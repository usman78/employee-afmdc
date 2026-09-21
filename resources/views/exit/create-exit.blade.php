@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4>Exit Interview Form (Confidential)</h4>
            <p class="mb-0 small">Please fill this form appropriately. Information shall be kept confidential.</p>
        </div>
        <div class="card-body">
            @if(isset($status))
                <div class="alert alert-info">
                    {{ $status }}
                </div>
            @elseif (isset($success))
                <div class="alert alert-success">
                    {{ $success }}
                </div>
            @else
            <form action="{{ route('exit-interview.store') }}" method="POST">
                @csrf

                {{-- Part 1: Auto-filled Employee Info --}}
                <div class="alert alert-secondary mb-4">
                    <strong>Employee:</strong> {{ $user->name }} | 
                    <strong>Designation:</strong> {{ $user->designation->desg_short ?? 'N/A' }} | 
                    <strong>Department:</strong> {{ $user->department->dept_desc ?? 'N/A' }}
                </div>

                {{-- Part 2: Separation Details  --}}
                <h5 class="border-bottom pb-2">Separation Details</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date of Leaving</label>
                        <input type="date" name="date_of_leaving" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type of Separation</label>
                        <select name="separation_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Resignation">Resignation</option>
                            <option value="Retirement">Retirement</option>
                            <option value="Retrenchment">Retrenchment</option>
                            <option value="Termination">Termination</option>
                        </select>
                    </div>
                </div>

                {{-- Part 3: Reasons for Leaving  --}}
                <h5 class="border-bottom pb-2 mt-4">What made you decide to leave?</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Unsatisfactory Working Conditions</h6>
                        @php
                            $unsatisfactory = [
                                'Physical conditions', 'Departmental Politics', 'Treatment by Reporting Officer',
                                'Inadequate Pay Package', 'Inadequate Chances of Growth', 'Work environment',
                                'Leadership style of RO', 'Lack of recognition', 'Mismatch of interest', 'Treatment by Colleagues'
                            ];
                        @endphp
                        @foreach($unsatisfactory as $reason)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reasons[]" value="{{ $reason }}" id="r_{{ Str::slug($reason) }}">
                                <label class="form-check-label" for="r_{{ Str::slug($reason) }}">{{ $reason }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="col-md-6">
                        <h6>Voluntary Resignation Due To</h6>
                        @php
                            $voluntary = [
                                'Relocating', 'Health Reasons', 'Domestic Problems', 'Further Education',
                                'New Job: Better Pay', 'New Job: Better Benefits', 'New Job: Career Opportunities',
                                'New Job: Interest Match', 'New Job: Better Conditions', 'New Job: Better Location',
                                'New Job: Better Training', 'New Job: Better Vehicle'
                            ];
                        @endphp
                        @foreach($voluntary as $reason)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reasons[]" value="{{ $reason }}" id="v_{{ Str::slug($reason) }}">
                                <label class="form-check-label" for="v_{{ Str::slug($reason) }}">{{ $reason }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Part 4: Open Questions [cite: 3, 4] --}}
                <div class="mb-3 mt-3">
                    <label>What circumstances would have prevented your departure?</label>
                    <textarea name="prevented_departure" class="form-control" rows="2"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>What did you like <strong>most</strong> about your job?</label>
                        <textarea name="liked_most" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label>What did you like <strong>least</strong> about your job?</label>
                        <textarea name="liked_least" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                {{-- Part 5: Scales  --}}
                <div class="row mt-4">
                    <div class="col-md-6">
                        <label class="form-label">Was your workload usually:</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="workload" value="Too Heavy" required> <label>Too Heavy</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="workload" value="About Right"> <label>About Right</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="workload" value="Too Light"> <label>Too Light</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Would you recommend the company to a friend?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="recommend_friend" value="Definitely" required> <label>Definitely</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="recommend_friend" value="With Reservations"> <label>With Reservations</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="recommend_friend" value="Never"> <label>Never</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Part 6: Reporting Officer Ratings  --}}
                <h5 class="border-bottom pb-2 mt-4">Rate your Reporting Officer</h5>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="table-light">
                            <th width="40%">Attribute</th>
                            <th>Always</th>
                            <th>Usually</th>
                            <th>Sometimes</th>
                            <th>Never</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $roAttributes = [
                                'Was consistently fair', 'Provided recognition', 'Resolved complaints',
                                'Was sensitive to Employee Needs', 'Provided feedback on performance',
                                'Was receptive for open communication'
                            ];
                        @endphp
                        @foreach($roAttributes as $attr)
                        <tr>
                            <td>{{ $attr }}</td>
                            @foreach(['Always', 'Usually', 'Sometimes', 'Never'] as $option)
                            <td><input type="radio" name="ro_ratings[{{ $attr }}]" value="{{ $option }}" required></td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Part 7: General Company Ratings  --}}
                <h5 class="border-bottom pb-2 mt-4">How would you rate the following?</h5>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="table-light">
                            <th width="40%">Factor</th>
                            <th>Excellent</th>
                            <th>Good</th>
                            <th>Fair</th>
                            <th>Poor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $companyFactors = [
                                'Co-operation within your department', 'Co-operation with other departments',
                                'Training & Development', 'Resources provided for the Job',
                                'Physical working conditions', 'Performance Appraisal System',
                                'Career Development Opportunities', 'Rate of Pay for your Job',
                                'Your Perks / Benefits', 'Leave structure'
                            ];
                        @endphp
                        @foreach($companyFactors as $factor)
                        <tr>
                            <td>{{ $factor }}</td>
                            @foreach(['Excellent', 'Good', 'Fair', 'Poor'] as $option)
                            <td><input type="radio" name="company_ratings[{{ $factor }}]" value="{{ $option }}" required></td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Part 8: Suggestions & Permission [cite: 12, 13] --}}
                <div class="mt-4">
                    <label class="form-label">What suggestions would you give to make this place a better place to work?</label>
                    <textarea name="suggestions" class="form-control" rows="3"></textarea>
                </div>

                <div class="mt-4 p-3 bg-light border rounded">
                    <label class="form-label fw-bold">Would you recommend us to show this form to your Reporting Officer? </label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="share_with_ro" value="1" required>
                            <label class="form-check-label">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="share_with_ro" value="0">
                            <label class="form-check-label">No</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success btn-lg">Submit Feedback</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection