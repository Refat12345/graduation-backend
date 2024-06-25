<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\MedicalSessionServiceInterface;
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
class MedicalSessionService implements MedicalSessionServiceInterface
{


    public function createDialysisSession(array $data)
    {
     
        $validator = Validator::make($data, [
            'sessionStartTime' => 'required|date',
            'sessionEndTime' => 'required|date|after:sessionStartTime',
            'weightBeforeSession' => 'required|numeric|min:0',
            'weightAfterSession' => 'required|numeric|min:0',
            'totalWithdrawalRate' => 'required|numeric|min:0',
            'withdrawalRateHourly' => 'required|numeric|min:0',
            'pumpSpeed' => 'required|numeric|min:0',
            'filterColor' => 'required|string|max:255',
            'filterType' => 'required|string|max:255',
            'vascularConnection' => 'required|string|max:255',
            'naConcentration' => 'required|numeric|min:0',
            'venousPressure' => 'required|integer|min:0',
            'status' => 'required|string|max:255',
            'sessionDate' => 'required|date',
            'patientID' => 'nullable|exists:users,id',
            'doctorID' => 'nullable|exists:users,id',
            // 'centerID' => 'required|exists:medical_centers,id',
        ]);
    
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    
    
        $validatedData = $validator->validated();
        $nurse = auth('user')->user();
        $validatedData['nurseID'] = $nurse->id;
        $centerId = $nurse->medicalCenters()->first()->id;
        $validatedData['centerID'] = $centerId;
    
        DB::beginTransaction();
        try {
            $dialysisSession = DialysisSession::create($validatedData);
    
            if (isset($data['medicines'])) {
                foreach ($data['medicines'] as $medicineData) {
                    $this->addSessionMedicine($medicineData, $dialysisSession->id);
                }
            }
    
            if (isset($data['bloodPressures'])) {
                foreach ($data['bloodPressures'] as $bloodPressureData) {
                    $this->addBloodPressureMeasurement($bloodPressureData, $dialysisSession->id);
                }
            }
    
            if (isset($data['appointmentID'])) {
                $appointment = Appointment::find($data['appointmentID']);
                if ($appointment) {
                    $appointment->sessionID = $dialysisSession->id;
                    $appointment->valid = 'finished';
                    $appointment->save();
                }}
    
            DB::commit();
            return $dialysisSession;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    // public function getDialysisSessionsWithChairInfo($centerId, $month, $year)
    // {
    //     $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
    //     $query = Appointment::with(['session', 'session.patient', 'session.nurse', 'chair'])
    //         ->whereHas('session', function ($sessionQuery) use ($centerId, $dateString) {
    //             if ($centerId > 0) {
    //                 $sessionQuery->where('centerID', $centerId);
    //             }
    //             $sessionQuery->whereRaw('DATE_FORMAT(sessionEndTime, "%Y-%m") = ?', [$dateString]);
    //         });
    
    //     $dialysisSessions = $query->get()
    //         ->map(function ($appointment) {
    //             return [
    //                 'id' => $appointment->session->id,
    //                 'patientName' => $appointment->session->patient->fullName,
    //                 'nurseName' => $appointment->session->nurse->fullName,
    //                 'sessionStartTime' => $appointment->session->sessionStartTime,
    //                 'sessionEndTime' => $appointment->session->sessionEndTime,
    //                 'chair' => $appointment->chair->chairNumber,
    //                 'roomName' => $appointment->chair->roomName,
    //                 'valid' => $appointment->valid
    //             ];
    //         });
    
    //     return $dialysisSessions;
    // }
    public function getDialysisSessionsWithChairInfo($centerId, $month = null, $year = null)
{
    $query = Appointment::with(['session', 'session.patient', 'session.nurse', 'chair'])
        ->whereHas('session', function ($sessionQuery) use ($centerId, $month, $year) {
            if ($centerId > 0) {
                $sessionQuery->where('centerID', $centerId);
            }
            if ($month && $year) {
                $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $sessionQuery->whereRaw('DATE_FORMAT(sessionEndTime, "%Y-%m") = ?', [$dateString]);
            }
        });

    $dialysisSessions = $query->get()
        ->map(function ($appointment) {
            return [
                'id' => $appointment->session->id,
                'patientName' => $appointment->session->patient->fullName,
                'nurseName' => $appointment->session->nurse->fullName,
                'sessionStartTime' => $appointment->session->sessionStartTime,
                'sessionEndTime' => $appointment->session->sessionEndTime,
                'chair' => $appointment->chair->chairNumber,
                'roomName' => $appointment->chair->roomName,
                'valid' => $appointment->valid
            ];
        });

    return $dialysisSessions;
}

    // public function getPatientDialysisSessions($patientId, $month, $year)
    // {
    //     $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
    //     $query = Appointment::with(['session', 'session.patient', 'session.nurse', 'chair'])
    //         ->whereHas('session', function ($sessionQuery) use ($patientId, $dateString) {
            
    //                 $sessionQuery->where('userID', $patientId);
             
    //             $sessionQuery->whereRaw('DATE_FORMAT(sessionEndTime, "%Y-%m") = ?', [$dateString]);
    //         });
    
    //     $dialysisSessions = $query->get()
    //         ->map(function ($appointment) {
    //             return [
    //                 'id' => $appointment->session->id,
    //                 'patientName' => $appointment->session->patient->fullName,
    //                 'nurseName' => $appointment->session->nurse->fullName,
    //                 'sessionStartTime' => $appointment->session->sessionStartTime,
    //                 'sessionEndTime' => $appointment->session->sessionEndTime,
    //                 'chair' => $appointment->chair->chairNumber,
    //                 'roomName' => $appointment->chair->roomName,
    //                 'valid' => $appointment->valid
    //             ];
    //         });
    
    //     return $dialysisSessions;
    // }
    
    public function getPatientDialysisSessions($patientId, $month = null, $year = null)
    {
        $query = Appointment::with(['session', 'session.patient', 'session.nurse', 'chair'])
            ->whereHas('session', function ($sessionQuery) use ($patientId, $month, $year) {
                $sessionQuery->where('userID', $patientId);
    
                if ($month && $year) {
                    $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                    $sessionQuery->whereRaw('DATE_FORMAT(sessionEndTime, "%Y-%m") = ?', [$dateString]);
                }
            });
    
        $dialysisSessions = $query->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->session->id,
                    'patientName' => $appointment->session->patient->fullName,
                    'nurseName' => $appointment->session->nurse->fullName,
                    'sessionStartTime' => $appointment->session->sessionStartTime,
                    'sessionEndTime' => $appointment->session->sessionEndTime,
                    'chair' => $appointment->chair->chairNumber,
                    'roomName' => $appointment->chair->roomName,
                    'valid' => $appointment->valid
                ];
            });
    
