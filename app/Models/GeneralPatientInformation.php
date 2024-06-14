<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralPatientInformation extends Model
{
    protected $table = 'general_patient_informations';
    use HasFactory;

    protected $fillable = [
        'maritalStatus', 'nationality', 'status', 'reasonOfStatus',
        'educationalLevel', 'generalIncome', 'incomeType', 'sourceOfIncome',
        'workDetails', 'residenceType', 'patientID','valid'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'patientID', 'id');
    }

    public function maritalStatus()
    {
        return $this->hasOne(MaritalStatus::class, 'generalPatientInformationID', 'id');
    }


    
}
