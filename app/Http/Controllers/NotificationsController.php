<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function handle($notificationId){
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        $notificationClass = $notification->type;
        $data = $notification->data;
        switch ($notificationClass) {
            case 'App\Notifications\TaskAssignedNotification':
            case 'App\Notifications\TaskCompletedNotification':
            case 'App\Notifications\TaskCommentNotification':
            case 'App\Notifications\TaskProgressNotification':
                return redirect()->route('employee-tasks.show', ['employeeTask' => $data['task_id']]);
            case 'App\Notifications\ServiceRequestToItNotification':
                return redirect()->route('service-requests.assignment', ['id' => $data['request_id']]);
            case 'App\Notifications\ServiceUpdateFromIt':
                return redirect()->route('service-requests.assignment-details', ['requestId' => $data['service_assignment_id']]);
            case 'App\Notifications\NewServiceRequestAssigned':    
                return redirect()->route('service-requests.assignment-details', ['requestId' => $data['request_id']]);
            default:
                if (isset($data['notice_id'])) {
                    return redirect()->route('notices.review', ['notice' => $data['notice_id']]);
                }

                if (isset($data['request_id'])) {
                    return redirect()->route('service-requests.show', ['id' => $data['request_id']]);
                }

                return redirect()->route('home');
        }
    }    
}
