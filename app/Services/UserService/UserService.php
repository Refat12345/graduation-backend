<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\UserServiceInterface;
use App\Models\User;
use App\Models\Telecom;
use App\Models\GeneralPatientInformation;
use App\Models\PatientCompanion;
use App\Models\Address;
use App\Models\City;
use App\Models\Permission;
use App\Models\MaritalStatus;
use App\Models\Country;
use App\Models\MedicalCenter;
use App\Models\UserCenter;
use App\Models\GlobalRequest;
use App\Models\PatientTransferRequest;
use App\Models\RequestModifyAppointment;
use App\Models\Requests;
use App\Models\Appointment;
use App\Models\Shift;
use App\Models\Chair;
use App\Models\UserShift;
use App\Models\DialysisSession;
use App\Models\Note;
use App\Models\Medicine;
use App\Models\MedicineTaken;
use App\Models\BloodPressureMeasurement;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\AllergicCondition;
use App\Models\AnalysisType;
use App\Models\MedicalAnalysis;
use App\Models\SurgicalHistory;

use App\Models\PathologicalHistory;
use App\Models\PharmacologicalHistory;
use App\Models\DisbursedMaterial;
use App\Models\DisbursedMaterialsUser;

use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Carbon\Carbon;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
class UserService implements UserServiceInterface
{

    protected $userModel;
    protected $requestsModel;

    public function __construct(User $userModel, Requests $requestsModel)
    {
        $this->userModel = $userModel;
        $this->requestsModel = $requestsModel;
    }

    // دكتور: doctor
    // ممرض: nurse
    // مريض: patient
    // مدير مركز: admin
    //  مدير برنامج : superAdmin
    // سكرتاريا: secretary

// request status : pending,approved,rejected

//  account status material status :       
//          active
//          nonActive



 // الحالة الاجتماعية :   waiting , accepted , Rejected  , pending

    /**
     * Create a new user account.
     *
     * @param array $userData
     * @return User
     * @throws LogicException
     */
  


     
     public function addPatientTransferRequest(array $data)
     {
        
         $validator = Validator::make($data, [
             'patientID' => 'required|exists:users,id',
             'centerPatientID' => 'required|exists:medical_centers,id',
             'destinationCenterID' => 'required|exists:medical_centers,id',
             'requestStatus' => 'required|in:pending,approved,rejected',
             'cause' => 'sometimes|required|string'
         ]);
     
         if ($validator->fails()) {
             return $validator->errors();
         }
         $request = new Requests();
         $request->requestStatus = $data['requestStatus'];
         $request->cause = $data['cause'] ?? null;
         $request->save();
     
         $patientTransferRequest = new PatientTransferRequest();
         $patientTransferRequest->patientID = $data['patientID'];
         $patientTransferRequest->centerPatientID = $data['centerPatientID'];
         $patientTransferRequest->destinationCenterID = $data['destinationCenterID'];
         $patientTransferRequest->requestID = $request->id;
         $patientTransferRequest->save();
     
         return $patientTransferRequest;
     }


     public function addGlobalRequest(array $data)
     {
         
         $validator = Validator::make($data, [
             'content' => 'required|string',
             'direction' => 'required|string',
            // 'requesterID' => 'required|exists:users,id',
             'reciverID' => 'required|exists:users,id',
             'requestStatus' => 'required|in:pending,approved,rejected',
             'cause' => 'sometimes|required|string'
         ]);
     
         if ($validator->fails()) {
             return $validator->errors();
         }
     
        
         $request = new Requests();
         $request->requestStatus = $data['requestStatus'];
         $request->cause = $data['cause'] ; 
         $request->save();
     
         $user=  auth('user')->user();
       
         $globalRequest = new GlobalRequest();
         $globalRequest->content = $data['content'];
         $globalRequest->direction = $data['direction'];
         $globalRequest->requestID = $request->id; 
         $globalRequest->requesterID = $user->id;
         $globalRequest->reciverID = $data['reciverID'];
         $globalRequest->save();
     
         return $globalRequest;
     }
     
     
 
     
     
     
     
     public function addRequestModifyAppointment(array $data)
     {
         
         $validator = Validator::make($data, [
             'newTime' => 'required|date_format:Y-m-d H:i:s',
             'appointmentID' => 'required|exists:appointments,id',
           //  'requesterID' => 'required|exists:users,id',
             'requestStatus' => 'required|in:pending,approved,rejected',
             'cause' => 'sometimes|required|string'
         ]);
     
         if ($validator->fails()) {
             return $validator->errors();
         }
     
        
         $request = new Requests();
         $request->requestStatus = $data['requestStatus'];
         $request->cause = $data['cause'] ?? null;
         $request->save();
     
         
         $user=  auth('user')->user();
     
         $requestModifyAppointment = new RequestModifyAppointment();
         $requestModifyAppointment->newTime = $data['newTime'];
         $requestModifyAppointment->appointmentID = $data['appointmentID'];
         $requestModifyAppointment->requestID = $request->id;
         $requestModifyAppointment->requesterID =  $user->id;
         $requestModifyAppointment->save();
     
         return $requestModifyAppointment;
     }
     
     
 
 
 
