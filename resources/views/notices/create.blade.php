@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="mb-0 text-white">Create New Notice</h5>
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

                    <form action="{{ route('notices.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Notice Title <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('title') is-invalid @enderror" 
                                id="title" 
                                name="title" 
                                placeholder="Enter notice title"
                                value="{{ old('title') }}"
                                required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Notice Content <span class="text-danger">*</span></label>
                            <textarea 
                                class="form-control @error('content') is-invalid @enderror" 
                                id="content" 
                                name="content" 
                                placeholder="Enter notice content"
                                rows="8"
                                required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="publish_starts_at" class="form-label">Publish Start Time</label>
                                <input
                                    type="datetime-local"
                                    class="form-control @error('publish_starts_at') is-invalid @enderror"
                                    id="publish_starts_at"
                                    name="publish_starts_at"
                                    value="{{ old('publish_starts_at') }}">
                                <small class="text-muted d-block mt-2">
                                    Leave blank to publish immediately.
                                </small>
                                @error('publish_starts_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="publish_ends_at" class="form-label">Removal Time</label>
                                <input
                                    type="datetime-local"
                                    class="form-control @error('publish_ends_at') is-invalid @enderror"
                                    id="publish_ends_at"
                                    name="publish_ends_at"
                                    value="{{ old('publish_ends_at') }}">
                                <small class="text-muted d-block mt-2">
                                    Leave blank to keep the notice visible.
                                </small>
                                @error('publish_ends_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="attachment" class="form-label">Attachment (Optional)</label>
                            <input 
                                type="file" 
                                class="form-control @error('attachment') is-invalid @enderror" 
                                id="attachment" 
                                name="attachment"
                                accept=".pdf,.doc,.docx,.xlsx,.txt,.jpg,.jpeg,.png,.gif">
                            <small class="text-muted d-block mt-2">
                                Max file size: 10MB. Allowed formats: PDF, DOC, DOCX, XLSX, TXT, JPG, JPEG, PNG, GIF
                            </small>
                            @error('attachment')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
{{-- 
                        <div class="alert alert-info" role="alert">
                            <strong>Note:</strong> This notice will be sent for approval by the COO before going live on the notice board.
                        </div> --}}

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Notice
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
