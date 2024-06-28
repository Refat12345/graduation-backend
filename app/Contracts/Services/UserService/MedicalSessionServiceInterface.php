<?php

declare(strict_types=1);

namespace App\Contracts\Services\UserService;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Collection;


interface MedicalSessionServiceInterface
{
 

    public function createDialysisSession(array $data);
    public function getDialysisSessionsWithChairInfo($centerId, $month, $year);
    public function getPatientDialysisSessions($patientId, $month, $year);
    public function getDialysisSessions($centerId);
    public function getCompleteDialysisSessionDetails($sessionId);
    public function getNurseDialysisSessions($sessionStatus, $day = null, $month = null, $year = null);
    public function startAppointment($appointmentId);
    public function updateDialysisSession($sessionId, array $data);
 
}
