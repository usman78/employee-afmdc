@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">{{ $task->title }}</h1>
            <p class="mb-0 text-muted">
                Assigned to {{ capitalizeWords($task->assignee->name ?? 'N/A') }} by {{ capitalizeWords($task->assigner->name ?? 'N/A') }}
            </p>
        </div>
        <div>
            <a href="{{ route('employee-tasks.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            @can('update', $task)
                <a href="{{ route('employee-tasks.edit', $task) }}" class="btn btn-primary">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    @if($task->isOverdue())
                        <div class="alert alert-danger">This task is overdue.</div>
                    @endif

                    <dl class="row mb-0">
                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">{{ $statuses[$task->status] ?? ucfirst(str_replace('_', ' ', $task->status)) }}</dd>
                        <dt class="col-sm-3">Priority</dt>
                        <dd class="col-sm-9">{{ $priorities[$task->priority] ?? ucfirst($task->priority) }}</dd>
                        <dt class="col-sm-3">Due Date</dt>
                        <dd class="col-sm-9">{{ $task->due_date ? $task->due_date->format('d-M-Y') : 'N/A' }}</dd>
                        <dt class="col-sm-3">Progress</dt>
                        <dd class="col-sm-9">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ (int) $task->progress }}%;">
                                    {{ (int) $task->progress }}%
                                </div>
                            </div>
                        </dd>
                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $task->description ?: 'No description provided.' }}</dd>
                    </dl>
                </div>
            </div>

            @can('updateProgress', $task)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Update Progress</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('employee-tasks.progress', $task) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control" required>
                                            @foreach([App\Models\Task::STATUS_PENDING, App\Models\Task::STATUS_IN_PROGRESS, App\Models\Task::STATUS_COMPLETED] as $status)
                                                <option value="{{ $status }}" @selected(old('status', $task->status) === $status)>
                                                    {{ $statuses[$status] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Progress %</label>
                                        <input type="number" name="progress" min="0" max="100" value="{{ old('progress', $task->progress) }}" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Comment</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="Optional update for your HOD">{{ old('comment') }}</textarea>
                            </div>
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-check mr-1"></i> Save Progress
                            </button>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Comments</h6>
                </div>
                <div class="card-body">
                    @can('comment', $task)
                        <form method="POST" action="{{ route('employee-tasks.comments.store', $task) }}" class="mb-4">
                            @csrf
                            <div class="form-group">
                                <textarea name="comment" class="form-control @error('comment') is-invalid @enderror" rows="3" placeholder="Add clarification or update" required>{{ old('comment') }}</textarea>
                                @error('comment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-comment mr-1"></i> Add Comment
                            </button>
                        </form>
                    @endcan

                    @forelse($task->comments as $comment)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="small text-muted">{{ capitalizeWords($comment->user->name ?? 'N/A') }} - {{ $comment->created_at->format('M d, Y h:i A') }}</div>
                            <div>{{ $comment->comment ?? $comment->COMMENT }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No comments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @can('close', $task)
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="POST" action="{{ route('employee-tasks.destroy', $task) }}" onsubmit="return confirm('Close this task?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-block" type="submit">
                                <i class="fas fa-lock mr-1"></i> Close Task
                            </button>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activity Log</h6>
                </div>
                <div class="card-body">
                    @forelse($task->activities as $activity)
                        @php
                            $activityAction = $activity->action ?? $activity->ACTION;
                            $fromStatus = $activity->from_status ?? $activity->FROM_STATUS;
                            $toStatus = $activity->to_status ?? $activity->TO_STATUS;
                            $activityDescription = $activity->description ?? $activity->DESCRIPTION;
                        @endphp
                        <div class="mb-3">
                            <div class="font-weight-bold">
                                @if($toStatus && $fromStatus !== $toStatus)
                                    Marked as {{ $statuses[$toStatus] ?? ucfirst(str_replace('_', ' ', $toStatus)) }}
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $activityAction)) }}
                                @endif
                                by {{ capitalizeWords($activity->actor->name ?? 'N/A') }}
                            </div>
                            <div class="small text-muted">{{ $activity->created_at->format('M d, Y h:i A') }}</div>
                            @if($activityDescription)
                                <div class="small">{{ $activityDescription }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">No activity recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
