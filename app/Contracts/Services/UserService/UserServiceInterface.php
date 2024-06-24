<?php

declare(strict_types=1);

namespace App\Contracts\Services\UserService;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Collection;


interface UserServiceInterface
{
    /**
     * @param Phone $phone
     * @return User
     * @throws ModelNotFoundException
     */






     public function addPatientTransferRequest(array $data);
     public function addRequestModifyAppointment(array $data);
     public function getAllRequests();
     public function addGlobalRequest(array $data);
     public function updateStatus( $requestId, $newStatus);
     public function createUser(array $userData): User;
     public function loginUser(string $nationalNumber, string $password);
  


    /**
     * Associate a user with a medical center by name.
     *
     * @param User $user
     * @param string $centerName
     * @return void
     */
    public function associateUserWithMedicalCenter(User $user, string $centerName);

    /**
     * Create address information for a user.
     *
     * @param User $user
     * @param array $addressData
     * @return void
     */
    public function createUserAddress(User $user, array $addressData);

    /**
     * Create telecom information for a user.
     *
     * @param User $user
     * @param array $telecomData
     * @return void
     */
    public function createUserTelecoms(User $user, array $telecomData);

    
   
    public function findUserBy(string $value): Collection;

    public function changeAccountStatus(User $user, string $newStatus);
    public function getUserByVerificationCode(string $verificationCode);

    public function verifyAccount( string $verificationCode,string $password);
   


//////////////////////////////////////// new ////////////////////////////////////////////

    public function addGeneralPatientInformationWithMaritalStatus(array $data);
    public function addPermissionsToUser($userId, array $permissions);
    public function getUserPermissions($userId);
    public function addPatientCompanionWithTelecom(array $companionData, array $telecomData); 


    public function addMedicalCenterWithUser(array $centerData);





    ////////////////////////////// Request /////////////////////////////


    
/////////////////////////   shift & chair  //////////////

    public function addChair(array $data);
    public function addShift(array $data);
    



    //////////// appointment ///////////



/////////////////////// shift ////////////////////////

    public function assignUserToShift(array $data);
    public function getShiftsByCenter($centerId);
    public function getDoctorsInShift($shiftId);


 
    public function getCenterUsersByRole($centerId, $role ,$pat);

    public function getUserDetails($userId);
    public function getMedicalCenterDetails($centerId);



    public function createCenterTelecoms($centerId, array $telecomsData);
    public function createNote(array $userData);
   // public function getNotesByMedicalCenter($centerId);
    
    public function getNotesByreceiverID($receiverID);
    public function getlogs($centerId);


    public function addPatientInfo(array $data);

    public function getMedicineNames();
    










public function acceptaddShift($shiftId, $status);
public function acceptAddChair($chairID, $status);
public function acceptAddMedicalRecord($medicalRecordID, $status);
public function acceptAddDisbursedMaterialsUser($disbursedMaterialdID, $status);
public function acceptPatientInformation($userId, $status);
public function getAddShiftsRequests($centerId);
public function getMedicalRecordRequests($centerId);
public function getChairsInCenter($centerId);
public function getAllPatientInfoRequests($centerId);

public function updatePermissionsUser($userId, array $permissionNames);

// public function updateUserAddress(User $user, array $addressData);
// public function updateUserTelecoms(User $user, array $telecomData);
public function updateUser($id, array $userData): User;
public function updateMedicalCenter($centerId, array $centerData);
public function getPatientsByCenter($centerID);

public function updatePatientStatus($patientID, $newStatus);
public function updatePatientInfo($patientId, array $data);

public function updateShift($shiftId, array $data);
public function updateChair($chairId, array $data);
}