<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Contracts\Services\UserService\MedicalSessionServiceInterface;      

use App\Models\User;
use App\Models\GlobalRequest;
use App\Models\PatientTransferRequest;
use App\Models\RequestModifyAppointment;
use App\Models\Requests;

class MedicalSessionController extends Controller
{
    protected $medicalSessionService;

    public function __construct(MedicalSessionServiceInterface $medicalSessionService)
    {
        $this->medicalSessionService = $medicalSessionService;
    }



    public function getDialysisSessions($centerId, $month, $year)
    {
        try {
            $sessions = $this->medicalSessionService->getDialysisSessionsWithChairInfo($centerId, $month, $year);
            return response()->json(['dialysisSessions' => $sessions], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    
    public function getDialysisSessionDetails($sessionsId)
    {
        try {
            $sessions = $this->medicalSessionService->getCompleteDialysisSessionDetails($sessionsId);
            return response()->json(['dialysisSession' => $sessions], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function createDialysisSession(Request $request)
{
    try {
        $dialysisSession = $this->medicalSessionService->createDialysisSession($request->all());

        return response()->json([$dialysisSession], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}






}
