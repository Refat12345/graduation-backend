<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\Services\UserService\AppointmentServiceInterface;      

use App\Models\User;
use App\Models\GlobalRequest;
use App\Models\PatientTransferRequest;
use App\Models\RequestModifyAppointment;
use App\Models\Requests;

class AppointmentController extends Controller
{
    protected $service;

    public function __construct(AppointmentServiceInterface $service) {
        $this->service = $service;
    }


    public function createAppointment(Request $request)
    {
        try {
        $appointment = $this->service->addAppointment($request->all());
        return response()->json([$appointment], 200);
    } catch (\Exception $e) {
               
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }
    
    
    
    public function showAppointmentsByCenter($centerId)
    {
        try{
        $appointments = $this->service->getAppointmentsByCenter($centerId);
        return response()->json([$appointments], 200);
    } catch (\Exception $e) {
               
        return response()->json(['error' => $e->getMessage()], 400);
    }
    
    
    }
    
    
    
    public function showUserAppointments($userId)
    {
        try { 
        $appointments = $this->service->getUserAppointments($userId);
    
        return response()->json([$appointments], 200);
    } catch (\Exception $e) {
               
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }
    
    
    
    



}
