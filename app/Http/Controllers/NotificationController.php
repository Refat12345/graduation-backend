<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function send( $token , $title , $body )
    {
        $this->notificationService->sendNotification($token, $title, $body);

        return response()->json(['message' => 'Notification sent successfully']);
    }
}
