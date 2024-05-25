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
                    $appointment->save();
                }}
    
            DB::commit();
            return $dialysisSession;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function getDialysisSessionsWithChairInfo($centerId, $month, $year)
    {
        $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
        $query = Appointment::with(['session', 'session.patient', 'session.nurse', 'chair'])
            ->whereHas('session', function ($sessionQuery) use ($centerId, $dateString) {
                if ($centerId > 0) {
                    $sessionQuery->where('centerID', $centerId);
                }
                $sessionQuery->whereRaw('DATE_FORMAT(sessionEndTime, "%Y-%m") = ?', [$dateString]);
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
                    'roomName' => $appointment->chair->roomName
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
}