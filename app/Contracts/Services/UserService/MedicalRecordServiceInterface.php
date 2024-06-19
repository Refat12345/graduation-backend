<?php

declare(strict_types=1);

namespace App\Contracts\Services\UserService;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Collection;


interface MedicalRecordServiceInterface
{
 
    public function createMedicalRecord(array $MedicalRecordData);
    public function getMedicalRecordWithDetails($userID);
    public function createAllergicCondition(array $AllergicConditionData );
    public function createSurgicalHistory(array $SurgicalHistoryData );
    public function createPharmacologicalHistory(array $PharmacologicalHistoryData );
    public function createPathologicalHistory(array $PathologicalHistoryData );
    public function updateMedicalRecord($id, array $MedicalRecordData);
    public function updatePharmacologicalHistory(array $PharmacologicalHistoryData);
    public function updateAllergicCondition(array $AllergicConditionData);
    public function updatePathologicalHistory(array $PathologicalHistoryData);

}