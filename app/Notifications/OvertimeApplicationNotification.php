<?php

namespace App\Notifications;

use App\Models\OvertimeApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OvertimeApplicationNotification extends Notification
{
    use Queueable;

    public function __construct(public OvertimeApplication $application, public string $stage)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = match ($this->stage) {
            'submitted' => 'New overtime application pending your approval.',
            'hod_approved' => 'Overtime application approved by HOD and pending HR review.',
            'hod_rejected' => 'Your overtime application has been rejected by HOD.',
            'hr_approved' => 'Overtime application approved by HR and pending Finance approval.',
            'hr_rejected' => 'Your overtime application has been rejected by HR.',
            'finance_approved' => 'Your overtime application has been approved by Finance.',
            'finance_rejected' => 'Your overtime application has been rejected by Finance.',
            'employee_update' => 'Your overtime application has been updated.',
            default => 'Overtime application update.',
        };

        return [
            'message' => $message,
            'overtime_application_id' => $this->application->id,
            'stage' => $this->stage,
        ];
    }
}
