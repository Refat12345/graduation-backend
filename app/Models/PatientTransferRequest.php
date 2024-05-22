<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PatientTransferRequest extends Model
{
    protected $fillable = ['patientID', 'centerPatientID', 'destinationCenterID', 'requestID'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patientID', 'id');
    }
    
    public function centerPatient(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'centerPatientID', 'id');
    }
    
    public function destinationCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'destinationCenterID', 'id');
    }
    
    public function request(): BelongsTo
    {
        return $this->belongsTo(Requests::class, 'requestID', 'id');
    }
}
