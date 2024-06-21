<?php

declare(strict_types=1);

namespace App\Contracts\Services\UserService;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Collection;


interface PrescriptionServiceInterface
{

    public function addPrescription(array $data);
  //  public function getPrescriptionsByPatient(User $patient): Collection;
    public function getAllPrescriptionsForUser($userId);
    public function updatePrescription($prescriptionId, array $data);
    
}
