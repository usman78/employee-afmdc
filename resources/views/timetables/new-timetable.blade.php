@extends('layouts.app')
@push('styles')
    ul.error-msg {
    margin-bottom: 0;
}
@endpush
@section('content')
    <div class="container">
        <div class="row mt-5">
            <div class="col-md-12 d-block mx-auto">  
                <div class="portfolio-details">
                    <div class="portfolio-info">
                        <h3>New Time Table</h3>
                        <p>Here you can create a new time table for the current month.</p>
                        <form method="POST" action="{{ route('timetables.create') }}">
                            @csrf
                            {{-- Success Message --}}
                            @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            {{-- General Error Message --}}
                            @if(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif

                            {{-- Detailed Errors --}}
                            @if(session('details'))
                                <div class="alert alert-warning">
                                    <ul class="mb-0">
                                        @foreach(session('details') as $detail)
                                            <li>{{ $detail }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="border p-3 rounded">
                                        <small class="text-muted">Select Year </small>
                                        <span class="text-danger">*</span>
                                        {{-- <input type="text" class="form-control" name="year_id" placeholder="Enter Year (e.g., YEAR01)" required> --}}
                                        <select class="form-select mt-2" name="year" required>
                                            <option value="">Select Year</option>
                                            @foreach($years as $year)
                                                <option value="{{ $year->year_seq }}">{{ $year->title }}</option>
                                            @endforeach
                                        </select>
                                        
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border p-3 rounded">
                                        <small class="text-muted">Program </small>
                                        <span class="text-danger">*</span>
                                        {{-- <input type="text" class="form-control" name="program" placeholder="Enter Class ID (e.g., CLASS01)" required> --}}
                                        <select class="form-select mt-2" name="program" required>
                                            <option value="">Select Program</option>
                                            @foreach($programs as $program)
                                                <option value="{{ $program }}">{{ $program }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <div class="border p-3 rounded">
                                        <small class="text-muted">From Date</small>
                                        <span class="text-danger">*</span>
                                        <input type="date" class="form-control" name="from_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <div class="border p-3 rounded">
                                        <small class="text-muted">To Date</small>
                                        <span class="text-danger">*</span>
                                        <input type="date" class="form-control" name="to_date" required>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Create Time Table
                                    </button>
                                    <a href="{{ route('timetables.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Timetables
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection        
