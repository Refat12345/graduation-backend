<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HasOne;

class MedicalCenter extends Model
{
    use HasFactory;

    protected $fillable = ['centerName', 'description', 'charityName'];




    public function centertelecoms()
    {
        return $this->hasMany(Telecom::class, 'centerID', 'id');
    }




    public function centerAddressWithCityAndCountry()
    {
        return $this->hasOne(Address::class, 'centerID', 'id')
                    ->with(['city' => function ($query) {
                        $query->with('country');
                    }]);
    }
    

    public function centerFullInformation()
    {
        return $this->with(['centerAddressWithCityAndCountry', 'centertelecoms']);
    }



    public function centerAppointmentsWithShiftAndChair(): HasOne 
    {
        return $this->hasMany(Appointment::class, 'centerID', 'id')
                    ->with(['shift', 'chair']);
    }


   
        public function centerLoggings()
        {
            return $this->hasManyThrough(
                Logging::class,
                UserCenter::class,
                'centerID',
                'affectedUserID',
                'id', 
                'userID'  
            );
        }

        public function address()
        {
            return $this->hasMany(Address::class, 'centerID', 'id');
        }
    
     

/////////////////////////// belongsToMany ///////////////////////////////


    public function users()
    {
        return $this->belongsToMany(User::class, 'user_centers', 'centerID', 'userID');
    }







////////////////////////////////////////////////////////////////////////
}
