<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
  
    protected $fillable = ['requestStatus', 'cause'];

    
    public function globalRequest()
    {
        return $this->hasOne(GlobalRequest::class, 'requestID', 'id');
    }

 
    public function patientTransferRequest()
    {
        return $this->hasOne(PatientTransferRequest::class, 'requestID', 'id');
    }

   
    public function requestModifyAppointment()
    {
        return $this->hasOne(RequestModifyAppointment::class, 'requestID', 'id');
    }


    public static function getAllRequests()
    {
        return self::with(['globalRequest', 'patientTransferRequest', 'requestModifyAppointment'])->get();
    }




    public function updateRequestStatus($newStatus)
    {
        $this->requestStatus = $newStatus;
        $this->save();
    }


    public static function getRequestsByStatus($status)
    {
        return self::where('requestStatus', $status)->get();
    }

 
    public function deleteRequest()
    {
        $this->delete();
    }

}


