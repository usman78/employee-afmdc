<?php

namespace App\Notifications;

use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoticeApprovalNotification extends Notification
{
    use Queueable;

    public Notice $notice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Notice $notice)
    {
        $this->notice = $notice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('A new notice has been posted that requires your approval.')
                    ->action('Review Notice', route('notices.review', $this->notice->id))
                    ->line('Please review and approve or reject this notice.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'New notice requires your approval: ' . $this->notice->title,
            'notice_id' => $this->notice->id,
            'notice_title' => $this->notice->title,
            'created_by' => $this->notice->created_by,
        ];
    }
}
