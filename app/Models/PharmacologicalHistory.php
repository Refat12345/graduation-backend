<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacologicalHistory extends Model
{
    use HasFactory;

    protected $fillable = ['medicineName', 'dateStart', 'dateEnd', 'generalDetails', 'medicalRecordID','valid'];
    
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medicalRecordID', 'id');
    }
}
