<?php

namespace App\Http\Controllers;

use App\Contracts\Services\UserService\MedicalAnalysisServiceInterface;      

use App\Models\MedicalAnalysis;
use Illuminate\Http\Request;

class MedicalAnalysisController extends Controller
{
    protected $medicalAnalysisService;

    public function __construct(MedicalAnalysisServiceInterface $medicalAnalysisService)
    {
        $this->medicalAnalysisService = $medicalAnalysisService;
    }


    

public function addMedicalAnalysis(Request $request)
{
    $data = $request->all();
    $result = $this->medicalAnalysisService->createMedicalAnalysis($data);

    return response()->json([$result], 200);
}


public function addAnalysisType(Request $request)
{
    $data = $request->all();
    $result = $this->medicalAnalysisService->createAnalysisType($data);

    return response()->json([$result], 200);
}


public function showMedicalAnalysis($userID)
{
    return response()->json(['analysis' => $this->medicalAnalysisService->getMedicalAnalysisWithAnalysisType($userID)], 200);
}


public function getAnalysisTypes()
{
    return response()->json(['analysisTypes' => $this->medicalAnalysisService->getAnalysisTypes()], 200);
}




public function updateMedicalAnalysis(Request $request)
{
    $medicalAnalysisId = $request->input('Id');

    $MedicalAnalysisData = $request->except('Id');

    $updatedAnalysis = $this->medicalAnalysisService->updateMedicalAnalysis($medicalAnalysisId, $MedicalAnalysisData);

    return response()->json($updatedAnalysis);
}



}
