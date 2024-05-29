<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PatientTransferRequest extends Model
{
    protected $fillable = ['patientID', 'centerPatientID', 'destinationCenterID', 'requestID'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'patientID', 'id');
    }
    
    public function centerPatient()
    {
        return $this->belongsTo(MedicalCenter::class, 'centerPatientID', 'id');
    }
    
    public function destinationCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'destinationCenterID', 'id');
    }
    
    public function request()
    {
        return $this->belongsTo(Requests::class, 'requestID', 'id');
    }
}