     public function getAllRequests()
     {
         $user = auth('user')->user();
         $userId = $user->id;
       
         $centerIds = UserCenter::where('userID', $userId)->pluck('centerID')->toArray();
     
         return Requests::whereHas('globalRequest', function ($query) use ($centerIds) {
             $query->whereIn('requesterID', $centerIds);
         })
         ->orWhereHas('patientTransferRequest', function ($query) use ($centerIds) {
             $query->whereIn('centerPatientID', $centerIds)
                   ->orWhereIn('destinationCenterID', $centerIds);
         })
         ->orWhereHas('requestModifyAppointment', function ($query) use ($centerIds) {
             $query->whereIn('requesterID', $centerIds);
         })
         ->with(['globalRequest', 'patientTransferRequest', 'requestModifyAppointment'])
         ->get();
     }
     
 
 
     
     
 public function updateStatus( $requestId, $newStatus)
 {
     $validator = Validator::make([
         'request_id' => $requestId, 
         'new_status' => $newStatus
     ], [
         'request_id' => 'required|integer|exists:requests,id', 
         'new_status' => 'required|string|in:pending,approved,rejected', 
     ]);
 
     
     if ($validator->fails()) {
         throw new InvalidArgumentException($validator->errors()->first());
     }
 
   $validatedData = $validator->validated();
 
     $requestModel = Requests::findOrFail($validatedData['request_id']);
     $requestModel->updateRequestStatus($validatedData['new_status']);
 }
 
 
















 public function createUser(array $userData): User
 {
     DB::beginTransaction();
     $validator = Validator::make($userData, [
         'fullName' => 'required|string|max:255',
         'password' => 'required|string|min:8',
         'nationalNumber' => 'required|string|max:11|unique:users',
         'dateOfBirth' => 'required|date',
         'gender' => 'required|in:male,female,other',
         'accountStatus' => 'required|string|max:255',
         'role' => 'required|string|max:255',
         'telecom' => 'required|array',
         'telecom.*.system' => 'required|string|max:255',
         'telecom.*.value' => 'required|string|max:255',
         'telecom.*.use' => 'required|string|max:255',
         'address' => 'required|array',
         'address.*.line' => 'required|string|max:255',
         'address.*.use' => 'required|string|max:255',
         'address.*.cityName' => 'required|string|max:255',
         'address.*.countryName' => 'required|string|max:255',
         'centerName' => 'required|string|max:255',
     ]);
 
     if ($validator->fails()) {
         throw new LogicException($validator->errors()->first());
     }
 
     try {
         $userData['verificationCode'] = rand(1000, 9999);
         $userData['password'] = Hash::make($userData['password']);
         $user = User::create($userData);
 
         $this->createUserTelecoms($user, $userData['telecom']);
         foreach ($userData['address'] as $addressData) {
             $this->createUserAddress($user, $addressData);
         }
         $this->associateUserWithMedicalCenter($user, $userData['centerName']);
 

         
         DB::commit();
         return $user;
     } catch (\Exception $e) {
         DB::rollBack();
         throw new LogicException('Error creating user: ' . $e->getMessage());
     }
 }
 



     
     public function createUserTelecoms(User $user, array $telecomData)
     {
        try{
         foreach ($telecomData as $data) {
          
             $data['userID'] = $user->id;
             $telecom = new Telecom($data);
             $user->telecom()->save($telecom);
         }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new LogicException('Error creating user: ' . $e->getMessage());
        }
     }

 
     

 
     public function createUserAddress(User $user, array $addressData)
     {
    
         $city = City::firstOrCreate(['cityName' => $addressData['cityName']]);
         $country = Country::firstOrCreate(['countryName' => $addressData['countryName']]);
   
         $city->country()->associate($country);

         $city->save();
     
         $address = new Address([
             'line' => $addressData['line'],
             'use' => $addressData['use'],
             'cityID' => $city->id,
             'userID' => $user->id, 
         ]);

         $user->address()->save($address);
     }
     



