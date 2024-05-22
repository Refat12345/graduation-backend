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


     
         public function createUser(array $userData): User;

         public function loginUser(string $nationalNumber, string $password): ?User;
  


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
    public function verifyAccount(User $user, string $verificationCode);





//////////////////////////////////////// new ////////////////////////////////////////////

    public function addGeneralPatientInformationWithMaritalStatus(array $data);
    public function addPermissionsToUser($userId, array $permissions);
    public function addPatientCompanionWithTelecom(array $companionData, array $telecomData); 


    public function addMedicalCenterWithUser(array $centerData);





    ////////////////////////////// Request /////////////////////////////


    public function addPatientTransferRequest(array $data);
    public function addRequestModifyAppointment(array $data);
    public function getAllRequests();


    
/////////////////////////   shift & chair  //////////////

    public function addChair(array $data);
    public function addShift(array $data);
    



    //////////// appointment ///////////
    public function addAppointment(array $data);
    public function getAppointmentsByCenter($centerId);
    public function getUserAppointments($userId);


/////////////////////// shift ////////////////////////

    public function assignUserToShift(array $data);
    public function getShiftsByCenter($centerId);
    public function getDoctorsInShift($shiftId);


 
    public function getCenterUsersByRole($centerId, $role);

    public function getUserDetails($userId);
    public function getMedicalCenterDetails($centerId);




    public function createNote(array $userData);
    public function getNotesByMedicalCenter($centerId);



  


    /////////////////////////////////////////////////////////////////////////////////////////////////////////

   
    public function getPieCharts($month, $year);
    public function causeRenalFailure();
    public function getCenterStatistics();
    
}