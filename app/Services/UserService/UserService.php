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
use App\Models\Appointment;
use App\Models\Requests;
use App\Models\ointmentointment;
use App\Models\Shift;
use App\Models\Chair;
use App\Models\UserShift;
use App\Models\DialysisSession;
use App\Models\Note;
use App\Models\Medicine;
use App\Models\MedicineTaken;
use App\Models\Logging;
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

    public function addGlobalRequest(array $data)
    {
        $validator = Validator::make($data, [
            'operation' => 'required|string',
            'requestable_id' => 'required|integer',
            'requestable_type' => 'required|string ',
            'requestStatus' => 'required|in:pending,approved,rejected',
            'cause' => 'sometimes|required|string'
        ]);
    
        if ($validator->fails()) {
            return $validator->errors();
        }
        $user=  auth('user')->user();
        $request = new Requests();
        $request->center_id=$user->center->centerID;
        $request->requestStatus = $data['requestStatus'];
        $request->cause = $data['cause'] ; 
        $request->save();
    
       
        $globalRequest = new GlobalRequest();
        $globalRequest->content = $data['operation'];
        $globalRequest->requestID = $request->id; 
        $globalRequest->requestable_id = $data['requestable_id'];
        $globalRequest->requestable_type = $data['requestable_type'];
        $globalRequest->requesterID = $user->id;
        $globalRequest->save();

        return $globalRequest;
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
  


 public function createUser(array $userData): User
 {
     DB::beginTransaction();
     $validator = Validator::make($userData, [
         'fullName' => 'required|string|max:255',
       //  'password' => 'required|string|min:8',
         'nationalNumber' => 'required|string|max:11|unique:users',
         'dateOfBirth' => 'required|date',
         'gender' => 'required|in:male,female,other',
       //  'accountStatus' => 'required|string|max:255',
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
       //  'centerName' => 'required|string|max:255',
        'permissionNames' => 'array'
     ]);
 
     if ($validator->fails()) {
         throw new LogicException($validator->errors()->first());
     }
 
     try {
      
        
        $us=  auth('user')->user();
        if ($us->role != 'superAdmin')
        {
            $userCenter = $us->userCenters()->where('valid', -1)->first();
            $centerId = $userCenter ? $userCenter->centerID : null;
            $centerName = $userCenter ? $userCenter->medicalCenter->centerName: null;
            $userData['centerName']=$centerName;


        }

         $userData['verificationCode'] = rand(100000, 999999);
        // $userData['password'] = Hash::make($userData['password']);
         $user = User::create($userData);
       
         $this->createUserTelecoms($user, $userData['telecom']);
         foreach ($userData['address'] as $addressData) {
             $this->createUserAddress($user, $addressData);
         }
         $this->associateUserWithMedicalCenter($user, $userData['centerName']);
         if ($userData['role'] === 'secretary') {
          
            $this->addPermissionsToUser($user->id, $userData['permissionNames']);
        }
        
        if (!in_array($userData['role'], ['secretary', 'admin'])) {
        $globalRequestData = [
            'operation' => 'انشاء حساب مستخدم',
            'requestable_id' => $user->id,
            'requestable_type' => User::class,
            'requestStatus' => 'pending',
            'cause' => '.'
        ];

        
        
        $this->addGlobalRequest($globalRequestData);
  }

  else {
    $user->valid= -1;
    $user->save(); 
}    
         DB::commit();
         return $user;
     } catch (\Exception $e) {
         DB::rollBack();
         throw new LogicException('Error creating user: ' . $e->getMessage());
     }
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



     public function updatePermissionsUser($userId, array $permissionNames)
{
    DB::transaction(function () use ($userId, $permissionNames) {
        $user = User::findOrFail($userId);
        $permissionsIds = Permission::whereIn('permissionName', $permissionNames)->pluck('id')->toArray();
        $user->permissions()->sync($permissionsIds);
    });
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





         ////////////////////////// update user ////////////////////////////




//          public function updateUser(array $userData, $userId)
//          {
//              DB::beginTransaction();

//              $validator = Validator::make($userData, [
//                 'fullName' => 'required|string|max:255',
//                 'nationalNumber' => 'required|string|max:11|unique:users',
//                 'dateOfBirth' => 'required|date',
//                 'gender' => 'required|in:male,female,other',
//                 'role' => 'required|string|max:255',
//                 'telecom' => 'required|array',
//                 'telecom.*.system' => 'required|string|max:255',
//                 'telecom.*.value' => 'required|string|max:255',
//                 'telecom.*.use' => 'required|string|max:255',
//                 'address' => 'required|array',
//                 'address.*.line' => 'required|string|max:255',
//                 'address.*.use' => 'required|string|max:255',
//                 'address.*.cityName' => 'required|string|max:255',
//                 'address.*.countryName' => 'required|string|max:255',
//                 'centerName' => 'required|string|max:255',
//                  'permissionNames' => 'array'
//             ]);

//             if ($validator->fails()) {
//                 throw new LogicException($validator->errors()->first());
//             }
            
//              try {
//                  $originalUser = User::findOrFail($userId);
         
//                  $editUser = $originalUser->replicate();
//                  $editUser->fill($userData);
         
//                  $editUser->nationalNumber = 'temp_' . $editUser->nationalNumber;


//     $this->updateUserTelecom($originalUser, $userData['telecom']);
        
//     $editUser->valid = $originalUser->id;
//     $editUser->save();

//     if (isset($userData['address'])) {
//         foreach ($userData['address'] as $addressData) {
//             $this->updateUserLocation($originalUser, $addressData);
//         }
//     }

//     if (isset($userData['permissionNames'])) {
//         $this->updatePermissionsToUser($editUser->id, $userData['permissionNames']);
//     }

//     DB::commit();
//     return $editUser;
// } catch (\Exception $e) {
//     DB::rollBack();
//     throw new LogicException('Error updating user: ' . $e->getMessage());
// }
// }

         
         
         public function updateUserLocation(User $user, array $locationData)
         {
             DB::beginTransaction();
             try {
                 $originalAddress = Address::where('userID', $user->id)->firstOrFail();
                 $originalCity = City::findOrFail($originalAddress->cityID);
                 $originalCountry = Country::findOrFail($originalCity->country_id);
         
                 $editCity = $originalCity->replicate();
                 $editCountry = $originalCountry->replicate();
         
                 $editCity->fill(['cityName' => $locationData['cityName']]);
                 $editCountry->fill(['countryName' => $locationData['countryName']]);
         
                 $editCity->valid = $originalCity->id;
                 $editCountry->valid = $originalCountry->id;
         
                 $editCity->save();
                 $editCountry->save();
         
                 $editAddress = $originalAddress->replicate();
                 $editAddress->cityID = $editCity->id;
                 $editAddress->valid = $originalAddress->id;
                 $editAddress->fill($locationData);
                 $editAddress->save();
         
                 DB::commit();
                 return ['city' => $editCity, 'country' => $editCountry, 'address' => $editAddress];
             } catch (\Exception $e) {
                 DB::rollBack();
                 throw new LogicException('Error updating location: ' . $e->getMessage());
             }
         }
         
         public function updateUserTelecom(User $user, array $telecomData)
         {
             DB::beginTransaction();
             try {
                 $originalTelecoms = Telecom::where('userID', $user->id)->get();
         
                 $editTelecoms = [];
                 foreach ($originalTelecoms as $originalTelecom) {
                     $editTelecom = $originalTelecom->replicate();
                     $editTelecom->fill($telecomData);
                     $editTelecom->valid = $originalTelecom->id;
                     $editTelecom->value = 'temp_' . $originalTelecom->value;
                     $editTelecom->save();
                     $editTelecoms[] = $editTelecom;
                 }
         
                 DB::commit();
                 return $editTelecoms;
             } catch (\Exception $e) {
                 DB::rollBack();
                 throw new LogicException('Error updating telecom: ' . $e->getMessage());
             }
         }
         


         public function approveUserEdits($originalUserId)
{
    DB::beginTransaction();
    try {
        $editUsers = User::where('valid', $originalUserId)->get();

        foreach ($editUsers as $editUser) {
            $originalUser = User::findOrFail($originalUserId);

            $originalUser->fill($editUser->getAttributes());
            $originalUser->save();

            $this->approveLocationEdits($editUser);
            $this->approveTelecomEdits($editUser);

            $editUser->delete();
        }

        DB::commit();
        return $originalUser;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error approving user edits: ' . $e->getMessage());
    }
}



public function approveLocationEdits(User $editUser)
{
    $editAddresses = Address::where('valid', $editUser->id)->get();
    foreach ($editAddresses as $editAddress) {
        $originalAddress = Address::findOrFail($editAddress->valid);
        $originalAddress->fill($editAddress->getAttributes());
        $originalAddress->save();
        $editAddress->delete();
    }
}

public function approveTelecomEdits(User $editUser)
{
    $editTelecoms = Telecom::where('valid', $editUser->id)->get();
    foreach ($editTelecoms as $editTelecom) {
        $originalTelecom = Telecom::findOrFail($editTelecom->valid);
        $originalTelecom->fill($editTelecom->getAttributes());
        $originalTelecom->save();
        $editTelecom->delete();
    }
}











//////////////////////////////////////////////////////////////////////////////////////////////






























    









     
     public function addPatientTransferRequest(array $data)
     {
        
         $validator = Validator::make($data, [
             'patientID' => 'required|exists:users,id',
             'centerPatientID' => 'required|exists:medical_centers,id',
             'destinationCenterID' => 'required|exists:medical_centers,id',
            // 'requestStatus' => 'required|in:pending,approved,rejected',
             'cause' => 'sometimes|required|string'
         ]);
     
         if ($validator->fails()) {
             return $validator->errors();
         }
         $request = new Requests();
         $us=  auth('user')->user();
         $request->center_id=$us->center->centerID;
         $request->requestStatus = 'pending';
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
         $user=  auth('user')->user();
         $request->center_id=$user->center->centerID;
         $request->requestStatus = $data['requestStatus'];
         $request->cause = $data['cause'] ?? null;
         $request->save();
     
         
       
     
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
         $centerId = $user->center->centerID; // تأكد من أن العلاقة center محددة بشكل صحيح في نموذج المستخدم
     
         // تحديث الاستعلامات للتحقق من center_id في جدول Requests
         $requests = Requests::where('center_id', $centerId)
             ->whereHas('globalRequest', function ($query) use ($centerId) {
                 $query->where('center_id', $centerId);
             })
             ->orWhereHas('patientTransferRequest', function ($query) use ($centerId) {
                 $query->where(function ($q) use ($centerId) {
                     $q->where('centerPatientID', $centerId)
                       ->orWhere('destinationCenterID', $centerId);
                 });
             })
             ->orWhereHas('requestModifyAppointment', function ($query) use ($centerId) {
                 $query->where('center_id', $centerId);
             })
             ->with(['globalRequest', 'patientTransferRequest', 'requestModifyAppointment'])
             ->get();
     
         return $this->mapRequests($requests);
     }

    // public function getAllRequests()
    // {
    //     $user = auth('user')->user();
    //     $centerId = $user->center->centerID; // تأكد من أن العلاقة center محددة بشكل صحيح في نموذج المستخدم
    
    //     // جلب الطلبات حسب center_id
    //     $requests = Requests::where('center_id', $centerId) // تأكد من أن لديك عمود center_id في جدول Requests
    //         ->with(['globalRequest', 'patientTransferRequest', 'requestModifyAppointment'])
    //         ->get();
    
    //     return $this->mapRequests($requests);
    // }


     public function mapRequests($requests)
     {
         $user = auth('user')->user(); 
     
         return $requests->map(function ($request) use ($user) { 
             $processedRequest = [
                 'id' => $request->id,
                 'requestStatus' => $request->requestStatus,
               //  'valid' => $request->valid,
             ];
     
             if ($request->globalRequest) {
                 $processedRequest['type'] = $request->globalRequest->content;
                // $processedRequest['content'] = $request->globalRequest->content;
                 $processedRequest['senderName'] = $request->globalRequest->requester->fullName;
                 if ($request->globalRequest->requestable) {
                    $requestable = $request->globalRequest->requestable;
                    $requestableType = class_basename($requestable->getMorphClass());

                    switch ($requestableType) {
                        case 'Chair':
                            $processedRequest['content'] = " تم إضافة كرسي رقم " . $requestable->chairNumber;
                            break;
                        case 'Shift':
                            $processedRequest['content'] = " تم إضافة وردية " . $requestable->name ;
                            break;
                        case 'MedicalRecord':
                            $processedRequest['content'] = " تم إضافة سجل طبي للمريض " . $requestable->user->fullName;
                            break;

                        case 'User':
                            $processedRequest['content'] = " تم إضافةالمريض " . $requestable->fullName;
                            break;

                        case 'DisbursedMaterialsUser':
                            $processedRequest['content'] = " تم صرف مادة للمريض " . $requestable->user->fullName;
                            break;

                            case 'GeneralPatientInformation':
                                $processedRequest['content'] = " تم اضافة معلومات الحالة الاحتماعية للمريض" . $requestable->user->fullName;
                                break;
                    }


                  



                } else {
                    $processedRequest['requestableType'] = 'غير متوفر';
                }
             }
             
             
             
             
             elseif ($request->patientTransferRequest) {
                 $patientName = $request->patientTransferRequest->user->fullName;
                 $centerPatientName = $request->patientTransferRequest->centerPatient->centerName;
                 $destinationCenterName = $request->patientTransferRequest->destinationCenter->centerName;
                 $processedRequest['type'] = 'طلب نقل مريض';
                 $processedRequest['senderName'] = $user->fullName;
                 $processedRequest['content'] = "نريد نقل المريض " . $patientName . " من مركز " . $centerPatientName . " الى مركز " . $destinationCenterName . " بسبب " . $request->cause;
             } elseif ($request->requestModifyAppointment) {

                $patientName = $request->requestModifyAppointment->user->fullName;
                 $processedRequest['type'] = 'طلب تعديل موعد';
                 $processedRequest['senderName'] = $user->fullName;
                  $oldTime= $request->requestModifyAppointment->newTime;
                $newTime = $request->requestModifyAppointment->appointment->appointmentTimeStamp;
                 $processedRequest['content'] = "نريد تعديل موعد المريض " . $patientName . " من  " . $oldTime . " الى " .  $newTime . " بسبب " . $request->cause;
             }
     
             return $processedRequest;
         });
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
     






public function getUserByVerificationCode(string $verificationCode)
{
   
    $user = User::where('verificationCode', $verificationCode)->first();
    
    if (!$user) {
        throw new LogicException('User not found for this verification code');
    }

    return [
        'fullName' => $user->fullName,
        'nationalNumber' => $user->nationalNumber,
        'verificationCode' => $user->verificationCode,
       
    ];
}
public function verifyAccount(string $verificationCode, string $password)
{
    $user = User::where('verificationCode', $verificationCode)->first();
    
    if (!$user) {
        throw new LogicException('Verification code is invalid.');
    }

    if ($user->valid != -1) {
        throw new LogicException('الحساب لم ');
    }

    $hashedPassword = Hash::make($password);
    $user->update([
        'accountStatus' => 'verified',
        'password' => $hashedPassword,
        'verificationCode' => null,
    ]);

    return $this->loginUser($user->nationalNumber, $password);
}




// public function loginUser(string $nationalNumber, string $password)
// {
//     if (Auth::attempt(['nationalNumber' => $nationalNumber, 'password' => $password])) {
//         $user = Auth::user();
//         $token = $user->createToken('auth_token')->plainTextToken;

//         $user->token = $token;
//         return $user;
//     }

//     return null;

// }

public function loginUser(string $nationalNumber, string $password)
{
    if (Auth::attempt(['nationalNumber' => $nationalNumber, 'password' => $password])) {
        $user = Auth::user();
        if ($user->valid === 0){

            return 'لم يتم تأكيد حسابك بعد ';
        }
        if ($user->valid === -2){

            return 'الحساب غير موجود';
        }
        $userCenter = $user->userCenters()->where('valid', -1)->first();
        $centerId = $userCenter ? $userCenter->centerID : null;
        $centerName = $userCenter ? $userCenter->medicalCenter->centerName: null;
        $token = $user->createToken('auth_token', ['center_id' => $centerId])->plainTextToken;

        $user->token = $token;
        $user->centerID =  $centerId;
        $user->centerName =  $centerName;
        return $user;
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





public function addPatientInfo(array $data)
{
    $validator = Validator::make($data, [
        'maritalStatus' => 'required|string|max:255',
        'nationality' => 'required|string|max:255',
        'status' => 'required|string|max:255',
        'reasonOfStatus' => 'nullable|string|max:255',
        'educationalLevel' => 'required|string|max:255',
        'generalIncome' => 'required|numeric',
        'incomeType' => 'required|string|max:255',
        'sourceOfIncome' => 'required|string|max:255',
        'workDetails' => 'required|string|max:255',
        'residenceType' => 'required|string|max:255',
        'fullName' => 'required|string|max:255',
        'degreeOfKinship' => 'required|string|max:255',

        'address' => 'required|array',
        'address.*.line' => 'required|string|max:255',
        'address.*.use' => 'required|string|max:255',
        'address.*.cityName' => 'required|string|max:255',
        'address.*.countryName' => 'required|string|max:255',


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

    foreach ($data['telecomDataArray'] as $telecomData) {
        $telecomValidator = Validator::make($telecomData, [
            'system' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'use' => 'required|string|max:255',
        ]);

        if ($telecomValidator->fails()) {
            throw new InvalidArgumentException($telecomValidator->errors()->first());
        }
    }

    if ($validator->fails()) {
        throw new InvalidArgumentException('Validation failed.');
    }

    DB::transaction(function () use ($data) {
        $generalPatientInfo = GeneralPatientInformation::create($data);
        $maritalStatusData = [
            'childrenNumber' => $data['childrenNumber'],
            'healthStateChildren' => $data['healthStateChildren'],
            'generalPatientInformationID' => $generalPatientInfo->id
        ];
        MaritalStatus::create($maritalStatusData);


        $companionData = [
            'fullName' => $data['fullName'],
            'degreeOfKinship' => $data['degreeOfKinship'],
            'userID' => $data['patientID'],

        ];

        $patientCompanion = PatientCompanion::create($companionData);

        foreach ($data['telecomDataArray'] as $telecomData) {
            $telecomData['patientCompanionID'] = $patientCompanion->id;
            Telecom::create($telecomData);
        }

        foreach ($data['address'] as $addressData) {
            $this->createCompanionAddress($patientCompanion, $addressData);
        }

      
        $globalRequestData = [
            'operation' => 'اضافة المعلومات العامة لمريض',
            'requestable_id' => $generalPatientInfo->id,
            'requestable_type' => GeneralPatientInformation::class,
            'requestStatus' => 'pending',
            'cause' => '.'
        ];
        $this->addGlobalRequest($globalRequestData);
    
    
    
    
        
    });



    
}


public function createCompanionAddress(PatientCompanion $user, array $addressData)
{

    $city = City::firstOrCreate(['cityName' => $addressData['cityName']]);
    $country = Country::firstOrCreate(['countryName' => $addressData['countryName']]);

    $city->country()->associate($country);

    $city->save();

    $address = new Address([
        'line' => $addressData['line'],
        'use' => $addressData['use'],
        'cityID' => $city->id,
        'addressID' => $user->id, 
    ]);

    $user->address()->save($address);
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



public function updateMedicalCenter($centerId, array $centerData)
{
    
    DB::beginTransaction();
    try {
        $medicalCenter = MedicalCenter::findOrFail($centerId);

        $validator = Validator::make($centerData, [
            'centerName' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'charityName' => 'nullable|string|max:255',
            'telecom' => 'sometimes|array',
            'telecom.*.system' => 'sometimes|string|max:255',
            'telecom.*.value' => 'sometimes|string|max:255',
            'telecom.*.use' => 'sometimes|string|max:255',
            'address' => 'sometimes|array',
            'address.use' => 'sometimes|string|max:255',
            'address.line' => 'sometimes|string',
            'address.cityName' => 'sometimes|string|max:255',
            'address.countryName' => 'sometimes|string|max:255',
        
        ]);

        if ($validator->fails()) {
            throw new LogicException($validator->errors()->first());
        }

        $medicalCenter->update($centerData);
    

      
        if (isset($centerData['telecom'])) {
            foreach ($centerData['telecom'] as $telecomData) {
                $telecom = Telecom::where('centerID', $medicalCenter->id)
                                  ->where('id', $telecomData['id'])
                                  ->firstOrFail();
                $telecom->update($telecomData);
            }
        }

        if (isset($centerData['address'])) {
            foreach ($centerData['address'] as $addressData) {
                $address = Address::findOrFail($addressData['id']);
                if ($address->centerID === $medicalCenter->id) {
                    $address->update($addressData);
                $address->city->cityName = $addressData['cityName'];
                $address->city->country->countryName = $addressData['countryName'];
                $address->city->save();
                $address->city->country->save();


                } else {
                    throw new LogicException('Address ID does not belong to the given user.');
                }
            }
        }

        DB::commit();
        return $medicalCenter;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error updating medical center: ' . $e->getMessage());
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


     $globalRequestData = [
        'operation' => 'اضافة كرسي',
        'requestable_id' => $user->id,
        'requestable_type' => Chair::class,
        'requestStatus' => 'pending',
        'cause' => '.'
    ];
    $this->addGlobalRequest($globalRequestData);





     return $chair;
 }
 

 public function updateChair($chairId, array $data)
 {
     $validatedData = Validator::make($data, [
         'chairNumber' => 'required|integer|max:255',
         'roomName' => 'required|string|max:255',
     ])->validate();
 
     $chair = Chair::findOrFail($chairId);
     $chair->update([
         'chairNumber' => $validatedData['chairNumber'],
         'roomName' => $validatedData['roomName'],
     ]);
 
     return $chair;
 }
 

//  public function addShift(array $data)
//  {
    
//     $validatedData = Validator::make($data, [
//         'shiftStart' => 'required|date',
//         'shiftEnd' => 'required|date|after:shiftStart',
//         'name' => 'required|string|max:255',
        
//     ])->validate();
 
//      $user = auth('user')->user();
//      $centerId = UserCenter::where('userID', $user->id)->first()->centerID;
//      $shift = new Shift([
//          'shiftStart' => $validatedData['shiftStart'],
//          'shiftEnd' => $validatedData['shiftEnd'],
//          'name' => $validatedData['name'],
//          'centerID' => $centerId
//      ]);
//      $shift->save();
//      return $shift;
//  }



public function addShift(array $data)
{
    $validatedData = Validator::make($data, [
       
        'centerID' => 'required|integer|exists:medical_centers,id',
        'shiftStart' => 'required|date_format:H:i', 
        'shiftEnd' => 'required|date_format:H:i|after:shiftStart', 
        'name' => 'required|string|max:255',
    ])->validate();

  //  $user = auth('user')->user();
    //$centerId = UserCenter::where('userID', $user->id)->first()->centerID;

    $shiftStart = Carbon::createFromFormat('H:i', $validatedData['shiftStart'])->toTimeString();
    $shiftEnd = Carbon::createFromFormat('H:i', $validatedData['shiftEnd'])->toTimeString();

    $shift = new Shift([
        'shiftStart' => $shiftStart,
        'shiftEnd' => $shiftEnd,
        'name' => $validatedData['name'],
        'centerID' =>  $validatedData['centerID'],
    ]);


    $shift->save();



    $globalRequestData = [
        'operation' => 'اضافة وردية',
        'requestable_id' => $shift->id,
        'requestable_type' => Shift::class,
        'requestStatus' => 'pending',
        'cause' => '.'
    ];
    $this->addGlobalRequest($globalRequestData);




    return $shift;
}

public function updateShift($shiftId, array $data)
{
    $validatedData = Validator::make($data, [
        'centerID' => 'required|integer|exists:medical_centers,id',
        'shiftStart' => 'required|date_format:H:i:s',
        'shiftEnd' => 'required|date_format:H:i:s|after:shiftStart',
        'name' => 'required|string|max:255',
    ])->validate();

    $shift = Shift::findOrFail($shiftId);

    $shiftStart = Carbon::createFromFormat('H:i:s', $validatedData['shiftStart'])->toTimeString();
    $shiftEnd = Carbon::createFromFormat('H:i:s', $validatedData['shiftEnd'])->toTimeString();

    $shift->update([
        'shiftStart' => $shiftStart,
        'shiftEnd' => $shiftEnd,
        'name' => $validatedData['name'],
        'centerID' => $validatedData['centerID'],
    ]);

    return $shift;
}




public function updateShifts(array $shiftsData)
{
    $updatedShifts = collect();

    foreach ($shiftsData as $shiftData) {
        $validatedData = Validator::make($shiftData, [
            'id' => 'required|integer|exists:shifts,id',
            'centerID' => 'required|integer|exists:medical_centers,id',
            'shiftStart' => 'required|date_format:H:i:s',
            'shiftEnd' => 'required|date_format:H:i:s|after:shiftStart',
            'name' => 'required|string|max:255',
        ])->validate();

        $shift = Shift::findOrFail($validatedData['id']);

        $shiftStart = Carbon::createFromFormat('H:i:s', $validatedData['shiftStart'])->toTimeString();
        $shiftEnd = Carbon::createFromFormat('H:i:s', $validatedData['shiftEnd'])->toTimeString();

        $shift->update([
            'shiftStart' => $shiftStart,
            'shiftEnd' => $shiftEnd,
            'name' => $validatedData['name'],
            'centerID' => $validatedData['centerID'],
        ]);

        $updatedShifts->push($shift);
    }

    return $updatedShifts;
}



    public function createCenterTelecoms($centerId, array $telecomsData)
    {
        $center = MedicalCenter::find($centerId);
        if (!$center) {
            throw new ModelNotFoundException('Medical Center not found');
        }

        $createdTelecoms = [];

        foreach ($telecomsData as $data) {
            $validator = Validator::make($data, [
                'system' => 'required|string|max:255',
                'value' => 'required|string|max:255|unique:telecoms,value',
                'use' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $telecom = new Telecom($validator->validated());
            $center->centertelecoms()->save($telecom);

            $createdTelecoms[] = $telecom;
        }

        return $createdTelecoms;
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




// public function getShiftsByCenter($centerId)
// {
//     $shifts = Shift::where('centerID', $centerId)->get();

//     if ($shifts->isEmpty()) {
//         return 'لا توجد ورديات متاحة لهذا المركز';
//     }

//     return $shifts ;
// }

public function getShiftsByCenter($centerId)
{
    $shifts = Shift::where('centerID', $centerId)->where('valid', -1)->get();

    if ($shifts->isEmpty()) {
        return 'لا توجد ورديات متاحة لهذا المركز';
    }

    return $shifts;
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
        'id' => $user->id,
        'fullName' => $user->fullName,
        'accountStatus' => $user->accountStatus,
        'gender' => $user->gender,
        'nationalNumber' => $user->nationalNumber,
        'dateOfBirth' => $user->dateOfBirth,
        'role' => $user->role,
      //  'userCenter' => $user->userCenters,

'userCenter' => $user->userCenters->filter(function($userCenter) {
    return $userCenter->valid == -1;
})->first()->medicalCenter->centerName ?? null,



        'telecom' => $user->telecom->map(function ($telecom) {
            return [
                'id' => $telecom->id,
                'system' => $telecom->system,
                'value' => $telecom->value,
                'use' => $telecom->use
            ];
        })->toArray(),
        'address' => []
    ];



        foreach ($user->userAddressWithCityAndCountry as $address) {
     
           
                $userDetails['address'][] = [
                    'id' => $address->id,
                    'line' => $address->line,
                    'use' => $address->use,
                    'cityName' => $address->city->cityName,
                    'countryName' => $address->city->country->countryName
                ];
            
        }

    if ($user->role === 'patient') {
        $generalPatientInformation = $user->generalPatientInformation;
        $patientCompanions=$user->patientCompanions;
        if ($generalPatientInformation) {
            $userDetails['generalInformation'] = 
                $generalPatientInformation;
        }


        if ($patientCompanions) {
            $userDetails['patientCompanion'] = $patientCompanions->map(function ($companion) {
                return [
                    'id' => $companion->id,
                    'fullName' => $companion->fullName,
                    'degreeOfKinship' => $companion->degreeOfKinship,
                    'address' => $companion->address->map(function ($address) {
                        return [
                            'id' => $address->id,
                            'line' => $address->line,
                            'use' => $address->use,
                            'cityName' => $address->city->cityName,
                            'countryName' => $address->city->country->countryName
                        ];
                    })->toArray(),

                    'telecom' => $companion->telecoms->map(function ($telecom) {
                        return [
                            'id' => $telecom->id,
                            'system' => $telecom->system,
                            'value' => $telecom->value,
                            'use' => $telecom->use
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }
    }


    
    

    return $userDetails;
}





// 
// public function getMedicalCenterDetails($centerId)
// {

//     $medicalCenter = MedicalCenter::with(['centertelecoms', 'centerAddressWithCityAndCountry'])
//     ->findOrFail($centerId);

//     return $medicalCenter;

// }





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


public function getMedicalCenterDetails($centerId)
{
    $medicalCenter = MedicalCenter::with([
        'centertelecoms',
        'centerAddressWithCityAndCountry',
       
    ])->findOrFail($centerId);

    $totalNurses = $medicalCenter->users->where('role', 'nurse')->count();
    $totalDoctors = $medicalCenter->users->where('role', 'doctor')->count();

    $totalShifts = $medicalCenter->shifts->where('valid', -1);

    $totalChairs = $medicalCenter->chairs->where('valid', -1)->count();

    $managerName = $medicalCenter->users->where('role', 'admin')->pluck('fullName')->implode(', ');


    $contactDetails = $medicalCenter->centertelecoms;

    $details = [
        'id' => $medicalCenter->id,
       'centerName' => $medicalCenter->centerName,
       'description' => $medicalCenter->description,
       'charityName' => $medicalCenter->charityName,
       'adminName' => $managerName,
       'totalChairs' => $totalChairs,
       'totalNurses' => $totalNurses,
       'totalDoctors' => $totalDoctors,
       'shifts' => array_values($totalShifts->toArray()),
        
      
       'telecom' => $contactDetails,

       'address' => []
    ];


    foreach ($medicalCenter->address as $address) {
     
           
        $details['address'][] = [
            'id' => $address->id, 
            'line' => $address->line,
            'use' => $address->use,
            'cityName' => $address->city->cityName,
            'countryName' => $address->city->country->countryName
        ];
        $details['shifts'] = array_values($details['shifts']);
}

return  $details;



}



// public function getNotesByMedicalCenter($centerId)
// {
//     $notes = Note::where('centerID', $centerId)->get();
//     return $notes;
// }





// public function getNotesByMedicalCenter($centerId)
// {
//     $notes = Note::where('centerID', $centerId)->get();
//     return $notes->map(function ($note) {
//         return [
//             'senderID' => $note->senderID,
//             'receiverID' => $note->receiverID ? $note->receiverID : null, 
//             'noteContent' => $note->noteContent,
//             'category' => $note->category,
//             'type' => $note->type,
//             'date' => $note->date,
//             'sessionID' => $note->sessionID,
//             'senderName' => $note->sender->fullName, 
         

//             'receiverName' => $note->receiver ? $note->receiver->fullName : null, 
//         ];
//     });
// }



public function getNotesByreceiverID($receiverID)
{
    $notes = Note::where('receiverID', $receiverID)->get();
    return $notes->map(function ($note) {
        return [
            'senderID' => $note->senderID,
            'receiverID' => $note->receiverID ? $note->receiverID : null, 
            'noteContent' => $note->noteContent,
            'category' => $note->category,
            'type' => $note->type,
            'date' => $note->date,
            'sessionID' => $note->sessionID,
            'senderName' => $note->sender->fullName, 
            'receiverName' => $note->receiver ? $note->receiver->fullName : null, 
        ];
    });
}




public function getlogs($centerId)
{
    $logs = Logging::where('centerID', $centerId)->get();
    $formattedLogs = $logs->map(function ($log) {
        return [
            'operation' => $log->operation,
            'destinationOfOperation' => $log->destinationOfOperation ?? null, 
            'oldData' => $log->oldData,
            'newData' => $log->newData,
            'sessionID' => $log->sessionID,
            'affectedUser' => $log->affectedUser->fullName, 
            'affectorUser' => $log->affectorUser->fullName,
            'affectedUserID' => $log->affectedUser->id, 
            'affectorUserID' => $log->affectorUser->id , 
            'date' => $log->created_at->format('Y-m-d')
        ];
    });

    return $formattedLogs ;
}






public function getMedicineNames()
{
    return Medicine::pluck('name');
}















////////////////////////// accept ///////////////////////

// public function acceptAddUser($userId)
// {
//     User::where('id', $userId)->update(['accountStatus' =>'active']);

//     UserCenter::where('userID', $userId)->update(['valid' => -1]);

//     return 'تم قبول اضافة المستخدم';
// }




public function changeAccountStatus(User $user, string $newStatus)
{
    $user->update(['accountStatus' => $newStatus]);
}



public function acceptUpdateCenter($centerId)
{
    MedicalCenter::where('id', $centerId)->update(['valid' => -1]);

    return 'تم تحديث بيانات المركز';
}




// public function acceptaddShift($shiftId)
// {
//     Shift::where('id', $shiftId)->update(['valid' => -1]);


//     return 'تم اضافة الوردية';
// }


public function acceptaddShift($shiftId, $status)
{
    if ($status === 'approved') {
        Shift::where('id', $shiftId)->update(['valid' => -1]);
        return 'تم قبول الوردية ';
    } elseif ($status === 'rejected') {
        Shift::where('id', $shiftId)->update(['valid' => -2]);
        return 'تم رفض الوردية ';
    }

    return 'الحالة الممررة غير معروفة.';
}





// public function acceptAddChair($chairID)
// {
//     Chair::where('id', $chairID)->update(['valid' => -1]);

//     return 'تم اضافة الكرسي';
// }

public function acceptAddChair($chairID, $status)
{
    if ($status === 'approved') {
        Chair::where('id', $chairID)->update(['valid' => -1]);
        return 'تم قبول إضافة الكرسي ';
    } elseif ($status === 'rejected') {
        Chair::where('id', $chairID)->update(['valid' => -2]);
        return 'تم رفض إضافة الكرسي ';
    }

    return 'الحالة الممررة غير معروفة.';
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

    if ($newStatus === 'approved') {
        $requestModel->valid = -1;
    } elseif ($newStatus === 'rejected') {
        $requestModel->valid = -2;
    }
    $requestModel->save();


    if ($requestModel->globalRequest) { 

if ($requestModel->globalRequest->requestable) {
    $requestable = $requestModel->globalRequest->requestable;
    $requestableType = class_basename($requestable->getMorphClass());
   $id= $requestable->id;

   
//    return $this->acceptAddDisbursedMaterialsUser($id, $newStatus);
//    return $this->acceptPatientInformation($id, $newStatus);
//    return $this->acceptaddShift($id, $newStatus);
//    return $this->acceptAddMedicalRecord($id, $newStatus);
//    return $this->acceptPatientInformation($id, $newStatus);
//    acceptPatientInformation

    switch ($requestableType) {
        case 'Chair':
        return $this->acceptAddChair($id, $newStatus);
            break;
        case 'Shift':
            return $this->acceptaddShift($id, $newStatus);
            break;
        case 'MedicalRecord':
            return $this->acceptAddMedicalRecord($id, $newStatus);
            break;

        case 'User':
            $processedRequest['content'] = " تم إضافةالمريض " . $requestable->fullName;
            break;

        case 'DisbursedMaterialsUser':
            return $this->acceptAddDisbursedMaterialsUser($id, $newStatus);
            break;

            case 'GeneralPatientInformation':
                return $this->acceptPatientInformation($id, $newStatus);
                break;
    }

}





    }
    
elseif ($requestModel->patientTransferRequest && $newStatus === 'approved') {
    $userCenter = UserCenter::where('userID', $requestModel->patientTransferRequest->patientID)
                             ->where('centerID', $requestModel->patientTransferRequest->centerPatientID)
                             ->first();
    if ($userCenter) {
        $userCenter->valid = 0;
        $userCenter->save();
    }

    $newUserCenter = new UserCenter([
        'userID' => $requestModel->patientTransferRequest->patientID,
        'centerID' => $requestModel->patientTransferRequest->destinationCenterID,
        'valid' => -1 
    ]);
    $newUserCenter->save();
}



    elseif ($requestModel->requestModifyAppointment  && $newStatus === 'approved') {

     
      $appointment= Appointment::findOrFail($requestModel->requestModifyAppointment->appointment->id);
   
        $appointment->updateappointmentTime($requestModel->requestModifyAppointment->newTime);
     
    }


}





// public function acceptAddMedicalRecord($medicalRecordID)
// {
//     MedicalRecord::where('id', $medicalRecordID)->update(['valid' => -1]);

//     return 'تم اضافة السجل الطبي';
// }


public function acceptAddMedicalRecord($medicalRecordID, $status)
{
    if ($status === 'approved') {
        MedicalRecord::where('id', $medicalRecordID)->update(['valid' => -1]);
        return 'تم قبول إضافة السجل الطبي ';
    } elseif ($status === 'rejected') {
        MedicalRecord::where('id', $medicalRecordID)->update(['valid' => -2]);
        return 'تم رفض إضافة السجل الطبي ';
    }

    return 'الحالة الممررة غير معروفة.';
}



// public function acceptAddDisbursedMaterialsUser($disbursedMaterialdID)
// {
//     DisbursedMaterialsUser::where('id', $disbursedMaterialdID)->update(['valid' => -1]);

//     return 'تم صرف المادة للمريض ';
// }




// public function acceptPatientInformation($userId)
// {
//     GeneralPatientInformation::where('patientID', $userId)->update(['valid' => -1]);

//     PatientCompanion::where('userID', $userId)->update(['valid' => -1]);

//     return 'تم قبول المعلومات العامة للمريض';
// }

public function acceptAddDisbursedMaterialsUser($disbursedMaterialdID, $status)
{
    if ($status === 'approved') {
        DisbursedMaterialsUser::where('id', $disbursedMaterialdID)->update(['valid' => -1]);
        return 'تم قبول صرف المادة للمريض ';
    } elseif ($status === 'rejected') {
        DisbursedMaterialsUser::where('id', $disbursedMaterialdID)->update(['valid' => -2]);
        return 'تم رفض صرف المادة للمريض ';
    }

    return 'الحالة الممررة غير معروفة.';
}

public function acceptPatientInformation($userId, $status)
{
    if ($status === 'approved') {
        GeneralPatientInformation::where('patientID', $userId)->update(['valid' => -1]);
        PatientCompanion::where('userID', $userId)->update(['valid' => -1]);
        return 'تم قبول المعلومات العامة للمريض ';
    } elseif ($status === 'rejected') {
        GeneralPatientInformation::where('patientID', $userId)->update(['valid' => -2]);
        PatientCompanion::where('userID', $userId)->update(['valid' => -2]);
        return 'تم رفض المعلومات العامة للمريض ';
    }

    return 'الحالة الممررة غير معروفة.';
}



//طلبات اضافة وردية



public function getAddShiftsRequests($centerId)
{
    $shifts = Shift::where('centerID', $centerId)->where('valid', 0)->get();

    if ($shifts->isEmpty()) {
        return 'لا توجد ورديات متاحة لهذا المركز';
    }

    return $shifts;
}



// // طلبات استهلاك المواد
// public function getDisbursedMaterialsRequests($centerID) {
//     $disbursedMaterials = DisbursedMaterialsUser::with(['disbursedMaterial', 'user'])
//                                 ->where('centerID', $centerID)->where('valid', 0)
                               
//                                 ->get();

//     return $disbursedMaterials;
// }


    

public function getMedicalRecordRequests($centerId)
{
    $users = User::whereHas('userCenters', function ($query) use ($centerId) {
        $query->where('centerID', $centerId);
    })->where('role', 'patient')->get();

    
    $medicalRecords = [];
    foreach ($users as $user) {
        $medicalRecord = MedicalRecord::with(['allergicConditions', 'pathologicalHistories', 'pharmacologicalHistories', 'surgicalHistories'])
                                      ->where('userID', $user->id)
                                      ->where('valid', -1)
                                      ->first();
        if ($medicalRecord) {
            $formattedRecord = [
                'vascularEntrance' => $medicalRecord->vascularEntrance,
                'dryWeight' => $medicalRecord->dryWeight,
                'bloodType' => $medicalRecord->bloodType,
                'causeRenalFailure' => $medicalRecord->causeRenalFailure,
                'dialysisStartDate' => $medicalRecord->dialysisStartDate,
                'kidneyTransplant' => $medicalRecord->kidneyTransplant ,
                'pharmacologicalPrecedents' => $medicalRecord->pharmacologicalHistories->map(function ($history) {
                    return [
                        'medicineName' => $history->medicineName,
                        'dateStart' => $history->dateStart,
                        'dateEnd' => $history->dateEnd,
                        'generalDetails' => $history->generalDetails
                    ];
                }),
                'pathologicalPrecedents' => $medicalRecord->pathologicalHistories->map(function ($history) {
                    return [
                        'illnessName' => $history->illnessName,
                        'medicalDiagnosisDate' => $history->medicalDiagnosisDate,
                        'generalDetails' => $history->generalDetails
                    ];
                }),
                'surgicalPrecedents' => $medicalRecord->surgicalHistories->map(function ($history) {
                    return [
                        'surgeryName' => $history->surgeryName,
                        'surgeryDate' => $history->surgeryDate,
                        'generalDetails' => $history->generalDetails
                    ];
                })
            ];
        
            $medicalRecords[] = $formattedRecord;
        }
    }

    return $medicalRecords;
}


public function getChairsInCenter($centerId)
{
    $chairs = Chair::where('centerID', $centerId)->get();
    return $chairs;
}






// public function getAllPatientInfoRequests($centerId)
// {
//     $patients = User::whereHas('userCenters', function ($query) use ($centerId) {
//         $query->where('centerID', $centerId);
//     })->where('role', 'patient')->get();

//     $allPatientInfo = [];
//     foreach ($patients as $patient) {
//         $patientInfo = GeneralPatientInformation::with([
//             'maritalStatus',
           
//         ])->where('patientID', $patient->id)->first();

//         if ($patientInfo) {
//             $formattedPatientInfo = [
//                 'maritalStatus' => $patientInfo->maritalStatus,
//                 'nationality' => $patientInfo->nationality,
//                 'status' => $patientInfo->status,
//                 'reasonOfStatus' => $patientInfo->reasonOfStatus,
//                 'educationalLevel' => $patientInfo->educationalLevel,
//                 'generalIncome' => $patientInfo->generalIncome,
//                 'incomeType' => $patientInfo->incomeType,
//                 'sourceOfIncome' => $patientInfo->sourceOfIncome,
//                 'workDetails' => $patientInfo->workDetails,
//                 'residenceType' => $patientInfo->residenceType,
//                 'childrenNumber' => $patientInfo->maritalStatus->childrenNumber,
//                 'healthStateChildren' => $patientInfo->maritalStatus->healthStateChildren,
//                 'valid' => $patientInfo->valid,

//                 'companion' => [
//                     'fullName' => $patientInfo->patientCompanion->fullName,
//                     'degreeOfKinship' => $patientInfo->patientCompanion->degreeOfKinship,
//                     'telecoms' => $patientInfo->patientCompanion->telecoms,
//                     'addresses' => $patientInfo->patientCompanion->addresses,
//                     'valid' => $patientInfo->patientCompanion->valid
//                 ]
//             ];
//             $allPatientInfo[] = $formattedPatientInfo;
//         }
//     }

//     return $allPatientInfo;
// }





public function getAllPatientInfoRequests($centerId)
{
    $patients = User::with(['generalPatientInformation.maritalStatus', 'patientCompanions.telecoms', 'patientCompanions.addresses'])
        ->whereHas('userCenters', function ($query) use ($centerId) {
            $query->where('centerID', $centerId);
        })
        ->where('role', 'patient')
        ->get();

    $allPatientInfo = [];
    foreach ($patients as $patient) {
        $patientInfo = $patient->generalPatientInformation;
        $patientCompanions = $patient->patientCompanions;

        $formattedPatientInfo = [
            'patientID' => $patient->id,
            'maritalStatus' => $patientInfo ? $patientInfo->maritalStatus : null,
            'nationality' => $patientInfo ? $patientInfo->nationality : null,
            'status' => $patientInfo ? $patientInfo->status : null,
            'reasonOfStatus' => $patientInfo ? $patientInfo->reasonOfStatus : null,
            'educationalLevel' => $patientInfo ? $patientInfo->educationalLevel : null,
            'generalIncome' => $patientInfo ? $patientInfo->generalIncome : null,
            'incomeType' => $patientInfo ? $patientInfo->incomeType : null,
            'sourceOfIncome' => $patientInfo ? $patientInfo->sourceOfIncome : null,
            'workDetails' => $patientInfo ? $patientInfo->workDetails : null,
            'residenceType' => $patientInfo ? $patientInfo->residenceType : null,
            'childrenNumber' => $patientInfo && $patientInfo->maritalStatus ? $patientInfo->maritalStatus->childrenNumber : null,
            'healthStateChildren' => $patientInfo && $patientInfo->maritalStatus ? $patientInfo->maritalStatus->healthStateChildren : null,
            'valid' => $patientInfo ? $patientInfo->valid : null,
            'companion' => $patientCompanions->map(function ($companion) {
                return [
                    'fullName' => $companion->fullName,
                    'degreeOfKinship' => $companion->degreeOfKinship,
                    'telecoms' => $companion->telecoms,
                    'addresses' => $companion->addresses,
                    'valid' => $companion->valid
                ];
            })->toArray()
        ];
        $allPatientInfo[] = $formattedPatientInfo;
    }

    // إرجاع جميع المعلومات المنسقة
    return response()->json($allPatientInfo);
}



















////////////////////////  update //////////////////////////////////


public function updateUser($id, array $userData): User
{
    DB::beginTransaction();
    try {
        $user = User::findOrFail($id);

        $validator = Validator::make($userData, [
            'fullName' => 'sometimes|string|max:255',
       'nationalNumber' => 'sometimes|string|max:11|unique:users,nationalNumber,' . $user->id,

            'dateOfBirth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'role' => 'sometimes|string|max:255',
            'telecom' => 'sometimes|array',
            'telecom.*.system' => 'sometimes|string|max:255',
            'telecom.*.value' => 'sometimes|string|max:255',
            'telecom.*.use' => 'sometimes|string|max:255',
            'address' => 'sometimes|array',
            'address.*.line' => 'sometimes|string|max:255',
            'address.*.use' => 'sometimes|string|max:255',
            'address.*.cityName' => 'sometimes|string|max:255',
            'address.*.countryName' => 'sometimes|string|max:255',
            'centerName' => 'sometimes|string|max:255',
            'permissionNames' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            throw new LogicException($validator->errors()->first());
        }

        $user->update($userData);
        if (isset($userData['telecom'])) {
            foreach ($userData['telecom'] as $telecomData) {
                $telecom = Telecom::findOrFail($telecomData['id']);
                if ($telecom->userID === $user->id) {
                    $telecom->update($telecomData);
                } else {
                    throw new LogicException('Telecom ID does not belong to the given user.');
                }
            }
        }

        if (isset($userData['address'])) {
            foreach ($userData['address'] as $addressData) {
                $address = Address::findOrFail($addressData['id']);
                if ($address->userID === $user->id) {
                    $address->update($addressData);
                $address->city->cityName = $addressData['cityName'];
                $address->city->country->countryName = $addressData['countryName'];
                $address->city->save();
                $address->city->country->save();


                } else {
                    throw new LogicException('Address ID does not belong to the given user.');
                }
            }
        }

                if (isset($userData['role']) && $userData['role'] === 'secretary' && isset($userData['permissionNames'])) {
            $this->updatePermissionsToUser($user->id, $userData['permissionNames']);
        }



        // if (isset($userData['role']) && $userData['role'] === 'secretary' && isset($userData['permissionNames'])) {
        //     $this->updatePermissionsToUser($user->id, $userData['permissionNames']);
        // }

        DB::commit();

        return $user;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error updating user: ' . $e->getMessage());
    }
}



// public function updateUserTelecoms(User $user, array $telecomData)
// {
//     foreach ($telecomData as $data) {
//         $telecom = Telecom::findOrFail($data['id']);
//         if ($telecom->userID === $user->id) {
//             $telecom->update($data);
//         } else {
//             throw new LogicException('Telecom ID does not belong to the given user.');
//         }
//     }
// }

// public function updateUserAddress(User $user, array $addressData)
// {
//     foreach ($addressData as $data) {
//         $address = Address::findOrFail($data['id']);
//         if ($address->userID === $user->id) {
//             $address->update($data);
//         } else {
//             throw new LogicException('Address ID does not belong to the given user.');
//         }
//     }
// }

public function updatePermissionsToUser($userId, array $permissionNames)
{
    DB::transaction(function () use ($userId, $permissionNames) {
        $user = User::findOrFail($userId);
        $permissionsIds = Permission::whereIn('permissionName', $permissionNames)->pluck('id');
        $user->permissions()->sync($permissionsIds);
    });
}




// public function getPatientInfo($patientID)
// {
//     $patientInfo = GeneralPatientInformation::where('patientID', $patientID)
//                     ->with(['patientCompanion', 'telecoms' => function ($query) {
//                         $query->where('system', 'phone');
//                     }, 'addresses'])
//                     ->get(['fullName', 'address', 'telecoms.value as phone', 'patientCompanion.fullName as companionName', 'patientCompanion.telecoms.value as companionPhone'])
//                     ->toArray();

//     $formattedPatientInfo = [];
//     foreach ($patientInfo as $info) {
//         $address = '';
//         foreach ($info['addresses'] as $addr) {
//             $address .= $addr['line'] . ', ' . $addr['cityName'] . ', ' . $addr['countryName'] . ' ';
//         }

//         $formattedPatientInfo[] = [
//             'fullName' => $info['fullName'],
//             'address' => $address,
//             'phone' => $info['phone'],
//             'companionName' => $info['patientCompanion']['companionName'],
//             'companionPhone' => $info['patientCompanion']['telecoms']['companionPhone']
//         ];
//     }

//     return $formattedPatientInfo;
// }




///////////////////////////////////////////////////////////////////




public function getPatientsByCenter($centerID)
{
    $patients = User::whereHas('userCenters', function ($query) use ($centerID) {
                    $query->where('centerID', $centerID);
                })
                ->where('role', 'patient')
                ->with(['patientCompanions.telecoms' => function ($query) {
                    $query->where('system', 'phone');
                }, 'telecom' => function ($query) {
                    $query->where('system', 'phone');
                }, 'address.city.country'])
                ->get();

    $formattedPatients = [];
    foreach ($patients as $patient) {
        $address = '';
        foreach ($patient->address as $addr) {
            $cityName = $addr->city->cityName ?? 'City not found';
            $countryName = $addr->city->country->countryName ?? 'Country not found';
            $address .= $addr['line'] . ', ' . $cityName . ', ' . $countryName . ' ';
        }

        $companionName = '';
        $companionPhone = '';
        if ($patient->patientCompanions->isNotEmpty()) {
            $companion = $patient->patientCompanions->first();
            $companionName = $companion->fullName;
            $companionPhone = $companion->telecoms->where('system', 'phone')->first()->value ?? null;
        }

        $phone = $patient->telecom->where('system', 'phone')->first()->value ?? null;

        $formattedPatients[] = [
            'fullName' => $patient->fullName,
            'address' => $address,
            'phone' => $phone,
            'companionName' => $companionName,
            'companionPhone' => $companionPhone
        ];
    }

    return $formattedPatients;
}


public function updatePatientStatus($patientID, $newStatus)
{
    $patientInfo = User::where('id', $patientID)->first();
    
    if (!$patientInfo) {
       
        return  'لم يتم العثور على المريض';
    }

    $patientInfo->generalPatientInformation->status = $newStatus;
    $patientInfo->generalPatientInformation->save();
    return 'تم تحديث حالة المريض';
}






public function updatePatientInfo($patientId, array $data)
{
    $validator = Validator::make($data, [
        'maritalStatus' => 'sometimes|string|max:255',
        'nationality' => 'sometimes|string|max:255',
        'status' => 'sometimes|string|max:255',
        'reasonOfStatus' => 'nullable|string|max:255',
        'educationalLevel' => 'sometimes|string|max:255',
        'generalIncome' => 'sometimes|numeric',
        'incomeType' => 'sometimes|string|max:255',
        'sourceOfIncome' => 'sometimes|string|max:255',
        'workDetails' => 'sometimes|string|max:255',
        'residenceType' => 'sometimes|string|max:255',
        'fullName' => 'sometimes|string|max:255',
        'degreeOfKinship' => 'sometimes|string|max:255',
    
    ]);

    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }

    DB::transaction(function () use ($patientId, $data) {
        $generalPatientInfo = GeneralPatientInformation::where('patientID', $patientId)->firstOrFail();
        $generalPatientInfo->update($data);

        if (isset($data['maritalStatusData'])) {
            $maritalStatus = MaritalStatus::where('generalPatientInformationID', $generalPatientInfo->id)->firstOrFail();
            $maritalStatus->update($data['maritalStatusData']);
        }

        if (isset($data['companionData'])) {
            $patientCompanion = PatientCompanion::where('userID', $patientId)->firstOrFail();
            $patientCompanion->update($data['companionData']);
        }

        if (isset($data['telecomDataArray'])) {
            foreach ($data['telecomDataArray'] as $telecomData) {
                $telecom = Telecom::where('patientCompanionID', $patientCompanion->id)
                                  ->where('id', $telecomData['id'])
                                  ->firstOrFail();
                $telecom->update($telecomData);
            }
        }

        if (isset($data['address'])) {
            foreach ($data['address'] as $addressData) {
                $address = Address::where('patientCompanionID', $patientCompanion->id)
                                  ->where('id', $addressData['id'])
                                  ->firstOrFail();
                $address->update($addressData);
            
                $address->city->cityName = $addressData['cityName'];
                $address->city->country->countryName = $addressData['countryName'];
                $address->city->save();
                $address->city->country->save();
            }
        }
   
    });

    return 'تم تعديل معلومات المستخدم';
}


}


