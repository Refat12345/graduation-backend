<?php

namespace App\Http\Controllers;

use App\Contracts\Services\UserService\MedicalRecordServiceInterface;
use Illuminate\Http\Request;
use Log;

class MedicalRecordController extends Controller
{
    protected $medicalRecordService;

    public function __construct(MedicalRecordServiceInterface $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
    }




public function createMedicalRecord(Request $request)
{
  
    try {
       

        $medicalRecord = $this->medicalRecordService->createMedicalRecord($request->all());
     
        Log::info('Medical Record: ', (array) $medicalRecord);
        return response()->json([$medicalRecord], 200);
        
    } catch (LogicException $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}





public function showMedicalRecord($userID)
{
  
    return response()->json([ 'medicalRecord' => $this->medicalRecordService->getMedicalRecordWithDetails($userID)], 200);
}






public function createAllergicCondition(Request $request)
{
    try {
    $AllergicConditionData = $request->all();

    $medical=  $this->medicalRecordService->createAllergicCondition($AllergicConditionData);

    return response()->json([$medical], 200);
} catch (LogicException $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function addSurgicalHistory(Request $request)
{
    $data = $request->all();
    $result = $this->medicalRecordService->createSurgicalHistory($data);
 
    return response()->json([$result], 200);
}





public function addPathologicalHistory(Request $request)
{
    $data = $request->all();
    $result = $this->medicalRecordService->createPathologicalHistory($data);

    return response()->json([$result], 200);
}




public function addPharmacologicalHistory(Request $request)
{
    $data = $request->all();
    $result = $this->medicalRecordService->createPharmacologicalHistory($data);

    return response()->json([ $result], 200);
}


}
