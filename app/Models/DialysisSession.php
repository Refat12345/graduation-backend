<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;





class DialysisSession extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sessionStartTime', 'sessionEndTime', 'weightBeforeSession',
        'weightAfterSession', 'totalWithdrawalRate', 'withdrawalRateHourly', 'pumpSpeed',
        'filterColor', 'filterType', 'vascularConnection', 'naConcentration',
        'venousPressure', 'status', 'sessionDate', 'patientID', 'nurseID', 'doctorID', 'centerID'
    ];
    
    public function patient()
    {
        return $this->belongsTo(User::class, 'patientID', 'id');
    }

  

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurseID', 'id');
    }

    
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctorID', 'id');
    }
    

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }






    public function bloodPressureMeasurements()
    {
        return $this->hasMany(BloodPressureMeasurement::class, 'sessionID', 'id');
    }



    public function medicineTakens()
    {
        return $this->hasMany(MedicineTaken::class, 'sessionID', 'id');
    }



    public function notes()
    {
        return $this->hasMany(Note::class, 'sessionID', 'id');
    }


}
