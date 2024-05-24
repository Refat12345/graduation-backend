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
    public function getDialysisSessionsWithChairInfo($centerId);
    public function getDialysisSessions($centerId);
    public function getCompleteDialysisSessionDetails($sessionId);
    
}