     public function createCenterAddress(MedicalCenter $center, array $addressData)
     {
    
         $city = City::firstOrCreate(['cityName' => $addressData['cityName']]);
         if (!$city) {
            throw new Exception('Failed to create or find the city.');
        }
         $country = Country::firstOrCreate(['countryName' => $addressData['countryName']]);
         if (!$country) {
            throw new Exception('Failed to create or find the country.');
        }
         $city->country()->associate($country);
         $city->save();
     
         $address = new Address([
             'line' => $addressData['line'],
             'use' => $addressData['use'],
             'cityID' => $city->id,
             'centerID' => $center->id, 
         ]);
         if (!$address) {
            throw new Exception('Failed to create or find the address.');
        }

         $center->address()->save($address);
     }

     


     public function associateUserWithMedicalCenter(User $user, string $centerName)
{

    $medicalCenter = MedicalCenter::firstOrCreate(['centerName' => $centerName]);
    $user->medicalCenters()->attach($medicalCenter->id);
}

     


     public function findUserBy(string $value): Collection

     {
         if (preg_match('/^\d{10}$/', $value)) {
             return User::whereHas('telecom', function ($query) use ($value) {
                 $query->where('value', $value);
             })->get();
         } elseif (preg_match('/^\d{11}$/', $value)) {
             return User::where('nationalNumber', $value)->get();
         } elseif (!empty($value)) {
             $keywords = explode(' ', $value);
             $query = User::query();
     
             foreach ($keywords as $keyword) {
                 $query->orWhere('fullName', 'LIKE', "%{$keyword}%");
             }
     
             return $query->get();
         } else {
             throw new InvalidArgumentException("Invalid search value: {$value}");
         }
     }
     



public function changeAccountStatus(User $user, string $newStatus)
{
    $user->update(['accountStatus' => $newStatus]);
}




public function verifyAccount(User $user, string $verificationCode)
{
    if ($user->verificationCode === $verificationCode) {
        $user->update(['accountStatus' => 'verified']);
    } else {
        throw new LogicException('Verification code is invalid.');
    }
}


    public function loginUser(string $nationalNumber, string $password): ?User
    {
        if (Auth::attempt(['nationalNumber' => $nationalNumber, 'password' => $password])) {
            return Auth::user();
        }

        return null;
    }



    public function addGeneralPatientInformationWithMaritalStatus(array $data)
    {
        $validator = Validator::make($data, [
            'maritalStatus' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'reasonOfStatus' => 'required|string|max:255',
            'educationalLevel' => 'required|string|max:255',
            'generalIncome' => 'required|numeric',
            'incomeType' => 'required|string|max:255',
            'sourceOfIncome' => 'required|string|max:255',
            'workDetails' => 'required|string|max:255',
            'residenceType' => 'required|string|max:255',
           // 'patientID' => 'required|integer|exists:users,id',
            'patientID' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'patient');
                }),
            ],
           
            'childrenNumber' => 'required|integer',
            'healthStateChildren' => 'required|string|max:255',
        ]);
    
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    
        DB::transaction(function () use ($data) {
            $generalPatientInfo = GeneralPatientInformation::create($data);
            $maritalStatusData = [
                'childrenNumber' => $data['childrenNumber'],
                'healthStateChildren' => $data['healthStateChildren'],
                'generalPatientInformationID' => $generalPatientInfo->id
            ];
            MaritalStatus::create($maritalStatusData);
        });
    }



    public function addPermissionsToUser($userId, array $permissionNames)
    {
        DB::transaction(function () use ($userId, $permissionNames) {
            $user = User::findOrFail($userId);
    
            foreach ($permissionNames as $permissionName) {
                $permission = Permission::firstOrCreate(['permissionName' => $permissionName]);
                $user->permissions()->syncWithoutDetaching([$permission->id]);
            }
        });
    }


    public function getUserPermissions($userId)
    {
        $user = User::findOrFail($userId);
        return $user->permissions()->pluck('permissionName');
    }
    








public function addPatientCompanionWithTelecom(array $companionData, array $telecomDataArray)
{
    $companionValidator = Validator::make($companionData, [
        'fullName' => 'required|string|max:255',
        'degreeOfKinship' => 'required|string|max:255',
        'userID' => 'required|integer|exists:users,id',
    ]);

    if ($companionValidator->fails()) {
        throw new InvalidArgumentException($companionValidator->errors()->first());
    }

    DB::transaction(function () use ($companionData, $telecomDataArray) {
        $patientCompanion = PatientCompanion::create($companionData);

        foreach ($telecomDataArray as $telecomData) {
            $telecomValidator = Validator::make($telecomData, [
                'system' => 'required|string|max:255',
                'value' => 'required|string|max:255',
                'use' => 'required|string|max:255',
                
            ]);

            if ($telecomValidator->fails()) {
                throw new InvalidArgumentException($telecomValidator->errors()->first());
            }

            $telecomData['patientCompanionID'] = $patientCompanion->id;
            Telecom::create($telecomData);
        }
    });
}


