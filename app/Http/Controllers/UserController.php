<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\Services\UserService\UserServiceInterface;      

use App\Models\User;
use App\Models\GlobalRequest;
use App\Models\PatientTransferRequest;
use App\Models\RequestModifyAppointment;
use App\Models\Requests;


class UserController extends Controller
{
    protected $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

 



    public function createPatientTransferRequest(Request $request)
    {
       try{
        $result = $this->userService->addPatientTransferRequest($request->all());

        if ($result instanceof PatientTransferRequest) {
            return response()->json([$result], 200);
        }

        return response()->json([$result], 400);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }
 






    public function createRequestModifyAppointment(Request $request)
    {
     try {
        $result = $this->userService->addRequestModifyAppointment($request->all());
        if ($result instanceof RequestModifyAppointment) {
            return response()->json([$result], 200);
        }
        return response()->json([$result], 400);

    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }




    public function createGlobalRequest(Request $request)
    {
        try{
    
        $result = $this->userService->addGlobalRequest($request->all());
    
        if ($result instanceof GlobalRequest) {
            return response()->json([$result], 200);
        }
    
        return response()->json([$result], 400);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }




    public function getAllRequests()
    {
        try{

        $allRequests = $this->userService->getAllRequests();
       
        return response()->json([$allRequests], 200);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }




    // new_status : pending,approved,rejected
public function changeReruestStatus(Request $request)
{

    
    try {
    $requestId = $request->input('request_id');
    $newStatus = $request->input('new_status');

    $this->userService->updateStatus($requestId, $newStatus);
    return response()->json(['message' => 'Request status updated successfully'], 200);
    }

 catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}
    
}
















    public function createUser(Request $request)
    {
        try{
        $userData = $request->all();
        $user = $this->userService->createUser($userData);
        return response()->json([$user], 200);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }

   

    public function loginUser(Request $request)
    {
     try{
        $nationalNumber = $request->input('nationalNumber');
        $password = $request->input('password');
        $user = $this->userService->loginUser($nationalNumber, $password);
        if ($user) {
            $token = $user->createToken('myauth')->plainTextToken;
      
            return response()->json([
                'status' => true,
                'message' => 'welcome',
                'token' => $token,
                'data' => $user,
              
            ], 200);
        }  else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }


  

    public function findUser(Request $request)
    {
        try{
        $value = $request->input('value');
        return response()->json($this->userService->findUserBy($value));
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }





public function associateUserWithMedicalCenter(Request $request)
{
    try {
    $centerName = $request->input('centerName');
    $user = User::findOrFail($request->input('userID'));
    $this->userService->associateUserWithMedicalCenter( $user ,$centerName );
    return response()->json(['message' => 'user associated successfully']);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}



    public function changeStatus(Request $request)
    {
        try {    
        
        $user = User::findOrFail($request->input('userID'));
        $newStatus = $request->input('status');
        $this->userService->changeAccountStatus($user, $newStatus);
        return response()->json(['message' => 'Account status updated successfully']);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }
  


    public function verifyUser(Request $request)
    {
        try {
            $user = auth('user')->user();
            $verificationCode = $request->input('verificationCode');
            $this->userService->verifyAccount($user, $verificationCode);
            return response()->json(['message' => 'Account verified successfully.']);

        } catch (\Exception $e) {
           
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }





    ////////////////////////// new /////////////////////////


    public function addGeneralPatientInformation(Request $request)
    {
        try{
        $data = $request->all();
        $this->userService->addGeneralPatientInformationWithMaritalStatus($data);
        return response()->json(['message' => 'General Patient Information added successfully']);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }




    public function addPatientCompanion(Request $request)
    {
        try{
        $companionData = $request->input('companionData');
        $telecomDataArray = $request->input('telecomDataArray'); 
    
        $this->userService->addPatientCompanionWithTelecom($companionData, $telecomDataArray);
        return response()->json(['message' => 'Patient Companion added successfully']);

    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }



    public function assignPermissions(Request $request)
    {
        $userId = $request->input('userId');
        $permissionNames = $request->input('permissionNames');
    
        try {
            $this->userService->addPermissionsToUser($userId, $permissionNames);
            return response()->json(['message' => 'Permissions assigned successfully.']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function getUserPermissions($userId)
    {

        try {
            $permissions = $this->userService->getUserPermissions($userId);
            return response()->json(['permissions' => $permissions]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function createMedicalCenter(Request $request)
    {
        try {
        $centerData = $request->all();
        $medicalCenter = $this->userService->addMedicalCenterWithUser($centerData);
        return response()->json([$medicalCenter], 200);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }
    




    //////////////////// Request ///////////////////////////////////


/////////////////////////   shift & chair  //////////////
public function createChair(Request $request)
{
    try{
    $chair = $this->userService->addChair($request->all());
  
    return response()->json([$chair], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function createShift(Request $request)
{ 
    try {
    $shift = $this->userService->addShift($request->all());
    return response()->json([$shift], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




///////////////// appointments //////////////////////




public function assignUserToShift(Request $request)
{
    try{
    $userShift = $this->userService->assignUserToShift($request->all());
    return response()->json([$userShift], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function showShiftsByCenter($centerId)
{
    try{
    $shifts = $this->userService->getShiftsByCenter($centerId);
    return response()->json([$shifts], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function showDoctorsInShift($shiftId)
{
    try {
    $doctors = $this->userService->getDoctorsInShift($shiftId);
    return response()->json([$doctors], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function getCenterUsersByRole( $centerId, $role , $pat)
{
    try{
    $staff = $this->userService->getCenterUsersByRole($centerId, $role, $pat);
    return response()->json([$staff], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function showUserDetails( $userId)
{
    try {
    $userDetails = $this->userService->getUserDetails($userId);

    return response()->json([$userDetails], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}





public function showMedicalCenterDetails( $centerId)
{
    try{
    $CenterDetails = $this->userService->getMedicalCenterDetails($centerId);
    return response()->json([$CenterDetails], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function createNote(Request $request)
{
try {
 
    $noteData = $request->all();
    $note = $this->userService->createNote($noteData);
    return response()->json([$note], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function getNotesByMedicalCenter($centerId)
{
    try {
    $notes = $this->userService->getNotesByMedicalCenter($centerId);
    return response()->json([$notes], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}













// public function getPrescriptionsByPatient( $patientID)
// {
//     try {
//     $patient = User::findOrFail($patientID);
//     $prescriptions = $this->userService->getPrescriptionsByPatient($patient);
//     return response()->json([$prescriptions], 200);
    
// } catch (\Exception $e) {
           
//     return response()->json(['error' => $e->getMessage()], 400);
// }
// }




// public function getPatientPrescriptions()
// {
//     try {

//     $patientID =  auth('user')->user()->id;
//     $patient = User::findOrFail($patientID);
//     $prescriptions = $this->userService->getPrescriptionsByPatient($patient);
//     return response()->json([$prescriptions], 200);
    
// } catch (\Exception $e) {
           
//     return response()->json(['error' => $e->getMessage()], 400);
// }
// }











/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////





public function getAllMedicalCenters()
{
    return response()->json(['medicalCenters' => $this->userService->getAllMedicalCenters()], 200);
}








}
