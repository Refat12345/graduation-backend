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
        return response()->json(['appointments' =>$appointments], 200);
    } catch (\Exception $e) {
               
        return response()->json(['error' => $e->getMessage()], 400);
    }
    
    
    }

    
    public function assignAppointmentToUser($appointmentId, $userId)
    {
        try{
        $appointment = $this->service->assignAppointmentToUser($appointmentId, $userId);
        return response()->json(['appointment' =>$appointment], 200);
    } catch (\Exception $e) {
               
        return response()->json(['error' => $e->getMessage()], 400);
    }
    
    
    }

    public function swapAppointmentsBetweenUsers($appointmentId1, $appointmentId2)
    {
        try{
        $appointment = $this->service->swapAppointmentsBetweenUsers($appointmentId1, $appointmentId2);
        return response()->json(['appointment' =>$appointment], 200);
    } catch (\Exception $e) {
               
        return response()->json(['error' => $e->getMessage()], 400);
    }
    
    
    }

 

    public function populateAppointments($centerId)
    {
        try{
        $appointments = $this->service->populateAppointments($centerId);
        return response()->json([$appointments], 200);
    } catch (\Exception $e) {
               
        return response()->json(['error' => $e->getMessage()], 400);
    }
    
    
    }

  
    public function updateAppointmentsStatus($centerId)
    {
        try{
        $ss = $this->service->updateAppointmentsStatus($centerId);
        return response()->json([$ss], 200);
    } catch (\Exception $e) {
               
        return response()->json(['error' => $e->getMessage()], 400);
    }
    
    
    }



    public function getAppointmentsByCenterAndDate($centerId, $year, $month, $day)
    {
        try{
        $appointments = $this->service->getAppointmentsByCenterAndDate($centerId, $year, $month, $day);
        return response()->json(['appointments'=> $appointments], 200);
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
