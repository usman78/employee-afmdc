<?php

namespace App\Notifications;

use App\Models\AdvanceSalaryApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvanceSalaryDecisionNotification extends Notification
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
        $message = match ($this->application->status) {
            AdvanceSalaryApplication::STATUS_HR_APPROVED => 'Your advance salary application has been approved by HR and sent to Accounts.',
            AdvanceSalaryApplication::STATUS_HR_REJECTED => 'Your advance salary application has been rejected by HR.',
            AdvanceSalaryApplication::STATUS_APPROVED => 'Your advance salary application has been approved completely.',
            AdvanceSalaryApplication::STATUS_ACCOUNTS_REJECTED => 'Your advance salary application has been rejected by Accounts.',
            default => 'Your advance salary application has been updated.',
        };

        return [
            'message' => $message,
            'advance_salary_application_id' => $this->application->id,
        ];
    }
}
