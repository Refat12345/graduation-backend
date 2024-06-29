<?php

declare(strict_types=1);

namespace App\Contracts\Services\UserService;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Collection;

interface RequestsServiceInterface 
{
   
     public function addPatientTransferRequest(array $data);
     public function addRequestModifyAppointment(array $data);

    // public function addGlobalRequest(array $data);
     public function updateStatus( $requestId, $newStatus);


    public function getAllRequests();


public function acceptaddShift($shiftId, $status);
public function acceptAddChair($chairID, $status);
public function acceptAddMedicalRecord($medicalRecordID, $status);
public function acceptAddDisbursedMaterialsUser($disbursedMaterialdID, $status);
public function acceptPatientInformation($userId, $status);
public function getAddShiftsRequests($centerId);
public function getMedicalRecordRequests($centerId);
public function getAllPatientInfoRequests($centerId);




}

