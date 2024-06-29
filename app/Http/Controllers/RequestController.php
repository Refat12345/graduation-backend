<?php

namespace App\Http\Controllers;

use App\Models\GlobalRequest;
use Illuminate\Http\Request;   
use App\Contracts\Services\UserService\RequestsServiceInterface;   
use App\Models\Requests;



    class RequestController extends Controller
{
    protected $requestsService;

    public function __construct(RequestsServiceInterface $requestsService)
    {
        $this->requestsService = $requestsService;
    }



    // public function createPatientTransferRequest(Request $request)
    // {
    //    try{
    //     $result = $this->requestsService->addPatientTransferRequest($request->all());

    //     if ($result instanceof PatientTransferRequest) {
    //         return response()->json([$result], 200);
    //     }

    //     return response()->json([$result], 400);
    // } catch (\Exception $e) {
           
    //     return response()->json(['error' => $e->getMessage()], 400);
    // }
    // }


    
    public function getAllRequests()
    {
        try{

        $allRequests = $this->requestsService->getAllRequests();
       
        return response()->json([$allRequests], 200);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }


    public function createPatientTransferRequest(Request $request)
    {
       try{
        $result = $this->requestsService->addPatientTransferRequest($request->all());

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
        $result = $this->requestsService->addRequestModifyAppointment($request->all());
        if ($result instanceof RequestModifyAppointment) {
            return response()->json([$result], 200);
        }
        return response()->json([$result], 400);

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

     $this->requestsService->updateStatus($requestId, $newStatus);
    return response()->json(['message' => 'Request status updated successfully'], 200);
    }

 catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}
    
}

 



public function getAddShiftsRequests($centerId)
{
   // return $this->requestsService->getAddShiftsRequests($centerId);
    return response()->json(['message' =>  $this->requestsService->getAddShiftsRequests($centerId)]);

}






public function acceptAddDisbursedMaterialsUser(Request $request)
{
    $status = $request->input('status');
    $disbursedMaterialdID = $request->input('disbursedMaterialdID');
    //return $this->requestsService->acceptAddDisbursedMaterialsUser($disbursedMaterialdID, $status);
    return response()->json(['message' => $this->requestsService->acceptAddDisbursedMaterialsUser($disbursedMaterialdID, $status)]);

}



public function acceptPatientInformation(Request $request)
{
    $status = $request->input('status');
    $patientId = $request->input('patientId');
  //  return $this->requestsService->acceptPatientInformation($patientId, $status);
    return response()->json(['message' =>  $this->requestsService->acceptPatientInformation($patientId, $status)]);

}




public function acceptaddShift(Request $request)
{
    $status = $request->input('status');
    $shiftId = $request->input('shiftId');
   // return $this->requestsService->acceptaddShift($shiftId, $status);

    return response()->json(['message' => $this->requestsService->acceptaddShift($shiftId, $status)]);
    
}


public function acceptAddChair(Request $request)
{
    $status = $request->input('status');
    $chairID = $request->input('chairID');
   // return $this->requestsService->acceptAddChair($chairID, $status);
    return response()->json(['message' =>  $this->requestsService->acceptAddChair($chairID, $status)]);
}




public function acceptAddMedicalRecord(Request $request)
{
    $status = $request->input('status');
    $medicalRecordID = $request->input('medicalRecordID');
  //  return $this->requestsService->acceptAddMedicalRecord($medicalRecordID, $status);
    return response()->json(['message' => $this->requestsService->acceptAddMedicalRecord($medicalRecordID, $status)]);
    
}




public function getMedicalRecordRequests($centerId)
{
  //  return $this->requestsService->getMedicalRecordRequests($centerId);
    return response()->json(['message' =>  $this->requestsService->getMedicalRecordRequests($centerId)]);

}


public function getAllPatientInfoRequests($centerId)
{
   // return $this->requestsService->getAllPatientInfoRequests($centerId);

    return response()->json(['message' =>  $this->requestsService->getAllPatientInfoRequests($centerId)]);

}
















}
