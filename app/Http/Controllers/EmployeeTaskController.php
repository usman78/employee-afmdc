<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentNotification;
use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskProgressNotification;
use Illuminate\Http\Request;

class EmployeeTaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $user = auth()->user();
        $isHod = $user->isBoss();
        $query = Task::with(['assignee:emp_code,name', 'assigner:emp_code,name']);

        if ($isHod) {
            $query->where('created_by', $user->emp_code)
                ->where('department_id', $user->dept_code);
        } else {
            $query->where('assigned_to', $user->emp_code);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($isHod && $request->filled('assignee')) {
            $query->where('assigned_to', $request->assignee);
        }

        if ($isHod && $request->filled('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        $tasks = $query->orderByRaw("CASE WHEN STATUS = 'completed' THEN 3 WHEN STATUS = 'closed' THEN 4 ELSE 1 END")
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('employee_tasks.index', [
            'tasks' => $tasks,
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
            'isHod' => $isHod,
            'assignees' => $isHod ? $this->subordinatesFor($user) : collect(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        return view('employee_tasks.create', [
            'task' => new Task(['PRIORITY' => Task::PRIORITY_NORMAL]),
            'priorities' => Task::priorities(),
            'assignees' => $this->subordinatesFor(auth()->user()),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $this->validatedTask($request);
        $assignee = $this->authorizedAssignee($validated['assigned_to']);

        if (! $assignee) {
            return back()->withInput()->with('error', 'The selected employee is not in your department/team.');
        }

        $task = Task::create([
            'ID' => getIncrementedId('EMPLOYEE_TASKS', 'ID'),
            'TITLE' => $validated['title'],
            'DESCRIPTION' => $validated['description'] ?? null,
            'STATUS' => Task::STATUS_PENDING,
            'PRIORITY' => $validated['priority'],
            'PROGRESS' => 0,
            'DUE_DATE' => $validated['due_date'] ?? null,
            'CREATED_BY' => auth()->user()->emp_code,
            'ASSIGNED_TO' => $assignee->emp_code,
            'DEPARTMENT_ID' => auth()->user()->dept_code,
        ]);

        $this->logActivity($task, 'created', null, Task::STATUS_PENDING, 'Task created and assigned.');

        $assignee->notify(new TaskAssignedNotification($task));

        return redirect()->route('employee-tasks.show', $task)->with('success', 'Task assigned successfully.');
    }

    public function show(Task $employeeTask)
    {
        $this->authorize('view', $employeeTask);

        $employeeTask->load([
            'assignee:emp_code,name,dept_code',
            'assigner:emp_code,name,dept_code',
            'comments.user:emp_code,name',
            'activities.actor:emp_code,name',
        ]);

        return view('employee_tasks.show', [
            'task' => $employeeTask,
            'statuses' => Task::statuses(),
            'priorities' => Task::priorities(),
        ]);
    }

    public function edit(Task $employeeTask)
    {
        $this->authorize('update', $employeeTask);

        return view('employee_tasks.edit', [
            'task' => $employeeTask,
            'priorities' => Task::priorities(),
            'assignees' => $this->subordinatesFor(auth()->user()),
        ]);
    }

    public function update(Request $request, Task $employeeTask)
    {
        $this->authorize('update', $employeeTask);

        $validated = $this->validatedTask($request);
        $assignee = $this->authorizedAssignee($validated['assigned_to']);

        if (! $assignee) {
            return back()->withInput()->with('error', 'The selected employee is not in your department/team.');
        }

        $employeeTask->fill([
            'TITLE' => $validated['title'],
            'DESCRIPTION' => $validated['description'] ?? null,
            'PRIORITY' => $validated['priority'],
            'DUE_DATE' => $validated['due_date'] ?? null,
            'ASSIGNED_TO' => $assignee->emp_code,
        ])->save();

        $this->logActivity($employeeTask, 'updated', $employeeTask->status, $employeeTask->status, 'Task details updated.');

        return redirect()->route('employee-tasks.show', $employeeTask)->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $employeeTask)
    {
        $this->authorize('close', $employeeTask);

        $oldStatus = $employeeTask->status;
        $employeeTask->forceFill([
            'STATUS' => Task::STATUS_CLOSED,
            'CLOSED_AT' => now(),
            'CLOSED_BY' => auth()->user()->emp_code,
        ])->save();

        $this->logActivity($employeeTask, 'closed', $oldStatus, Task::STATUS_CLOSED, 'Task closed by HOD.');

        return redirect()->route('employee-tasks.index')->with('success', 'Task closed successfully.');
    }

    public function progress(Request $request, Task $employeeTask)
    {
        $this->authorize('updateProgress', $employeeTask);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Task::STATUS_PENDING,
                Task::STATUS_IN_PROGRESS,
                Task::STATUS_COMPLETED,
            ]),
            'progress' => 'required|integer|min:0|max:100',
            'comment' => 'nullable|string|max:2000',
        ]);

        $oldStatus = $employeeTask->status;
        $newStatus = $validated['status'];

        if ($newStatus === Task::STATUS_COMPLETED) {
            $validated['progress'] = 100;
        }

        $employeeTask->forceFill([
            'STATUS' => $newStatus,
            'PROGRESS' => $validated['progress'],
        ])->save();

        if ($oldStatus !== $newStatus) {
            $this->logActivity($employeeTask, 'status_changed', $oldStatus, $newStatus, 'Status changed.');
        } else {
            $this->logActivity($employeeTask, 'progress_updated', $oldStatus, $newStatus, 'Progress updated to ' . $validated['progress'] . '%.');
        }

        if (! empty($validated['comment'])) {
            $this->storeComment($employeeTask, $validated['comment']);
        }

        if ($oldStatus !== Task::STATUS_COMPLETED && $newStatus === Task::STATUS_COMPLETED) {
            $this->notifyCounterpart($employeeTask, new TaskCompletedNotification($employeeTask));
        } else {
            $this->notifyCounterpart($employeeTask, new TaskProgressNotification(
                $employeeTask,
                capitalizeWords(auth()->user()->name),
                (int) $validated['progress'],
                $newStatus
            ));
        }

        return redirect()->route('employee-tasks.show', $employeeTask)->with('success', 'Task progress updated successfully.');
    }

    public function comment(Request $request, Task $employeeTask)
    {
        $this->authorize('comment', $employeeTask);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $this->storeComment($employeeTask, $validated['comment']);

        return redirect()->route('employee-tasks.show', $employeeTask)->with('success', 'Comment added successfully.');
    }

    private function validatedTask(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:4000',
            'priority' => 'required|in:' . implode(',', array_keys(Task::priorities())),
            'assigned_to' => 'required',
            'due_date' => 'nullable|date',
        ]);
    }

    private function authorizedAssignee($empCode): ?User
    {
        return $this->subordinatesFor(auth()->user())
            ->first(fn ($employee) => (string) $employee->emp_code === (string) $empCode);
    }

    private function subordinatesFor(User $hod)
    {
        $teamMembers = $hod->teamMembers->pluck('emp_code_l');

        return User::select('emp_code', 'name', 'dept_code')
            ->whereIn('emp_code', $teamMembers)
            ->where('dept_code', $hod->dept_code)
            ->whereNull('quit_stat')
            ->orderBy('name')
            ->get();
    }

    private function storeComment(Task $task, string $comment, bool $notify = true): void
    {
        TaskComment::create([
            'ID' => getIncrementedId('EMPLOYEE_TASK_COMMENTS', 'ID'),
            'TASK_ID' => $task->getKey(),
            'USER_ID' => auth()->user()->emp_code,
            'COMMENT' => $comment,
        ]);

        $this->logActivity($task, 'commented', $task->status, $task->status, 'Comment added.');

        if ($notify) {
            $this->notifyCounterpart($task, new TaskCommentNotification($task, capitalizeWords(auth()->user()->name)));
        }
    }

    private function logActivity(Task $task, string $action, ?string $fromStatus, ?string $toStatus, ?string $description = null): void
    {
        TaskActivity::create([
            'ID' => getIncrementedId('EMPLOYEE_TASK_ACTIVITIES', 'ID'),
            'TASK_ID' => $task->getKey(),
            'ACTOR_ID' => auth()->user()->emp_code,
            'ACTION' => $action,
            'FROM_STATUS' => $fromStatus,
            'TO_STATUS' => $toStatus,
            'DESCRIPTION' => $description,
        ]);
    }

    private function notifyCounterpart(Task $task, $notification): void
    {
        $actorCode = (string) auth()->user()->emp_code;
        $assigneeCode = (string) $task->assigned_to;
        $assignerCode = (string) $task->created_by;

        if ($actorCode === $assigneeCode) {
            $recipient = $task->assigner ?: User::where('emp_code', $assignerCode)->first();
        } elseif ($actorCode === $assignerCode) {
            $recipient = $task->assignee ?: User::where('emp_code', $assigneeCode)->first();
        } else {
            $recipient = null;
        }

        $recipient?->notify($notification);
    }
}
