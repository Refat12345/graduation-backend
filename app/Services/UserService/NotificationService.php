<?php

declare(strict_types=1);

namespace App\Services\UserService;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging;

class NotificationService
{
    protected $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function sendNotification($token, $title, $body)
    {
        $notification = Notification::create($title, $body);
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification);

        $this->messaging->send($message);
    }




    public function  senddeviceTokenDeviceID ( $deviceToken , $deviceID )
    {

        $user = auth('user')->user();
        $devicetoken = new DeviceToken([
            'deviceToken' => $deviceToken,
            'deviceID' => $deviceID,
            'userID' => $user->id, 
        ]);
   
        $user->deviceTokens()->save($devicetoken);
        
    }

  
}
