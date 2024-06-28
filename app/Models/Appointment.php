<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

 
    protected $fillable = ['appointmentTimeStamp', 'userID', 'shiftID', 'chairID','centerID', 'sessionID','valid','nurseID'];
    

    public function updateappointmentTime($new)
    {
        $this->appointmentTimeStamp = $new;
        $this->save();
    }



    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }
    
    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurseID', 'id');
    }

    
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shiftID', 'id');
    }
    
    public function chair()
    {
        return $this->belongsTo(Chair::class, 'chairID', 'id');
    }

    public function center()
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }

    public function session()
    {
        return $this->belongsTo(DialysisSession::class, 'sessionID', 'id');
    }
}
