<?php

namespace App\Http\Controllers;

use App\Contracts\Services\UserService\MaterialServiceInterface;      

use App\Models\MedicalAnalysis;
use App\Models\DisbursedMaterial;
use Illuminate\Http\Request;

class DisbursedMaterialController extends Controller
 {
    protected $service;

    public function __construct(MaterialServiceInterface $service) {
        $this->service = $service;
    }


    
public function addMedicine(Request $request)
{
    try {
    $data = $request->all();
   
    return response()->json([$this->service->addNewMedicine($data)], 200);
} catch (\Exception $e) {
           
    return response()->json(['error' => $e->getMessage()], 400);
}
}




public function getMedicines()
{
    try{
    return response()->json([$this->service->getAllMedicines()], 200);

} catch (\Exception $e) {     
    return response()->json(['error' => $e->getMessage()], 400);
}
}






public function createDisbursedMaterial(Request $request)
{
    try {
        $disbursedMaterial = $this->service->createDisbursedMaterial($request->all());

        return response()->json([$disbursedMaterial], 200);
    } catch (LogicException $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}





public function assignMaterialToUserCenter(Request $request)
{
    try {
        $disbursed= $this->service->assignMaterialToUserCenter($request->all());

        return response()->json([$disbursed], 200);
    } catch (LogicException $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}





public function getDisbursedMaterialsDetailsForUser(Request $request)
{
    try {
    $user_id = $request->input('user_id');

   $materialsDetails = $this->service->getDisbursedMaterialsDetailsForUser($user_id);
    return response()->json([$materialsDetails], 200);
    }

 catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}


    
}

public function getAllUsersWithDisbursedMaterials()
{
    try {


        

   $data = $this->service->getAllUsersWithDisbursedMaterials();
    return response()->json(['data' => $data], 200);
    }

 catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}


    
}




public function getMaterialNames()
{
    $materials = DisbursedMaterial::all();
    return $materials;
}




public function getDisbursedMaterialsForCenterInTimeRange(Request $request)
{
    try {
    $centerID = $request->input('centerID');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');



    
   $materialsDetails = $this->service->getDisbursedMaterialsForCenterInTimeRange($centerID, $startDate, $endDate);
    return response()->json([ $materialsDetails], 200);
    }

 catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}
    
}


}