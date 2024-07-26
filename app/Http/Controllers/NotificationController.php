<?php

namespace App\Http\Controllers;

use App\Services\UserService\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }



    public function sendNotification ( Request $request)
    {

        $token = $request->input('token');
        $title = $request->input('title'); 
        $body = $request->input('body'); 

        $this->notificationService->sendNotification($token, $title, $body);

        return response()->json(['message' => 'Notification sent successfully']);
    }



    public function senddeviceTokenDeviceID ( Request $request)
    {  

        $deviceToken = $request->input('deviceToken');
        $deviceID = $request->input('deviceID');
    
        $this->notificationService->senddeviceTokenDeviceID ( $deviceToken , $deviceID );
        return response()->json(['message' => 'data sent successfully']);
    }

    
  
}
