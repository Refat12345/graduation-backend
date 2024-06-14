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
        'userID','valid'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function telecoms()
    {
        return $this->hasMany(Telecom::class, 'patientCompanionID', 'id');
    }

    public function address()
    {
        return $this->hasMany(Address::class, 'patientCompanionID', 'id');
    }

    public function userAddressWithCityAndCountry()
    {
        return $this->hasMany(Address::class, 'patientCompanionID', 'id')
                    ->with(['city' => function ($query) {
                        $query->with('country');
                    }]);
    }


}
