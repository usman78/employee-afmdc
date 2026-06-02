<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvanceSalarySubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'New advance salary application pending your approval.',
            'advance_salary_application_id' => $this->application->id,
        ];
    }
}