        return $dialysisSessions;
    }
    





// public function getCompleteDialysisSessionDetails($sessionId)
// {
//     $dialysisSession = DialysisSession::with(['medicineTakens', 'bloodPressureMeasurements', 'appointment'])
//                                       ->find($sessionId);

//     if (!$dialysisSession) {
//         throw new ModelNotFoundException('Dialysis session not found.');
//     }

//     $completeDetails = [

//         'nurse' => $dialysisSession->nurse->fullName,
//         'center' => $dialysisSession->medicalCenter->centerName,
//         'doctor' => $dialysisSession->doctor->fullName,
       
//         'sessionStartTime' => $dialysisSession->sessionStartTime  ,
//         'sessionEndTime' => $dialysisSession->sessionEndTime   ,
//         'weightBeforeSession' => $dialysisSession->weightBeforeSession   ,
       
//         'weightAfterSession' => $dialysisSession->weightAfterSession   ,
//         'totalWithdrawalRate' => $dialysisSession->totalWithdrawalRate   ,
//         'withdrawalRateHourly' => $dialysisSession->withdrawalRateHourly   ,
//         'pumpSpeed' => $dialysisSession->pumpSpeed   ,
//         'filterColor' => $dialysisSession->filterColor   ,
//         'filterType' => $dialysisSession->filterType   ,

//         'vascularConnection' => $dialysisSession->vascularConnection   ,
//         'naConcentration' => $dialysisSession->naConcentration   ,
//         'venousPressure' => $dialysisSession->venousPressure   ,
//         'status' => $dialysisSession->status   ,

//         'medicines' => $dialysisSession->medicineTakens->toArray(),
//         'bloodPressures' => $dialysisSession->bloodPressureMeasurements->toArray(),
//         'sessionNotes' => $dialysisSession->notes->toArray(),
//         'chair' => $dialysisSession->appointment->chair->toArray(),
      
//     ];

//     return $completeDetails;
// }

