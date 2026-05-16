<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskProgressNotification extends Notification
{
    use Queueable;

    public function __construct(public Task $task, public string $actorName, public int $progress, public string $status)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->actorName . ' updated task progress to ' . $this->progress . '% (' . str_replace('_', ' ', $this->status) . '): ' . $this->task->title,
            'task_id' => $this->task->getKey(),
        ];
    }
}