public function addMedicalCenterWithUser(array $centerData)
{
    $validator = Validator::make($centerData, [
        'centerName' => 'required|string|max:255',
        'description' => 'required|string|max:1000',
        'charityName' => 'nullable|string|max:255',
        'telecom' => 'required|array',
        'telecom.*.system' => 'required|string|max:255',
        'telecom.*.value' => 'required|string|max:255',
        'telecom.*.use' => 'required|string|max:255',
        'address' => 'required|array',
        'address.use' => 'required|string|max:255',
        'address.line' => 'required|string',
        'address.cityName' => 'required|string|max:255',
        'address.countryName' => 'required|string|max:255',
       
    ]);

    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }

    DB::beginTransaction();
    try {
        $user = auth('user')->user();
        $medicalCenters = $user->userCenter()->first();
        $medicalCenter = MedicalCenter::find($medicalCenters->centerID);
        if (!$medicalCenter) {
            throw new LogicException('No medical center associated with the user.');
        }

        $medicalCenter->update([
                'centerName' => $centerData['centerName'],
                'description' => $centerData['description'],
                'charityName' => $centerData['charityName'] ?? null,
            ]);

        foreach ($centerData['telecom'] as $telecom) {
            $telecom['centerID'] = $medicalCenter->id;
            Telecom::create($telecom);
        }

       
        $this->createCenterAddress($medicalCenter, $centerData['address']);

        UserCenter::create([
            'userID' => $user->id,
            'centerID' => $medicalCenter->id,
        ]);

        DB::commit();
        return $medicalCenter;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error creating medical center: ' . $e->getMessage());
    }
}




public function getAllMedicalCenters()
{   
    $medicalCenters = MedicalCenter::with('centertelecoms', 'address')
                                   ->get()
                                   ->map(function ($center) {
                                       return [
                                           'centerName' => $center->centerName,
                                           'telecom' => $center->centertelecoms->map(function ($telecom) {
                                               return [
                                                   'use' => $telecom->use,
                                                   'value' => $telecom->value
                                               ];
                                           }),
                                           'address' => $center->address->map(function ($address) {
                                               return $address->line . ', ' . $address->city->cityName . ', ' . $address->city->country->countryName;
                                           })->implode(', '),
                                           'description' => $center->description
                                       ];
                                   });

    return $medicalCenters;
}





 public function addChair(array $data)
 {
    
     $validatedData = Validator::make($data, [
         'chairNumber' => 'required|integer|max:255',
         'roomName' => 'required|string|max:255',
     
     ])->validate();
 
     $user = auth('user')->user();
     $centerId = UserCenter::where('userID', $user->id)->first()->centerID;
     $chair = new Chair([
         'chairNumber' => $validatedData['chairNumber'],
         'roomName' => $validatedData['roomName'],
         'centerID' => $centerId
     ]);
     $chair->save();
     return $chair;
 }
 




 public function addShift(array $data)
 {
    
    $validatedData = Validator::make($data, [
        'shiftStart' => 'required|date',
        'shiftEnd' => 'required|date|after:shiftStart',
        
        
    ])->validate();
 
     $user = auth('user')->user();
     $centerId = UserCenter::where('userID', $user->id)->first()->centerID;
     $shift = new Shift([
         'shiftStart' => $validatedData['shiftStart'],
         'shiftEnd' => $validatedData['shiftEnd'],
         'centerID' => $centerId
     ]);
     $shift->save();
     return $shift;
 }





public function assignUserToShift(array $data)
{
    $validator = Validator::make($data, [
        'userID' => [
            'required',
            'integer',
            Rule::exists('users', 'id')->where(function ($query) {
                $query->where('role', 'nurse');
            }),
        ],
        'shiftID' => 'required|exists:shifts,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => 'Validation failed',
            'messages' => $validator->errors(),
        ], 422);
    }

    $userShift = new UserShift($validator->validated());
    $userShift->save();

    return response()->json([
        'message' => 'تم تعيين المستخدم إلى الورديّة بنجاح',
        'userShift' => $userShift,
    ]);
}




public function getShiftsByCenter($centerId)
{
    $shifts = Shift::where('centerID', $centerId)->get();

    if ($shifts->isEmpty()) {
        return 'لا توجد ورديات متاحة لهذا المركز';
    }

    return $shifts ;
}




