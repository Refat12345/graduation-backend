<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\AppointmentServiceInterface ;
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



class AppointmentService implements AppointmentServiceInterface 
{

    
    // public function addAppointment(array $data)
    // {
    //     $validatedData = Validator::make($data, [
    //       //  'appointmentTimeStamp' => 'required|date_format:H:i',
    //         'day' => 'required|string',
    //         'userID' => 'required|exists:users,id',
    //         'shiftID' => 'required|exists:shifts,id',
    //         'chairID' => 'required|exists:chairs,id',
    //         'centerID' => 'required|exists:medical_centers,id'
    //     ])->validate();

    //    $shift = Shift::findOrFail($validatedData['shiftID']);

    //    // $time = Carbon::createFromFormat('H:i', $shift->shiftStart)->toTimeString();
    
    //     $appointment = new Appointment([
    //        'appointmentTimeStamp' => $shift->shiftStart,
    //         'day' => $validatedData['day'],
    //         'userID' => $validatedData['userID'],
    //         'shiftID' =>  $validatedData['shiftID'],
    //         'chairID' => $validatedData['chairID'],
    //         'centerID' =>  $validatedData['centerID'],
    //     ]);

    //     $appointment->save();
    //     return $appointment;
    // }



// public function getAppointmentsAvailability($centerId)
// {
//     $shifts = Shift::where('centerID', $centerId)->get();
//     $chairs = Chair::where('centerID', $centerId)->get();

//     $daysOfWeek = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

//     $totalAppointments = $shifts->count() * $chairs->count() * count($daysOfWeek);

//     $appointments = Appointment::where('centerID', $centerId)
//                     ->with(['user', 'shift', 'chair'])
//                     ->get();

  
//     $result = [
//         'totalAppointments' => $totalAppointments,
//         'availableAppointments' => $totalAppointments - $appointments->count(),
//         'unavailableAppointments' => $appointments->count(),
//         'appointments' => $appointments->map(function ($appointment) {
//             return [
//                 'patientName' => $appointment->user ? $appointment->user->fullName : null,
//                 'day' => $appointment->day,
//                 'shift' => [
//                     'shiftStart' => $appointment->shift->shiftStart,
//                     'shiftEnd' => $appointment->shift->shiftEnd,
//                     'name' => $appointment->shift->name,
//                 ],
//                 'chair' => [
//                     'chairNumber' => $appointment->chair->chairNumber,
//                     'roomName' => $appointment->chair->roomName,
//                 ],
//             ];
//         }),
//     ];

//     return $result;
// }

public function populateAppointments($centerId)
{
    $shifts = Shift::where('centerID', $centerId)->where('valid', -1)->get();
    $chairs = Chair::where('centerID', $centerId)->where('valid', -1)->get();

    $daysOfWeek = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

    
    Appointment::where('centerID', $centerId)
        ->whereNotIn('shiftID', $shifts->pluck('id'))
        ->orWhereNotIn('chairID', $chairs->pluck('id'))
        ->update(['isValid' => false]);

    foreach ($daysOfWeek as $day) {
        foreach ($shifts as $shift) {
            foreach ($chairs as $chair) {
                Appointment::firstOrCreate([
                    'day' => $day,
                    'shiftID' => $shift->id,
                    'chairID' => $chair->id,
                    'centerID' => $centerId,
                ], [
                    'appointmentTimeStamp' => $shift->shiftStart,
                    'valid' => 'available',
                    'isValid' => true,
                ]);
            }
        }
    }

    return 'تم انشاء جدول مواعيد المركز';
}



public function updateAppointmentsStatus($centerId)
{

    $appointments = Appointment::where('centerID', $centerId)
                               ->where('valid', '!=', 'available')
                               ->get();
                      
 
   
        foreach ($appointments as $appointment) {
            $appointment->valid = 'coming';
            $appointment->start = null;
            $appointment->nurseID = null;
            $appointment->save();
        }
    

    return 'تم تجديد المواعيد بنجاح';
}





public function assignAppointmentToUser($appointmentId, $userId)
{

    $appointment = Appointment::where('id', $appointmentId)
                    ->where('valid', 'available')
                    ->where('isValid', true)
                    ->first();

    if (!$appointment) {
        return 'الموعد محجوز مسبقا';
    }

 
    $appointment->update([
        'userID' => $userId,
        'valid' => 'coming',
    ]);

    return $appointment ;
}


public function swapAppointmentsBetweenUsers($appointmentId1, $appointmentId2)
{
    $appointment1 = Appointment::where('id', $appointmentId1)->first();
    $appointment2 = Appointment::where('id', $appointmentId2)->first();

    if (!$appointment1 || !$appointment2) {
        return 'أحد الموعدين غير موجود';
    }

    if ($appointment1->valid === 'active' ||$appointment2->valid === 'active' || !$appointment1->isValid  || !$appointment2->isValid) {
        return 'أحد الموعدين غير صالح';
    }

    $userId1 = $appointment1->userID;
    $userId2 = $appointment2->userID;

    $appointment1->update(['userID' => $userId2]);
    $appointment2->update(['userID' => $userId1]);

    return 'تم تبديل الموعدين بنجاح';
}



//     public function getAppointmentsByCenter($centerId)
// {
//     return Appointment::where('centerID', $centerId)
//                       ->with(['shift', 'chair', 'user'])
//                       ->get();
// }

public function getAppointmentsByCenter($centerId)
{
    return Appointment::where('centerID', $centerId)
                      ->with(['shift', 'chair', 'user'])
                      ->get()
                      ->map(function ($appointment) {
                          return [
                            'id' => $appointment->id,
                            'patientName' => $appointment->user->fullName ?? null,
                              'day' => $appointment->day,
                              'valid' => $appointment->valid,
                              'shiftID' => $appointment->shift->id,
                              'shiftStart' => $appointment->shift->shiftStart,
                              'shiftEnd' => $appointment->shift->shiftEnd,
                              'shiftName' => $appointment->shift->name,


                              'chairID' => $appointment->chair->id,
                              'chairNumber' => $appointment->chair->chairNumber,
                              'roomName' => $appointment->chair->roomName,
                          ];
                      });
}





// public function getAppointmentsByCenterAndDate($centerId, $year, $month, $day)
// {
//     return Appointment::where('centerID', $centerId)
//                     ->whereYear('appointmentTimeStamp', $year)
//                     ->whereMonth('appointmentTimeStamp', $month)
//                     ->whereDay('appointmentTimeStamp', $day)
//                     ->with(['shift', 'chair', 'user','nurse'])
//                     ->get()
//                     ->map(function ($appointment) {
//                         $user=  auth('user')->user();
//                         $appointmentTime = Carbon::parse($appointment->appointmentTimeStamp)->format('H:i');
//                         $nurse=null;
//                         $id=null;
//                         if($appointment->nurse){
//                             $id=$appointment->nurse->id;
//                     $nurse=$appointment->nurse->fullName;
//                         }
                        
//                         return [
//                             'id' => $appointment->id,
//                             'patientId' => $appointment->user->id,
//                             'patientName' => $appointment->user->fullName,
//                             'nurseName' => $nurse,
//                             'roomName' => $appointment->chair->roomName,
//                             'chair' => $appointment->chair->chairNumber,
//                             'appointmentTime' => $appointmentTime,
//                             'startTime' => $appointment->start,
//                             'valid' => $appointment->valid,
//                             'sessionID' => $appointment->sessionID,
//                             'nurseId' => $id,
//                         ];
//                     });
// }

public function getAppointmentsByCenterAndDate($centerId, $year, $month, $day)
{
 
    $appointments = Appointment::where('centerID', $centerId)
                    ->where('isValid', true)
                    ->whereIn('valid', ['coming', 'active'])
                    ->with(['shift', 'chair', 'user', 'nurse'])
                    ->get()
                    ->map(function ($appointment) {
                        $appointmentTime = Carbon::parse($appointment->appointmentTimeStamp)->format('H:i');
                       // $appointmentTime = $appointment->day . ' ' . $appointment->appointmentTimeStamp;
                        $nurse = $appointment->nurse ? $appointment->nurse->fullName : null;
                        $nurseId = $appointment->nurse ? $appointment->nurse->id : null;
                        $id=null;
                        return [
                            'id' => $appointment->id,
                            'patientId' => $appointment->user->id,
                            'patientName' => $appointment->user->fullName,
                            'nurseName' => $nurse,
                            'roomName' => $appointment->chair->roomName,
                            'chair' => $appointment->chair->chairNumber,
                            'appointmentTime' => $appointmentTime,
                            'startTime' => $appointment->start,
                            'valid' => $appointment->valid,
                            'sessionID' => $id,
                            'nurseId' => $nurseId,
                        ];
                    });

   
    $sessions = DialysisSession::where('centerID', $centerId)
                    ->whereYear('sessionEndTime', $year)
                    ->whereMonth('sessionEndTime', $month)
                    ->whereDay('sessionEndTime', $day)
                    ->with(['patient', 'nurse', 'doctor', 'appointment.chair'])
                    ->get()
                    ->map(function ($session) {
                        $nurse = $session->nurse ? $session->nurse->fullName : null;
                        $nurseId = $session->nurse ? $session->nurse->id : null;
                        $appointmentTime = Carbon::parse($session->appointment->appointmentTimeStamp)->format('H:i');
                        return [
                            
                            'id' => $session->appointment ->id,
                            'patientId' => $session->patient->id,
                            'patientName' => $session->patient->fullName,
                            'nurseName' => $nurse,
                            'roomName' => $session->appointment ? $session->appointment->chair->roomName : null,
                            'chair' => $session->appointment ? $session->appointment->chair->chairNumber : null,
                            'appointmentTime' => $appointmentTime, 
                            'startTime' => $session->sessionStartTime,
                            'valid' => 'finished',
                            'sessionID' => $session->id,
                            'nurseId' => $nurseId,
                        ];
                    });

    
    $appointmentsCollection = collect($appointments);
    $sessionsCollection = collect($sessions);

  
    $mergedResults = $appointmentsCollection->merge($sessionsCollection);

    return $mergedResults;
}




public function getUserAppointments($userId)
{
    return Appointment::where('userID', $userId)
                      ->with(['shift', 'chair'])
                      ->get();
}

}
