<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergicCondition extends Model
{
    use HasFactory;
    protected $fillable = ['allergy', 'dateOfSymptomOnset', 'generalDetails', 'medicalRecordID'];
    
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medicalRecordID', 'id');
    }
}
