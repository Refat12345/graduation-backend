<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestModifyAppointment extends Model
{
    protected $fillable = ['newTime', 'requestID', 'requesterID', 'appointmentID'];
    
    public function request(): BelongsTo
    {
        return $this->belongsTo(Requests::class, 'requestID', 'id');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesterID', 'id');
    }
    
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointmentID', 'id');
    }
}
