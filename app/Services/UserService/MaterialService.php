<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\MaterialServiceInterface;
use App\Models\User;
use App\Models\Telecom;
use App\Models\GeneralPatientInformation;
use App\Models\PatientCompanion;
use App\Models\Address;
use App\Models\City;
use App\Models\Permission;
use App\Models\MaritalStatus;
use App\Models\Country;
use App\Models\MedicalCenter;
use App\Models\UserCenter;
use App\Models\GlobalRequest;
use App\Models\PatientTransferRequest;
use App\Models\RequestModifyAppointment;
use App\Models\Requests;
use App\Models\Appointment;
use App\Models\Shift;
use App\Models\Chair;
use App\Models\UserShift;
use App\Models\DialysisSession;
use App\Models\Note;
use App\Models\Medicine;
use App\Models\MedicineTaken;
use App\Models\BloodPressureMeasurement;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\AllergicCondition;
use App\Models\AnalysisType;
use App\Models\MedicalAnalysis;
use App\Models\SurgicalHistory;

use App\Models\PathologicalHistory;
use App\Models\PharmacologicalHistory;
use App\Models\DisbursedMaterial;
use App\Models\DisbursedMaterialsUser;

use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
class MaterialService implements MaterialServiceInterface
{

    
public function addNewMedicine(array $data): Medicine
{
    $validator = Validator::make($data, [
        'name' => 'required|string|max:255',
        'titer' => 'required|numeric|min:0'
    ]);

    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }

    return Medicine::create($validator->validated());
}




public function getAllMedicines()
{
    return Medicine::get(['name', 'titer', 'id', 'created_at', 'updated_at']);
}




public function createDisbursedMaterial(array $materialData)
{
    $validator = Validator::make($materialData, [
        'materialName' => 'required|string|max:255',
        'date' => 'required|date',
    ]);

    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }

    $validatedMaterialData = $validator->validated();

    $disbursedMaterial = DisbursedMaterial::create([
        'materialName' => $validatedMaterialData['materialName'],
        'date' => $validatedMaterialData['date'],
    ]);

    return $disbursedMaterial;
}




public function assignMaterialToUserCenter(array $assignmentData)
{
    $validator = Validator::make($assignmentData, [
        'userID' => 'required|exists:users,id',
        'centerID' => 'required|exists:medical_centers,id',
        'disbursedMaterialID' => 'required|exists:disbursed_materials,id',
        'quantity' => 'required|numeric|min:0',
        'status' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }

    $validatedAssignmentData = $validator->validated();

    \DB::beginTransaction();
    try {
        $disbursedMaterialsUser = new DisbursedMaterialsUser([
            'userID' => $validatedAssignmentData['userID'],
            'centerID' => $validatedAssignmentData['centerID'],
            'disbursedMaterialID' => $validatedAssignmentData['disbursedMaterialID'],
            'quantity' => $validatedAssignmentData['quantity'],
            'status' => $validatedAssignmentData['status'],
        ]);
        $disbursedMaterialsUser->save();

        \DB::commit();
        return $disbursedMaterialsUser;
    } catch (\Exception $e) {
        \DB::rollback();
        throw $e;
    }
}




public function getDisbursedMaterialsDetailsForUser($userID) {
    $disbursedMaterials = DisbursedMaterialsUser::with(['disbursedMaterial', 'medicalCenter'])
                                ->where('userID', $userID)
                                ->get();

    return $disbursedMaterials;
}




public function getDisbursedMaterialsForCenterInTimeRange($centerID, $startDate, $endDate) {
    $disbursedMaterials = DisbursedMaterialsUser::with(['disbursedMaterial', 'user'])
                                ->where('centerID', $centerID)
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->get();

    return $disbursedMaterials;
}



}