<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PathologicalHistory extends Model
{
    use HasFactory;

    protected $fillable = ['illnessName', 'medicalDiagnosisDate', 'generalDetails', 'medicalRecordID','valid'];
    
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medicalRecordID', 'id');
    }
}
