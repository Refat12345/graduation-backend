<?php

declare(strict_types=1);

namespace App\Contracts\Services\UserService;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Collection;

interface MaterialServiceInterface
{
 

    public function addNewMedicine(array $data);
    public function getAllMedicines();
    public function getAllUsersWithDisbursedMaterials();
    public function createDisbursedMaterial(array $materialData);
    public function assignMaterialToUserCenter(array $assignmentData);
    public function getDisbursedMaterialsDetailsForUser($userID);
    public function getDisbursedMaterialsForCenterInTimeRange($centerID, $startDate, $endDate);

    
}