public function getDoctorsInShift($shiftId)
{
    $doctors = User::whereHas('userShifts', function ($query) use ($shiftId) {
        $query->where('shiftID', $shiftId);
    })->get();



    if ($doctors->isEmpty()) {
        return 'لا يوجد ممرضين معينين لهذه الوردية';
    }

    return  $doctors ;
}


public function getCenterUsersByRole($centerId, $role, $pat)
{
    return User::when($centerId != 0, function ($query) use ($centerId) {
        $query->whereHas('userCenter', function ($subQuery) use ($centerId) {
            $subQuery->where('centerID', $centerId);
        });
    })
    ->where('role', $role)
    ->select('id', 'fullName', 'accountStatus', 'gender', 'role', 'dateOfBirth') 
    ->with(['telecom' => function ($query) {
        $query->where('system', 'phone') 
              ->select('userID', 'value');
    }, 'address.city' => function ($query) {
        $query->select('id', 'cityName');
    }])
    ->when($role === 'patient', function ($query) use ($pat) {
        $query->whereHas('generalPatientInformation', function ($subQuery) use ($pat) {
            $subQuery->where('status', $pat); 
        });
    })
    ->get()
    ->map(function ($user) {
        $user->contactNumber = $user->telecom->pluck('value')->first() ?? null; 
        $user->city = $user->address->first()->city->cityName ?? null;
        $user->age = Carbon::parse($user->dateOfBirth)->age; 
        unset($user->telecom, $user->address, $user->dateOfBirth);
        return $user;
    });
}












// public function getCenterUsersByRole($centerId, $role)
// {
//     return User::when($centerId != 0, function ($query) use ($centerId) {
//         $query->whereHas('userCenter', function ($subQuery) use ($centerId) {
//             $subQuery->where('centerID', $centerId);
//         });
//     })
//     ->where('role', $role)
//     ->select('id', 'fullName', 'accountStatus', 'gender', 'role', 'dateOfBirth') 
//     ->with(['telecom' => function ($query) {
//         $query->where('system', 'phone') 
//               ->select('userID', 'value');
//     }, 'address.city' => function ($query) {
//         $query->select('id', 'cityName');
//     }])
//     ->get()
    
//     ->map(function ($user) {
//         $user->contactNumber = $user->telecom->pluck('value')->first() ?? null; 
//         $user->city = $user->address->first()->city->cityName ?? null;
//         $user->age = Carbon::parse($user->dateOfBirth)->age; 
//         unset($user->telecom, $user->address, $user->dateOfBirth);
//         return $user;
//     });
// }



public function getUserDetails($userId)
{
    $user = User::with([
                    'telecom', 
                    'userAddressWithCityAndCountry.city.country', 
                ])
                ->findOrFail($userId);

    $userDetails = [
        'fullName' => $user->fullName,
        'accountStatus' => $user->accountStatus,
        'gender' => $user->gender,
        'nationalNumber' => $user->nationalNumber,
        'dateOfBirth' => $user->dateOfBirth,
        'role' => $user->role,
        'telecom' => $user->telecom->map(function ($telecom) {
            return [
                'system' => $telecom->system,
                'value' => $telecom->value,
                'use' => $telecom->use
            ];
        })->toArray(),
        'address' => []
    ];

    // تحقق من أن العلاقة تعود بمجموعة من العناوين

        foreach ($user->userAddressWithCityAndCountry as $address) {
            // تحقق من أن كل عنصر هو كائن قبل الوصول إلى خصائصه
           
                $userDetails['address'][] = [
                    'line' => $address->line,
                    'use' => $address->use,
                    'cityName' => $address->city->cityName,
                    'countryName' => $address->city->country->countryName
                ];
            
        }
    

    return $userDetails;
}






public function getMedicalCenterDetails($centerId)
{

    $medicalCenter = MedicalCenter::with(['centertelecoms', 'centerAddressWithCityAndCountry'])
    ->findOrFail($centerId);

    return $medicalCenter;

}



public function createNote(array $noteData)
{
 
    $validator = Validator::make($noteData, [
        'noteContent' => 'required|string|max:1000',
        'category' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'date' => 'required|date',
        'sessionID' => 'nullable|integer|exists:dialysis_sessions,id',
        //'senderID' => 'required|integer|exists:users,id',
        'receiverID' => 'nullable|integer|exists:users,id',
        'centerID' => 'required|integer|exists:medical_centers,id',
    ]);   

    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }
   
    $noteData['senderID'] = auth('user')->user()->id;

    $note = Note::create($noteData);

    return $note;
}





public function getNotesByMedicalCenter($centerId)
{
    $notes = Note::where('centerID', $centerId)->get();

    return $notes;
}












}


