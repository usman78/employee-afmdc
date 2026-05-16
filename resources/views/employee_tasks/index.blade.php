@extends('layouts.app')

@push('styles')
    .task-overdue {
        background: #fff1f2;
        color: #991b1b;
    }
    .task-overdue a {
        color: #991b1b;
        font-weight: 700;
    }
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Task System</h1>
            <p class="mb-0 text-muted">{{ $isHod ? 'Tasks assigned by you to your department team.' : 'Tasks assigned to you.' }}</p>
        </div>
        @can('create', App\Models\Task::class)
            <a href="{{ route('employee-tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> New Task
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employee-tasks.index') }}" class="row">
                <div class="col-md-{{ $isHod ? '2' : '4' }} mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-{{ $isHod ? '2' : '4' }} mb-3">
                    <label>Priority</label>
                    <select name="priority" class="form-control">
                        <option value="">All</option>
                        @foreach($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if($isHod)
                    <div class="col-md-3 mb-3">
                        <label>Assignee</label>
                        <select name="assignee" class="form-control">
                            <option value="">All</option>
                            @foreach($assignees as $assignee)
                                <option value="{{ $assignee->emp_code }}" @selected((string) request('assignee') === (string) $assignee->emp_code)>
                                    {{ capitalizeWords($assignee->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>Due Date</label>
                        <input type="date" name="due_date" value="{{ request('due_date') }}" class="form-control">
                    </div>
                @endif
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button class="btn btn-primary mr-2" type="submit">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a class="btn btn-outline-secondary" href="{{ route('employee-tasks.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>{{ $isHod ? 'Assignee' : 'Assigned By' }}</th>
                            <th>Progress</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr @class(['task-overdue' => $task->isOverdue()])>
                                <td>
                                    <a href="{{ route('employee-tasks.show', $task) }}">{{ $task->title }}</a>
                                    @if($task->isOverdue())
                                        <span class="badge badge-danger ml-2">Overdue</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                    {{ $statuses[$task->status] ?? ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span></td>
                                <td>{{ $priorities[$task->priority] ?? ucfirst($task->priority) }}</td>
                                <td>{{ capitalizeWords($isHod ? ($task->assignee->name ?? 'N/A') : ($task->assigner->name ?? 'N/A')) }}</td>
                                <td>
                                    <div class="progress" style="height: 18px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ (int) $task->progress }}%;">
                                            {{ (int) $task->progress }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $task->due_date ? $task->due_date->format('d-M-Y') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('employee-tasks.show', $task) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No tasks found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tasks->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
