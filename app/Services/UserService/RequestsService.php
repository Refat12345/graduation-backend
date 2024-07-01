<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\RequestsServiceInterface;
use App\Models\Request;
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




class RequestsService implements RequestsServiceInterface{



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
                $processedRequest['senderid'] = $request->globalRequest->requester->id;
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
                           $processedRequest['content'] = "تم اضافة المستخدم ". $requestable->fullName;
                           break;

                       case 'DisbursedMaterialsUser':
                           $processedRequest['content'] = " تم صرف مادة للمريض " . $requestable->user->fullName;
                           break;

                           case 'GeneralPatientInformation':
                               $processedRequest['content'] = " تم اضافة معلومات الحالة الاجتماعية للمريض " . $requestable->user->fullName;
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
                $processedRequest['senderid'] = $user->id;
                $processedRequest['content'] = "نريد نقل المريض " . $patientName . " من مركز " . $centerPatientName . " الى مركز " . $destinationCenterName . " بسبب " . $request->cause;
            } elseif ($request->requestModifyAppointment) {

               $patientName = $request->requestModifyAppointment->user->fullName;
                $processedRequest['type'] = 'طلب تعديل موعد';
                $processedRequest['senderName'] = $user->fullName;
                $processedRequest['senderid'] = $user->id;
                 $oldTime= $request->requestModifyAppointment->newTime;
               $newTime = $request->requestModifyAppointment->appointment->appointmentTimeStamp;
                $processedRequest['content'] = "نريد تعديل موعد المريض " . $patientName . " من  " . $oldTime . " الى " .  $newTime . " بسبب " . $request->cause;
            }
    
            return $processedRequest;
        });
    }




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
            return $this->acceptAddUser($id, $newStatus);
         
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




public function acceptUpdateCenter($centerId)
{
    MedicalCenter::where('id', $centerId)->update(['valid' => -1]);

    return 'تم تحديث بيانات المركز';
}


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


public function acceptAddUser($userID, $status)
{
    if ($status === 'approved') {
        User::where('id', $userID)->update(['valid' => -1]);
        return 'تم قبول إضافة الكرسي ';
    } elseif ($status === 'rejected') {
        User::where('id', $userID)->update(['valid' => -2]);
        return 'تم رفض إضافة الكرسي ';
    }

    return 'الحالة الممررة غير معروفة.';
}









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



public function acceptPatientInformation($generalId, $status)
{
    if ($status === 'approved') {
        GeneralPatientInformation::where('id', $generalId)->update(['valid' => -1]);
      //  PatientCompanion::where('id', $generalId)->update(['valid' => -1]);
        return 'تم قبول المعلومات العامة للمريض ';
    } elseif ($status === 'rejected') {
        GeneralPatientInformation::where('patientID', $generalId)->update(['valid' => -2]);
        PatientCompanion::where('userID', $generalId)->update(['valid' => -2]);
        return 'تم رفض المعلومات العامة للمريض ';
    }

    return 'الحالة الممررة غير معروفة.';
}




public function getAddShiftsRequests($centerId)
{
    $shifts = Shift::where('centerID', $centerId)->where('valid', 0)->get();

    if ($shifts->isEmpty()) {
        return 'لا توجد ورديات متاحة لهذا المركز';
    }

    return $shifts;
}



public function getAllPatientInfoRequests($centerId)
{
    $patients = User::with(['generalPatientInformation.maritalStatus', 'patientCompanions.telecoms', 'patientCompanions.address'])
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
}