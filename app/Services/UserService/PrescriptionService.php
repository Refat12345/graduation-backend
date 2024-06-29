<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\PrescriptionServiceInterface;
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

class PrescriptionService implements PrescriptionServiceInterface
{


    public function addPrescription(array $data)
    {
        $validator = Validator::make($data, [
            'patientID' => 'required|exists:users,id',
            'medicines' => 'required|array',
            'medicines.*.name' => 'required|string', 
         //   'medicines.*.titer' => 'required|string|max:255',
            'medicines.*.dateOfStart' => 'required|date',
            'medicines.*.dateOfEnd' => 'required|date|after_or_equal:medicines.*.dateOfStart',
            'medicines.*.amount' => 'nullable|numeric|min:0',
            'medicines.*.details' => 'required|string|max:255',
        ]);
    
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
        
        $validatedData = $validator->validated();
        $doctor = auth('user')->user();
    
        \DB::beginTransaction();
        try {
            $prescription = Prescription::create([
                'patientID' => $validatedData['patientID'],
                'doctorID' => $doctor->id,
            ]);
    
            foreach ($validatedData['medicines'] as $medicineData) {
              //  $medicine = Medicine::where('name', $medicineData['name'])->firstOrFail(); 
              $medicine = Medicine::firstOrNew(['name' => $medicineData['name']]);
    
              if (!$medicine->exists) {
                  // إذا لم يكن الدواء موجودًا، قم بإضافته
                  $medicine->fill($medicineData);
                  $medicine->save();
              }
                $prescription->medicines()->attach($medicine->id, [
                    'dateOfStart' => $medicineData['dateOfStart'],
                    'dateOfEnd' => $medicineData['dateOfEnd'],
                    'amount' => $medicineData['amount'],
                    'details' => $medicineData['details'],
                    'status' => 'nonActive',
                ]);
            }
    
            \DB::commit();
            return $prescription;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

// public function getPrescriptionsByPatient(User $patient): Collection
// { 

//     $prescriptions = $patient->prescriptions()->with(['medicines.prescriptionMedicine', 'doctor'])->get()->map(function ($prescription) {
    
//         return [
//             'doctor' => $prescription->doctor->fullName,
//             'medicines' => $prescription->medicines->map(function ($medicine) {
//                 $prescriptionMedicine = $medicine->prescriptionMedicine; 
//                 return [
//                     'status' => $prescriptionMedicine->status, 
//                     'name' => $medicine->name,
//                     'dateOfStart' =>  $dateOfStart = $prescriptionMedicine->dateOfStart instanceof Carbon ? $prescriptionMedicine->dateOfStart : Carbon::parse($prescriptionMedicine->dateOfStart)->format('Y-m-d'),
//                     'dateOfEnd' =>  $dateOfEnd = $prescriptionMedicine->dateOfEnd instanceof Carbon ? $prescriptionMedicine->dateOfEnd : Carbon::parse($prescriptionMedicine->dateOfEnd)->format('Y-m-d'),
                    
//                     'details' => $prescriptionMedicine->details
//                 ];
//             })
//         ];
//     });

//     return  $prescriptions;
// }


public function getAllPrescriptionsForUser($userId) {
    $prescriptions = Prescription::where('patientID', $userId)->with(['medicines', 'doctor'])->get();

    return $prescriptions->map(function ($prescription) {
        return [
            'prescriptionID' => $prescription->id ,
            'doctor' => $prescription->doctor->fullName ,
            'medicines' => $prescription->medicines->map(function ($medicine) {
                return [
                    'Id' => $medicine->id,
                    'status' => $medicine->pivot->status,
                    'name' => $medicine->name,
                    'titer'=> $medicine->titer,
                    'dateOfStart' => Carbon::parse($medicine->pivot->dateOfStart)->format('Y-m-d'),
                    'dateOfEnd' => Carbon::parse($medicine->pivot->dateOfEnd)->format('Y-m-d'),
                    'details' => $medicine->pivot->details
                ];
            })
        ];
    });
}









// public function updatePrescription($prescriptionId, array $data)
// {
//     $validator = Validator::make($data, [
//         'patientID' => 'required|exists:users,id',
//         'medicines' => 'required|array',
//         'medicines.*.id' => 'required|integer',
//         'medicines.*.name' => 'required|name',
//         'medicines.*.dateOfStart' => 'required|date',
//         'medicines.*.dateOfEnd' => 'required|date|after_or_equal:medicines.*.dateOfStart',
//         'medicines.*.amount' => 'nullable|numeric|min:0',
//         'medicines.*.details' => 'required|string|max:255',
//     ]);

//     if ($validator->fails()) {
//         throw new InvalidArgumentException($validator->errors()->first());
//     }

//     $validatedData = $validator->validated();

//     \DB::beginTransaction();
//     try {
//         $prescription = Prescription::findOrFail($prescriptionId);
//         $prescription->update([
//             'patientID' => $validatedData['patientID'],
//         ]);

//         $prescription->medicines()->detach(); 
//         foreach ($validatedData['medicines'] as $medicineData) {
//             $medicine = Medicine::where('id', $medicineData['id'])->firstOrFail();
//             $prescription->medicines()->attach($medicine->name, [
              
//                 //'name' => $medicineData['name'],
//                 'dateOfStart' => $medicineData['dateOfStart'],
//                 'dateOfEnd' => $medicineData['dateOfEnd'],
//                 'amount' => $medicineData['amount'],
//                 'details' => $medicineData['details'],
//                 'status' => 'nonActive', 
//             ]);
//         }

//         \DB::commit();
//         return $prescription;
//     } catch (\Exception $e) {
//         \DB::rollback();
//         throw $e;
//     }
// }


public function updatePrescription($prescriptionId, array $data)
{
    $validator = Validator::make($data, [
        'patientID' => 'required|exists:users,id',
        'medicines' => 'required|array',
        'medicines.*.id' => 'required|integer',
        'medicines.*.name' => 'required|string',
        'medicines.*.dateOfStart' => 'required|date',
        'medicines.*.dateOfEnd' => 'required|date|after_or_equal:medicines.*.dateOfStart',
        'medicines.*.amount' => 'nullable|numeric|min:0',
        'medicines.*.details' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }

    $validatedData = $validator->validated();

    \DB::beginTransaction();
    try {
        $prescription = Prescription::findOrFail($prescriptionId);
        $prescription->update([
            'patientID' => $validatedData['patientID'],
        ]);

        foreach ($validatedData['medicines'] as $medicineData) {
         
            $prescription->medicines()->detach($medicineData['id']);

            $newMedicine = Medicine::create([
                'name' => $medicineData['name'],
            ]);

            $prescription->medicines()->attach($newMedicine->id, [
                'dateOfStart' => $medicineData['dateOfStart'],
                'dateOfEnd' => $medicineData['dateOfEnd'],
                'amount' => $medicineData['amount'],
                'details' => $medicineData['details'],
                'status' => 'nonActive',
            ]);
        }

        \DB::commit();
        return $prescription;
    } catch (\Exception $e) {
        \DB::rollback();
        throw $e;
    }
}


// public function updatePrescription($prescriptionId, array $data)
// {
//     $validator = Validator::make($data, [
//         'patientID' => 'required|exists:users,id',
//         'medicines' => 'required|array',
//         'medicines.*.id' => 'required|integer|exists:medicines,id',
//         'medicines.*.name' => 'required|string',
//         'medicines.*.dateOfStart' => 'required|date',
//         'medicines.*.dateOfEnd' => 'required|date|after_or_equal:medicines.*.dateOfStart',
//         'medicines.*.amount' => 'nullable|numeric|min:0',
//         'medicines.*.details' => 'required|string|max:255',
//     ]);

//     if ($validator->fails()) {
//         throw new InvalidArgumentException($validator->errors()->first());
//     }

//     $validatedData = $validator->validated();

//     \DB::beginTransaction();
//     try {
//         $prescription = Prescription::findOrFail($prescriptionId);
//         $prescription->update([
//             'patientID' => $validatedData['patientID'],
//         ]);

//         foreach ($validatedData['medicines'] as $medicineData) {
//             $medicine = Medicine::findOrFail($medicineData['id']);
//             $existingPrescriptions = $medicine->prescriptions()->where('prescriptionID', '!=', $prescriptionId)->count();

//             if ($existingPrescriptions > 0) {
//                 // إذا كان الدواء مرتبطًا بوصفات أخرى، قم بإنشاء دواء جديد
//                 $newMedicine = Medicine::create([
//                     'name' => $medicineData['name'],
//                 ]);
//                 $medicineId = $newMedicine->id;
//             } else {
//                 // إذا كان الدواء مرتبطًا فقط بالوصفة الحالية، قم بتحديث اسمه
//                 $medicine->update([
//                     'name' => $medicineData['name'],
//                 ]);
//                 $medicineId = $medicine->id;
//             }

//             $prescription->medicines()->updateOrCreate(
//                 ['medicineID' => $medicineId], // تغيير إلى 'medicineID'
//                 [
//                     'dateOfStart' => $medicineData['dateOfStart'],
//                     'dateOfEnd' => $medicineData['dateOfEnd'],
//                     'amount' => $medicineData['amount'],
//                     'details' => $medicineData['details'],
//                     'status' => 'nonActive',
//                 ]
//             );
//         }

//         \DB::commit();
//         return $prescription;
//     } catch (\Exception $e) {
//         \DB::rollback();
//         throw $e;
//     }
// }



}