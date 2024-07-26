<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

 
    protected $fillable = ['appointmentTimeStamp','day', 'userID', 'shiftID', 'chairID','centerID', 'valid','nurseID','isValid'];
    

    public function updateappointmentTime($new)
    {
        $this->appointmentTimeStamp = $new;
        $this->save();
    }

    public function updateappointmentDay($new)
    {
        $this->day = $new;
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

  
}
