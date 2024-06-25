<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;

        protected $fillable = [
            'fullName', 'password', 'nationalNumber', 'dateOfBirth', 
            'gender', 'accountStatus', 'role', 'verificationCode','valid'
        ];
    
        protected $hidden = [
            'password', 'verificationCode',
        ];




        public function generalPatientInformation()
        {
            return $this->hasOne(GeneralPatientInformation::class, 'patientID', 'id')
                        ->with(['maritalStatus']);
        }


        public function getAdminName()
        {
            if ($this->role === 'admin') {
                return $this->attributes['fullName'];
            }
        }

//////////////////////  جدول الجلسات بدون باقي التفاصيل //////////////////////////

        public function patientSessions(): HasMany
        {
            return $this->hasMany(DialysisSession::class, 'patientID', 'id');
        }
        


        public function nurseSessions(): HasMany
        {
            return $this->hasMany(DialysisSession::class, 'nurseID', 'id');
        }

        
        public function doctorSessions(): HasMany
        {
            return $this->hasMany(DialysisSession::class, 'doctorID', 'id');
        }



        
///////////////////////////////////////end/////////////////////////////////////////////
        




////////////////////// جلسات الغسيل مع باقي التفاصيل  //////////////////////////



public function patientSessionsWithRelatedData()
{
    return $this->hasMany(DialysisSession::class, 'patientID', 'id')
                ->with([
                    'bloodPressureMeasurements','medicineTakens' => function ($query) {
                        $query->with(['medicine' => function ($query) {
                            $query->select('id', 'name', 'titer');
                        }])->select('sessionID', 'value', 'medicineID');
                    },
                    'notes'
                ]);
}


public function nurseSessionsWithRelatedData()
{
    return $this->hasMany(DialysisSession::class, 'nurseID', 'id')
                ->with([
                    'bloodPressureMeasurements',
                    'medicineTakens' => function ($query) {
                        $query->with(['medicine' => function ($query) {
                            $query->select('id', 'name', 'titer');
                        }])->select('sessionID', 'value', 'medicineID');
                    },
                    'notes'
                ]);
}

public function doctorSessionsWithRelatedData()
{
    return $this->hasMany(DialysisSession::class, 'doctorID', 'id')
                ->with([
                    'bloodPressureMeasurements',
                    'medicineTakens' => function ($query) {
                        $query->with(['medicine' => function ($query) {
                            $query->select('id', 'name', 'titer');
                        }])->select('sessionID', 'value', 'medicineID');
                    },
                    'notes'
                ]);
}



///////////////////////////////////////end/////////////////////////////////////////////


        public function note(): HasMany
        {
            return $this->hasMany(Note::class, 'senderID', 'id');
        }


     
        public function prescription(): HasMany
        {
            return $this->hasMany(Prescription::class, 'patientID', 'id');
        }
    


        public function telecom()
        {
            return $this->hasMany(Telecom::class, 'userID', 'id');
        }
    


        public function address()
        {
            return $this->hasMany(Address::class, 'userID', 'id');
        }
    
        public function userAddressWithCityAndCountry()
        {
            return $this->hasMany(Address::class, 'userID', 'id')
                        ->with(['city' => function ($query) {
                            $query->with('country');
                        }]);
        }


        // public function generalPatientInformation(): HasOne
        // {
        //     return $this->hasOne(GeneralPatientInformation::class, 'patientID', 'id');
        // }


        public function medicalAnalysis(): HasMany
        {
            return $this->hasMany(MedicalAnalysis::class, 'userID', 'id')
                        ->with('analysisType');
        }
    
    

       
        public function medicalRecord()
        {
            return $this->hasOne(MedicalRecord::class, 'userID', 'id');
        }



 

   




        public function userAppointmentsWithShiftAndChair()
        {
            return $this->hasMany(Appointment::class, 'userID', 'id')
                        ->with(['shift', 'chair']);
        }


        public function medicalRecordWithHistories()
        {
            return $this->hasOne(MedicalRecord::class)->with([
                'pharmacologicalHistories',
                'surgicalHistories',
                'pathologicalHistories',
                'allergicConditions'
            ]);
        }

        public function patientCompanions()
        {
            return $this->hasMany(PatientCompanion::class, 'userID', 'id') ->with(['address', 'telecoms']);
        }


        public function hasRole($role)
        {
            return $this->role === $role;
        }




/////////////////////////// belongsToMany ///////////////////////////////


public function medicalCenters()
{
    return $this->belongsToMany(MedicalCenter::class, 'user_centers', 'userID', 'centerID');
}


public function prescriptions()  : HasMany
{
    return $this->hasMany(Prescription::class, 'patientID', 'id');
}



public function permissions()
{
    return $this->belongsToMany(Permission::class, 'user_permissions', 'userID', 'permissionID');
}
////////////////////////////////////////////////////////////////////////


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

  

    public function userShifts(): HasMany
    {
        return $this->hasMany(UserShift::class, 'userID', 'id');
    }

    
    public function userCenter()
    {
        return $this->belongsTo(UserCenter::class, 'id', 'userID');
    }


    
    public function userCenters(): HasMany
{
    return $this->hasMany(UserCenter::class, 'userID', 'id');
}


public function globalRequests()
{
    return $this->morphMany(GlobalRequest::class, 'requestable');
}

}