public function getCompleteDialysisSessionDetails($sessionId)
{
    $dialysisSession = DialysisSession::with(['medicineTakens.medicine', 'bloodPressureMeasurements', 'appointment'])
                                      ->find($sessionId);

    if (!$dialysisSession) {
        throw new ModelNotFoundException('Dialysis session not found.');
    }


    

    $completeDetails = [
        'nurse' => $dialysisSession->nurse->fullName,
        'center' => $dialysisSession->medicalCenter->centerName,
        'doctor' => $dialysisSession->doctor->fullName,
        'sessionStartTime' => $dialysisSession->sessionStartTime,
        'sessionEndTime' => $dialysisSession->sessionEndTime,
        'weightBeforeSession' => $dialysisSession->weightBeforeSession,
        'weightAfterSession' => $dialysisSession->weightAfterSession,
        'totalWithdrawalRate' => $dialysisSession->totalWithdrawalRate,
        'withdrawalRateHourly' => $dialysisSession->withdrawalRateHourly,
        'pumpSpeed' => $dialysisSession->pumpSpeed,
        'filterColor' => $dialysisSession->filterColor,
        'filterType' => $dialysisSession->filterType,
        'vascularConnection' => $dialysisSession->vascularConnection,
        'naConcentration' => $dialysisSession->naConcentration,
        'venousPressure' => $dialysisSession->venousPressure,
        'status' => $dialysisSession->status,
        'medicines' => $dialysisSession->medicineTakens->map(function ($medicineTaken) {
            return [
                'medicineTakenID' => $medicineTaken->id,
                'medicineID' => $medicineTaken->medicine->id,
                'medicineName' => $medicineTaken->medicine->name,
                'value' => $medicineTaken->value,
            ];
        })->toArray(),
        'bloodPressures' => $dialysisSession->bloodPressureMeasurements->toArray(),
        'sessionNotes' => $dialysisSession->notes->toArray(),
        'chair' => $dialysisSession->appointment->chair->toArray(),
    ];

    return $completeDetails;
}

    
    

    
    public function addSessionMedicine(array $data, $sessionId)
    {
        $validator = Validator::make($data, [
            'medicineID' => 'required|exists:medicines,id',
            'value' => 'required|numeric|min:0'
        ]);
    
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    
        $validatedData = $validator->validated();
        $validatedData['sessionID'] = $sessionId;
    
        return MedicineTaken::create($validatedData);
    }
    
    
    
    
    
    public function addBloodPressureMeasurement(array $data, $sessionId)
    {
        $validator = Validator::make($data, [
            'pressureValue' => 'required|numeric|min:0',
            'pulseValue' => 'required|numeric|min:0',
            'time' => 'required|date_format:Y-m-d H:i:s'
    
        ]);
    
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    
        $validatedData = $validator->validated();
        $validatedData['sessionID'] = $sessionId;
    
        return BloodPressureMeasurement::create($validatedData);
    }


    


public function getDialysisSessions($centerId)
{
    $query = DialysisSession::with(['patient', 'nurse', 'chair', 'room']);

    if ($centerId > 0) {
        $query->where('centerID', $centerId);
    }

    $dialysisSessions = $query->latest('sessionStartTime') 
                               ->get()
                               ->map(function ($session) {
                                   return [
                                       'id' => $session->id,
                                       'patientName' => $session->patient->name,
                                       'nurseName' => $session->nurse->name,
                                       'sessionStartTime' => $session->sessionStartTime->format('g:i A'),
                                       'sessionEndTime' => $session->sessionEndTime->format('g:i A'),
                                       'chair' => $session->chair->number,
                                       'roomName' => $session->room->name
                                   ];
                               });

    return response()->json(['dialysisSessions' => $dialysisSessions]);
}  





// public function getNurseDialysisSessions($sessionStatus, $day = null, $month = null, $year = null)
// {
//     $nurse = auth('user')->user();
//     $centerId = $nurse->medicalCenters()->first()->id;

//     $query = Appointment::with(['session', 'session.patient', 'session.nurse', 'chair']);






    
//     if ($sessionStatus === 'coming') {
//         $query->where('centerID', $centerId);

//     } else {
//         $query->where('nurseID', $nurse->id);
//     }

//     $query->where('valid', $sessionStatus);

//     if ($day && $month && $year) {
//         $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
//         $query->whereRaw('DATE_FORMAT(appointmentTimeStamp, "%Y-%m-%d") = ?', [$dateString]);
//     }

//     $dialysisSessions = $query->get()
//         ->map(function ($appointment) {
//             return [
//                 'id' => $appointment->session->id,
//                 'patientName' => $appointment->session->patient->fullName,
//                 'nurseName' => $appointment->session->nurse->fullName,
//                 'sessionStartTime' => $appointment->session->sessionStartTime,
//                 'sessionEndTime' => $appointment->session->sessionEndTime,
//                 'chair' => $appointment->chair->chairNumber,
//                 'roomName' => $appointment->chair->roomName,
//                 'sessionStatus' => $appointment->valid 
//             ];
//         });

//     return $dialysisSessions;
// }




