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

    
    public function addAppointment(array $data)
    {
        $validatedData = Validator::make($data, [
            'appointmentTimeStamp' => 'required|date',
            'userID' => 'required|exists:users,id',
            'shiftID' => 'required|exists:shifts,id',
            'chairID' => 'required|exists:chairs,id',
            'centerID' => 'required|exists:medical_centers,id'
        ])->validate();

        $appointment = new Appointment($validatedData);
        $appointment->save();
        return $appointment;
    }




    public function getAppointmentsByCenter($centerId)
{
    return Appointment::where('centerID', $centerId)
                      ->with(['shift', 'chair', 'user'])
                      ->get();
}


public function getAppointmentsByCenterAndDate($centerId, $year, $month, $day)
{
    return Appointment::where('centerID', $centerId)
                      ->whereYear('appointmentTimeStamp', $year)
                      ->whereMonth('appointmentTimeStamp', $month)
                      ->whereDay('appointmentTimeStamp', $day)
                      ->with(['shift', 'chair', 'user','nurse'])
                      ->get()
                      ->map(function ($appointment) {
                        $appointmentTime = Carbon::parse($appointment->appointmentTimeStamp)->format('H:i');
                          return [

                            'id' => $appointment->id,
                            'patientId' => $appointment->user->id,
                              'patientName' => $appointment->user->fullName,
                              'nurseName' => $appointment->nurse->fullName,
                              'roomName' => $appointment->chair->roomName,
                              'chairName' => $appointment->chair->chairNumber,
                              'appointmentTime' => $appointmentTime,
                              'startTime' => $appointment->start,
                              'valid' => $appointment->valid,
                              'sessionID' => $appointment->sessionID,
                              
                          ];
                      });
}

















public function getUserAppointments($userId)
{
    return Appointment::where('userID', $userId)
                      ->with(['shift', 'chair'])
                      ->get();
}

}
