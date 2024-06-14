<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Telecom extends Model
{
    use HasFactory;

    protected $fillable = ['system', 'value', 'use', 'userID', 'centerID','patientCompanionID','valid'];
    
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }

    
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }


    public function patientCompanion(): BelongsTo
    {
        return $this->belongsTo(PatientCompanion::class, 'patientCompanionID', 'id');
    }

}
