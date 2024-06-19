<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\MedicalRecordServiceInterface;
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
class MedicalRecordService  implements MedicalRecordServiceInterface
{


    public function createMedicalRecord(array $MedicalRecordData)
    {
      
      
        $validator = Validator::make($MedicalRecordData, [
            'dialysisStartDate' => 'required|date',
            'dryWeight' => 'required|numeric',
            'bloodType' => 'required|string|max:255',
            'vascularEntrance' => 'required|string|max:255',
            'kidneyTransplant' => 'required|boolean',
            'causeRenalFailure' => 'required|string|max:255',
            'userID' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'patient');
                }),
                Rule::unique('medical_records', 'userID')
            ],
        ]);
        $validator->after(function ($validator) use ($MedicalRecordData) {
            if (MedicalRecord::where('userID', $MedicalRecordData['userID'])->exists()) {
                $validator->errors()->add('userID', 'يوجد سجل طبي لهذا المريض');
            }
        });
    
        if ($validator->fails()) {
            throw new LogicException($validator->errors()->first());
        }
     
        DB::beginTransaction();
        try {
            $MedicalRecord = MedicalRecord::create($MedicalRecordData);
    
            $allAllergicConditionsData = request()->input('allergicConditions');
        if (!empty($allAllergicConditionsData)) {
            foreach ($allAllergicConditionsData as $AllergicConditionData) {
                $AllergicConditionData['medicalRecordID'] = $MedicalRecord->id;
                $this->createAllergicCondition($AllergicConditionData);
            }
            
        }
     
            $allPathologicalHistoriesData = request()->input('pathologicalHistories');
            if (!empty($allPathologicalHistoriesData)) {
            foreach ($allPathologicalHistoriesData as $PathologicalHistoryData) {
                $PathologicalHistoryData['medicalRecordID'] = $MedicalRecord->id;
                $this->createPathologicalHistory($PathologicalHistoryData);
            }}
       
            $allPharmacologicalHistoriesData = request()->input('pharmacologicalHistories');
            if (!empty($allPharmacologicalHistoriesData)) {
            foreach ($allPharmacologicalHistoriesData as $PharmacologicalHistoryData) {
                $PharmacologicalHistoryData['medicalRecordID'] = $MedicalRecord->id;
                $this->createPharmacologicalHistory($PharmacologicalHistoryData);
            }}
    
            $allSurgicalHistoriesData = request()->input('surgicalHistories');
            if (!empty($allSurgicalHistoriesData)) {
            foreach ($allSurgicalHistoriesData as $SurgicalHistoryData) {
                $SurgicalHistoryData['medicalRecordID'] = $MedicalRecord->id;
                $this->createSurgicalHistory($SurgicalHistoryData);
            }
        }
            DB::commit();
            return $MedicalRecord;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new LogicException('Error creating MedicalRecord: ' . $e->getMessage());
        }

      
    }
    
    
    
    
    public function createPathologicalHistory(array $PathologicalHistoryData )
    {
       $validator = Validator::make($PathologicalHistoryData, [
        'illnessName' => 'required|string|max:255',
        'medicalDiagnosisDate' => 'required|date',
        'generalDetails' => 'required|string|max:255',
        'medicalRecordID' => [
            'required',
            'integer',
            Rule::exists('medical_records', 'id'),
        ],   ]);
    
    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }
    
    DB::beginTransaction();
    try {
         $PathologicalHistory = PathologicalHistory::create($PathologicalHistoryData);
        DB::commit();
        return $PathologicalHistory;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error creating PathologicalHistory: ' . $e->getMessage());
    }
    }
    
    
    
    

    
    public function createPharmacologicalHistory(array $PharmacologicalHistoryData )
    {
       $validator = Validator::make($PharmacologicalHistoryData, [
        'medicineName' => 'required|string|max:255',
        'dateStart' => 'required|date',
        'dateEnd' => 'required|date',
        'generalDetails'=>'required|String|max:255',
        'medicalRecordID' => [
            'required',
            'integer',
            Rule::exists('medical_records', 'id'),
        ],   ]);
    
    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }
    
    DB::beginTransaction();
    try {
         $PharmacologicalHistory = PharmacologicalHistory::create($PharmacologicalHistoryData);
        DB::commit();                 
        return $PharmacologicalHistory;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error creating PharmacologicalHistory: ' . $e->getMessage());
    }
    }
    
    
    
       


    public function createSurgicalHistory(array $SurgicalHistoryData )
    {
    
    
       $validator = Validator::make($SurgicalHistoryData, [
        'surgeryName' => 'required|string|max:255',
        'surgeryDate' => 'required|date',
        'generalDetails' => 'required|string|max:255',
        'medicalRecordID' => [
            'required',
            'integer',
            Rule::exists('medical_records', 'id'),
        ],   ]);
    
    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }
    
    DB::beginTransaction();
    try {
         $SurgicalHistory = SurgicalHistory::create($SurgicalHistoryData);
        DB::commit();
        return $SurgicalHistory;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error creating SurgicalHistory: ' . $e->getMessage());
    }
    }
    
    
    
    public function createAllergicCondition(array $AllergicConditionData )
    {
       $validator = Validator::make($AllergicConditionData, [
        'allergy' => 'required|string|max:255',
        'dateOfSymptomOnset' => 'required|date',
        'generalDetails' => 'required|string|max:255',
        'medicalRecordID' => [
            'required',
            'integer',
            Rule::exists('medical_records', 'id'),
        ],   ]);
    
    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }
    
    DB::beginTransaction();
    try {
         $AllergicCondition = AllergicCondition::create($AllergicConditionData);
        DB::commit();
        return $AllergicCondition;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error creating AllergicCondition: ' . $e->getMessage());
    }
    }
    
    

    
    




    public function getMedicalRecordWithDetails($userID)
    {
        $medicalRecord = MedicalRecord::with(['allergicConditions', 'pathologicalHistories', 'pharmacologicalHistories', 'surgicalHistories'])
                                      ->where('userID', $userID)->first();
    
        if (!$medicalRecord) {
            return 'لا يوجد سجل طبي لهذاالمريض';
        }
    
        $formattedRecord = [
            'id' => $medicalRecord->id,
            'vascularEntrance' => $medicalRecord->vascularEntrance,
            'dryWeight' => $medicalRecord->dryWeight,
            'bloodType' => $medicalRecord->bloodType,
            'causeRenalFailure' => $medicalRecord->causeRenalFailure,
            'dialysisStartDate' => $medicalRecord->dialysisStartDate,
            'kidneyTransplant' => $medicalRecord->kidneyTransplant ,
            'pharmacologicalPrecedents' => $medicalRecord->pharmacologicalHistories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'medicineName' => $history->medicineName,
                    'dateStart' => $history->dateStart,
                    'dateEnd' => $history->dateEnd,
                    'generalDetails' => $history->generalDetails
                ];
            }),
            'pathologicalPrecedents' => $medicalRecord->pathologicalHistories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'illnessName' => $history->illnessName,
                    'medicalDiagnosisDate' => $history->medicalDiagnosisDate,
                    'generalDetails' => $history->generalDetails
                ];
            }),
            'surgicalPrecedents' => $medicalRecord->surgicalHistories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'surgeryName' => $history->surgeryName,
                    'surgeryDate' => $history->surgeryDate,
                    'generalDetails' => $history->generalDetails
                ];
            })
        ];
    
        return $formattedRecord;
    }





 ////////////////////////////  update  ///////////////////////////////////





 public function updateMedicalRecord($id, array $MedicalRecordData)
 {
     $MedicalRecord = MedicalRecord::findOrFail($id);
 
     $validator = Validator::make($MedicalRecordData, [
         'dialysisStartDate' => 'sometimes|date',
         'dryWeight' => 'sometimes|numeric',
         'bloodType' => 'sometimes|string|max:255',
         'vascularEntrance' => 'sometimes|string|max:255',
         'kidneyTransplant' => 'sometimes|boolean',
         'causeRenalFailure' => 'sometimes|string|max:255',
       
         // 'userID' => 'sometimes|integer|exists:users,id|unique:medical_records,userID,' . $MedicalRecord->id,
     ]);
 
     if ($validator->fails()) {
         throw new LogicException($validator->errors()->first());
     }
 
     DB::beginTransaction();
     try {
         $MedicalRecord->update($MedicalRecordData);
 
         $allAllergicConditionsData = request()->input('allergicConditions');
         if (!empty($allAllergicConditionsData)) {
             foreach ($allAllergicConditionsData as $AllergicConditionData) {
                 $AllergicConditionData['medicalRecordID'] = $MedicalRecord->id;
                 $this->updateAllergicCondition($AllergicConditionData);
             }
         }
 
         $allPathologicalHistoriesData = request()->input('pathologicalHistories');
         if (!empty($allPathologicalHistoriesData)) {
             foreach ($allPathologicalHistoriesData as $PathologicalHistoryData) {
                 $PathologicalHistoryData['medicalRecordID'] = $MedicalRecord->id;
                 $this->updatePathologicalHistory($PathologicalHistoryData);
             }
         }
 
         $allPharmacologicalHistoriesData = request()->input('pharmacologicalHistories');
         if (!empty($allPharmacologicalHistoriesData)) {
             foreach ($allPharmacologicalHistoriesData as $PharmacologicalHistoryData) {
                 $PharmacologicalHistoryData['medicalRecordID'] = $MedicalRecord->id;
                 $this->updatePharmacologicalHistory($PharmacologicalHistoryData);
             }
         }
 
         $allSurgicalHistoriesData = request()->input('surgicalHistories');
         if (!empty($allSurgicalHistoriesData)) {
             foreach ($allSurgicalHistoriesData as $SurgicalHistoryData) {
                 $SurgicalHistoryData['medicalRecordID'] = $MedicalRecord->id;
                 $this->updateSurgicalHistory($SurgicalHistoryData);
             }
         }
 
         DB::commit();
         return $MedicalRecord;
     } catch (\Exception $e) {
         DB::rollBack();
         throw new LogicException('Error updating MedicalRecord: ' . $e->getMessage());
     }
 }
 

 public function updateAllergicCondition(array $AllergicConditionData)
{
    $validator = Validator::make($AllergicConditionData, [
        'conditionName' => 'required|string|max:255',
        'medicalRecordID' => 'required|integer|exists:medical_records,id',
    ]);

    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }

    $AllergicCondition = AllergicCondition::findOrFail($AllergicConditionData['id']);
    $AllergicCondition->update($AllergicConditionData);
    return $AllergicCondition;
}



