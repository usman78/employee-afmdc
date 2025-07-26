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
            case 'App\Notifications\ServiceRequestToItNotification':
                return redirect()->route('service-requests.assignment', ['id' => $data['request_id']]);
            case 'App\Notifications\ServiceUpdateFromIt':
                return redirect()->route('service-requests.assignment-details', ['requestId' => $data['service_assignment_id']]);
            case 'App\Notifications\NewServiceRequestAssigned':    
                return redirect()->route('service-requests.assignment-details', ['requestId' => $data['request_id']]);
            default:
                return redirect()->route('service-requests.show', ['id' => $data['request_id']]); // Fallback route
        }
    }    
}