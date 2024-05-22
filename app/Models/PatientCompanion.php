<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCompanion extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullName',
        'degreeOfKinship',
        'userID',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function telecoms()
    {
        return $this->hasMany(Telecom::class, 'patientCompanionID', 'id');
    }

}
