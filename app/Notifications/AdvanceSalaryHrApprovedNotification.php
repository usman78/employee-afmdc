<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvanceSalaryHrApprovedNotification extends Notification
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
            'message' => 'Advance salary application approved by HR and pending Accounts approval.',
            'advance_salary_application_id' => $this->application->id,
        ];
    }
}