public function getNurseDialysisSessions($sessionStatus, $day = null, $month = null, $year = null)
{
    $user = auth('user')->user();
    $query = Appointment::with(['session', 'session.patient', 'session.nurse', 'chair']);

    if ($user->role === 'admin') {
        $centerId = $user->medicalCenters()->first()->id;
        $query->where('centerID', $centerId)->where('valid', 'active');
    } else {
        if ($sessionStatus === 'coming') {
            $centerId = $user->medicalCenters()->first()->id;
            $query->where('centerID', $centerId);
        } else {
            $query->where('nurseID', $user->id);
        }
        $query->where('valid', $sessionStatus);
    }

    if ($day && $month && $year) {
        $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        $query->whereRaw('DATE_FORMAT(appointmentTimeStamp, "%Y-%m-%d") = ?', [$dateString]);
    }

    $dialysisSessions = $query->get()
        ->map(function ($appointment) {

            $sessionStartTime = Carbon::parse($appointment->session->sessionStartTime);
            $sessionEndTime = Carbon::parse($appointment->session->sessionEndTime);
            return [
                'id' => $appointment->session->id,
                'patientName' => $appointment->session->patient->fullName,
                'nurseName' => $appointment->session->nurse->fullName,
                'sessionStartTime' => $sessionStartTime->format('h:i:s'),
                'sessionEndTime' => $sessionEndTime->format('h:i:s'),
                'chair' => $appointment->chair->chairNumber,
                'roomName' => $appointment->chair->roomName,
                'sessionStatus' => $appointment->valid 
            ];
        });

    return $dialysisSessions;
}




function formatTimeToArabic($time) {
    $time = Carbon::createFromFormat('H:i:s', $time);
    $hours = $time->format('g');
    $minutes = $time->format('i');
    $meridiem = $time->format('A') === 'AM' ? 'صباحاً' : 'مساءً';

    $numbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $arabicNumbers = str_replace(range(0, 9), $numbers, $hours . ':' . $minutes);
    $text = 'الساعة ' . $arabicNumbers . ' ' . $meridiem;

    return $text;
}



public function startAppointment($appointmentId)
{
    $nurse = auth('user')->user();
    try {
        $appointment = Appointment::findOrFail($appointmentId);
        $appointment->valid = 'active';
        $appointment->nurseID = $nurse->id;
        $appointment->save();

        return 'تم حجز الموعد';
    } catch (ModelNotFoundException $e) {
        return 'الموعد غير موجود';
    }
}








public function updateDialysisSession($sessionId, array $data)
{
    $dialysisSession = DialysisSession::findOrFail($sessionId);

    $validator = Validator::make($data, [
        'sessionStartTime' => 'required|date',
        'sessionEndTime' => 'required|date|after:sessionStartTime',
        'weightBeforeSession' => 'required|numeric|min:0',
        'weightAfterSession' => 'required|numeric|min:0',
        'totalWithdrawalRate' => 'required|numeric|min:0',
        'withdrawalRateHourly' => 'required|numeric|min:0',
        'pumpSpeed' => 'required|numeric|min:0',
        'filterColor' => 'required|string|max:255',
        'filterType' => 'required|string|max:255',
        'vascularConnection' => 'required|string|max:255',
        'naConcentration' => 'required|numeric|min:0',
        'venousPressure' => 'required|integer|min:0',
        'status' => 'required|string|max:255',
        'sessionDate' => 'required|date',
        'patientID' => 'nullable|exists:users,id',
        'doctorID' => 'nullable|exists:users,id',
    ]);

    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }

    $validatedData = $validator->validated();
    
    DB::beginTransaction();
    try {
        $dialysisSession->update($validatedData);

        // تعديل الأدوية
        if (isset($data['medicines'])) {
            foreach ($data['medicines'] as $medicineData) {
                $this->updateSessionMedicine($medicineData);
            }
        }

        // تعديل قياسات ضغط الدم
        if (isset($data['bloodPressures'])) {
            foreach ($data['bloodPressures'] as $bloodPressureData) {
                $this->updateBloodPressureMeasurement($bloodPressureData);
            }
        }

        DB::commit();
        return $dialysisSession;
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}

public function updateSessionMedicine(  array $data)
{
    $validator = Validator::make($data, [
        'medicineTakenID'=> 'required|exists:medicine_takens,id',
        'medicineID' => 'required|exists:medicines,id',
        'value' => 'required|numeric|min:0'
    ]);

    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }

    $validatedData = $validator->validated();
    $medicine = MedicineTaken::findOrFail($validatedData['medicineTakenID']);
                           

    if (!$medicine) {
        throw new ModelNotFoundException('Medicine record not found for the given session.');
    }

    $medicine->update([
        'value' => $validatedData['value'],
       'medicineID' =>$validatedData['medicineID']
    ]);
}


public function updateBloodPressureMeasurement(array $data)
{
    $validator = Validator::make($data, [
        'id' => 'required|exists:blood_pressure_measurements,id',
        'pressureValue' => 'required|numeric|min:0',
        'pulseValue' => 'required|numeric|min:0',
        'time' => 'required|date_format:Y-m-d H:i:s'
       
    ]);

    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }

    $validatedData = $validator->validated();
    $bloodPressure = BloodPressureMeasurement::findOrFail($validatedData['id']);
    $bloodPressure->update($validatedData);
}












}