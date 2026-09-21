<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $this->createdByHodInDepartment($user, $task)
            || (string) $task->assigned_to === (string) $user->emp_code;
    }

    public function create(User $user): bool
    {
        return $user->isBoss();
    }

    public function update(User $user, Task $task): bool
    {
        return $this->createdByHodInDepartment($user, $task)
            && $task->status !== Task::STATUS_CLOSED;
    }

    public function close(User $user, Task $task): bool
    {
        return $this->createdByHodInDepartment($user, $task)
            && $task->status !== Task::STATUS_CLOSED;
    }

    public function updateProgress(User $user, Task $task): bool
    {
        return (string) $task->assigned_to === (string) $user->emp_code
            && $task->status !== Task::STATUS_CLOSED;
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    private function createdByHodInDepartment(User $user, Task $task): bool
    {
        return (string) $task->created_by === (string) $user->emp_code
            && (string) $task->department_id === (string) $user->dept_code;
    }
}
