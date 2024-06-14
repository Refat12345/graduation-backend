<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestModifyAppointment extends Model
{
    protected $fillable = ['newTime', 'requestID', 'requesterID', 'appointmentID','valid'];
    
    public function request()
    {
        return $this->belongsTo(Requests::class, 'requestID', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'requesterID', 'id');
    }
    
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointmentID', 'id');
    }
}
