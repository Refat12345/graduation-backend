<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'dialysisStartDate', 'dryWeight', 'bloodType', 'vascularEntrance',
        'kidneyTransplant', 'causeRenalFailure', 'userID','valid'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }



    public function allergicConditions()
    {
        return $this->hasMany(AllergicCondition::class, 'medicalRecordID', 'id');
    }


    public function pathologicalHistories()
    {
        return $this->hasMany(PathologicalHistory::class, 'medicalRecordID', 'id');
    }


    public function pharmacologicalHistories()
    {
        return $this->hasMany(PharmacologicalHistory::class, 'medicalRecordID', 'id');
    }


    public function surgicalHistories()
    {
        return $this->hasMany(SurgicalHistory::class, 'medicalRecordID', 'id');
    }



}
