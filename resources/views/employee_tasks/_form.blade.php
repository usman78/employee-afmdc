@csrf

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="form-group">
    <label for="title">Title</label>
    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $task->title) }}" required>
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="assigned_to">Assignee</label>
            <select name="assigned_to" id="assigned_to" class="form-control @error('assigned_to') is-invalid @enderror" required>
                <option value="">Select employee</option>
                @foreach($assignees as $assignee)
                    <option value="{{ $assignee->emp_code }}" @selected((string) old('assigned_to', $task->assigned_to) === (string) $assignee->emp_code)>
                        {{ capitalizeWords($assignee->name) }}
                    </option>
                @endforeach
            </select>
            @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="priority">Priority</label>
            <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" required>
                @foreach($priorities as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', $task->priority ?? App\Models\Task::PRIORITY_NORMAL) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}">
            @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="d-flex justify-content-end">
    <a href="{{ route('employee-tasks.index') }}" class="btn btn-outline-secondary mr-2">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Save Task
    </button>
</div>