public function updatePathologicalHistory(array $PathologicalHistoryData)
{
    $validator = Validator::make($PathologicalHistoryData, [
        'illnessName' => 'required|string|max:255',
        'medicalDiagnosisDate' => 'required|date',
        'generalDetails' => 'required|string|max:255',
        'medicalRecordID' => 'required|integer|exists:medical_records,id',
    ]);

    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }

    $PathologicalHistory = PathologicalHistory::findOrFail($PathologicalHistoryData['id']);
    $PathologicalHistory->update($PathologicalHistoryData);
    return $PathologicalHistory;
}






public function updatePharmacologicalHistory(array $PharmacologicalHistoryData)
{
    $validator = Validator::make($PharmacologicalHistoryData, [
        'medicineName' => 'required|string|max:255',
        'dateStart' => 'required|date',
        'dateEnd' => 'required|date',
        'generalDetails' => 'required|string|max:255',
        'medicalRecordID' => 'required|integer|exists:medical_records,id',
    ]);

    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }

    $PharmacologicalHistory = PharmacologicalHistory::findOrFail($PharmacologicalHistoryData['id']);
    $PharmacologicalHistory->update($PharmacologicalHistoryData);
    return $PharmacologicalHistory;
}






public function updateSurgicalHistory(array $SurgicalHistoryData)
{
    $validator = Validator::make($SurgicalHistoryData, [
        'surgeryName' => 'required|string|max:255',
        'surgeryDate' => 'required|date',
        'generalDetails' => 'required|string|max:255',
        'medicalRecordID' => 'required|integer|exists:medical_records,id',
    ]);

    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }

    $SurgicalHistory = SurgicalHistory::findOrFail($SurgicalHistoryData['id']);
    $SurgicalHistory->update($SurgicalHistoryData);
    return $SurgicalHistory;
}













}