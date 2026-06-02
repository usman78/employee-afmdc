<?php

namespace App\Notifications;

use App\Models\AdvanceSalaryApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvanceSalaryHodDecisionNotification extends Notification
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
        $message = $this->application->status === AdvanceSalaryApplication::STATUS_HOD_APPROVED
            ? 'Your advance salary application has been approved by HOD.'
            : 'Your advance salary application has been rejected by HOD.';

        return [
            'message' => $message,
            'advance_salary_application_id' => $this->application->id,
        ];
    }
}
