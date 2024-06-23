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






public function addPatientInfo(Request $request)
    {
        try {

      
            $patientData = $request->all();
            $this->userService->addPatientInfo($patientData);

         
            return response()->json(['message' => 'Patient information added successfully'], 200);
        } catch (\Exception $e) {
 
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }














    public function createUser(Request $request)
    {
        try{
        $userData = $request->all();

        $role = isset($userData['role']) ? $userData['role'] : null;

  
      
        $user = $this->userService->createUser($userData);
        return response()->json([$user], 200);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }

   


    public function loginUser(Request $request)
{
    try {
        $nationalNumber = $request->input('nationalNumber');
        $password = $request->input('password');
        $user = $this->userService->loginUser($nationalNumber, $password);
        
        if (!$user) {
            throw new \Exception('Invalid nationalNumber or password');
        }

        return response()->json(['user' => $user], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}




public function getUserByVerificationCode(Request $request)
{
    try {
        $verificationCode = $request->input('verificationCode');
      
        $user = $this->userService->getUserByVerificationCode($verificationCode);
        
        return response()->json(['user' => $user], 200);
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
  


    // public function verifyUser(Request $request)
    // {
    //     try {
          
    //         $verificationCode = $request->input('verificationCode');
    //         $password = $request->input('password');
    //       $user=  $this->userService->verifyAccount($verificationCode,$password);
    //         return response()->json(['message' => 'Account verified successfully.','user' =>$user]);

    //     } catch (\Exception $e) {
           
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }

    public function verifyUser(Request $request)
{
    try {
        $verificationCode = $request->input('verificationCode');
        $password = $request->input('password');
        $result = $this->userService->verifyAccount($verificationCode, $password);

        if ($result) {
            return response()->json([
                'message' => 'Account verified successfully',
                'user' =>   $result
            ]);
        }

        return response()->json(['error' => 'Login failed'], 400);

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

 
    public function updatePermissionsUser(Request $request)
    {
        $userId = $request->input('userId');
        $permissionNames = $request->input('permissionNames');
    
        try {
            $this->userService->updatePermissionsToUser($userId, $permissionNames);
            return response()->json(['message' => 'Permissions updated successfully.']);
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


public function createCenterTelecoms(Request $request)
{
    try{
    $validatedData = $request->validate([
        'centerID' => 'required|exists:medical_centers,id',
        'telecoms' => 'required|array',
        'telecoms.*.system' => 'required|string|max:255',
        'telecoms.*.value' => 'required|string|max:255|unique:telecoms,value',
        'telecoms.*.use' => 'nullable|string|max:255',
    ]);

    $centerId = $validatedData['centerID'];
    $telecomsData = $validatedData['telecoms'];

    $data = $this->userService->createCenterTelecoms($centerId, $telecomsData);

    return response()->json(['data' => $data], 200);

} catch (\Exception $e) {
    return response()->json(['error' => 'An unexpected error occurred', 'message' => $e->getMessage()], 500);
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




public function getCenterUsersByRole( $centerId, $role , $pat=null)
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
    return response()->json(['center' =>$CenterDetails], 200);
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




public function getNotesByreceiverID($receiverID)
{
    try {
    $notes = $this->userService->getNotesByreceiverID($receiverID);
    return response()->json(['notes' =>$notes], 200);
} catch (\Exception $e) {
         
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function getNotesByMedicalCenter($centerId)
{
    try {
    $notes = $this->userService->getNotesByMedicalCenter($centerId);
    return response()->json(['notes' =>$notes], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}

public function getlogs($centerId)
{
    try {

    $logs = $this->userService->getlogs($centerId);
    return response()->json(['logs' => $logs], 200);

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



public function getMedicineNames()
{
    $medicineNames = $this->userService->getMedicineNames();
    return response()->json(['medicine_names' => $medicineNames]);
}






/////////////////////////// accept /////////////////////////////



public function acceptaddShift(Request $request)
{
    $status = $request->input('status');
    $shiftId = $request->input('shiftId');
   // return $this->userService->acceptaddShift($shiftId, $status);

    return response()->json(['message' => $this->userService->acceptaddShift($shiftId, $status)]);
    
}


public function acceptAddChair(Request $request)
{
    $status = $request->input('status');
    $chairID = $request->input('chairID');
   // return $this->userService->acceptAddChair($chairID, $status);
    return response()->json(['message' =>  $this->userService->acceptAddChair($chairID, $status)]);
}




public function acceptAddMedicalRecord(Request $request)
{
    $status = $request->input('status');
    $medicalRecordID = $request->input('medicalRecordID');
  //  return $this->userService->acceptAddMedicalRecord($medicalRecordID, $status);
    return response()->json(['message' => $this->userService->acceptAddMedicalRecord($medicalRecordID, $status)]);
    
}



public function acceptAddDisbursedMaterialsUser(Request $request)
{
    $status = $request->input('status');
    $disbursedMaterialdID = $request->input('disbursedMaterialdID');
    //return $this->userService->acceptAddDisbursedMaterialsUser($disbursedMaterialdID, $status);
    return response()->json(['message' => $this->userService->acceptAddDisbursedMaterialsUser($disbursedMaterialdID, $status)]);

}



public function acceptPatientInformation(Request $request)
{
    $status = $request->input('status');
    $patientId = $request->input('patientId');
  //  return $this->userService->acceptPatientInformation($patientId, $status);
    return response()->json(['message' =>  $this->userService->acceptPatientInformation($patientId, $status)]);

}




public function getAddShiftsRequests($centerId)
{
   // return $this->userService->getAddShiftsRequests($centerId);
    return response()->json(['message' =>  $this->userService->getAddShiftsRequests($centerId)]);

}



public function getMedicalRecordRequests($centerId)
{
  //  return $this->userService->getMedicalRecordRequests($centerId);
    return response()->json(['message' =>  $this->userService->getMedicalRecordRequests($centerId)]);

}



public function getChairsInCenter($centerId)
{
  //  return $this->userService->getChairsInCenter($centerId);
    return response()->json(['message' =>  $this->userService->getChairsInCenter($centerId)]);

}


public function getAllPatientInfoRequests($centerId)
{
   // return $this->userService->getAllPatientInfoRequests($centerId);

    return response()->json(['message' =>  $this->userService->getAllPatientInfoRequests($centerId)]);

}



public function updateUser(Request $request)
{
   
    $id = $request->input('id');
    $userData = $request->except('id');

    try {
    
       $updatedUser = $this->userService->updateUser($id, $userData);

       
        return response()->json([
            
            'message' => 'تم تحديث بيانات المستخدم' ,
            'data' => $updatedUser
        ], 200);
    } catch (LogicException $e) {
   
        return response()->json([

            'message' => $e->getMessage(),
        ], 400);
    }
}


public function updateMedicalCenter(Request $request)
{
   
    $id = $request->input('id');
    $userData = $request->except('id');

    try {
    
     $updatedUser = $this->userService->updateMedicalCenter($id, $userData);

       
        return response()->json([
          //  'xx' =>  $id,
          'message' => 'تم تحديث بيانات المركز' ,
          'data' => $updatedUser
        ], 200);
    } catch (LogicException $e) {
   
        return response()->json([

            'message' => $e->getMessage(),
        ], 400);
    }
}


public function updatePatientInfo(Request $request)
{
   
    $id = $request->input('id');
    $data = $request->except('id');

    try {
    
     $updated = $this->userService->updatePatientInfo($id, $data);

       
        return response()->json([
          //  'xx' =>  $id,
          'message' => 'تم تحديث المعلومات العامة للمستخدم' ,
          'data' => $updated
        ], 200);
    } catch (LogicException $e) {
   
        return response()->json([

            'message' => $e->getMessage(),
        ], 400);
    }
}




// public function updateMedicalCenter(Request $request)
// {
   
//     $id = $request->input('id');
//     $userData = $request->except('id');

//     try {
    
//       //  $updated= $this->userService->updateMedicalCenter( $id , $data);

       
//         return response()->json([
//             'message' => $id,
//         //    'data' => $updated
//         ], 200);
//     } catch (LogicException $e) {
   
//         return response()->json([

//             'message' => $e->getMessage(),
//         ], 400);
//     }
// }


public function getPatientsByCenter($centerID)
{
   // $centerID = $request->input('center_id'); 
    $patientsData = $this->userService->getPatientsByCenter($centerID);


    return response()->json([
        'patients' => $patientsData
    ]);
}


public function updatePatientStatus($patientID, $newStatus)
{
    $message = $this->userService->updatePatientStatus($patientID, $newStatus);


    return response()->json([
        'message' => $message
    ]);
}





}
