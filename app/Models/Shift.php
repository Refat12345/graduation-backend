<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = ['shiftStart', 'shiftEnd','name', 'centerID','valid'];
    
    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }


    public function globalRequests()
{
    return $this->morphMany(GlobalRequest::class, 'requestable');
}

public function appointments()
{
    return $this->hasMany(Appointment::class, 'shiftID', 'id')
                ;
}







}
