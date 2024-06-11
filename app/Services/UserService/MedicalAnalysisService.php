<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\MedicalAnalysisServiceInterface;
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
class MedicalAnalysisService implements MedicalAnalysisServiceInterface
{
  
public function createMedicalAnalysis(array $MedicalAnalysisData )
{
   $validator = Validator::make($MedicalAnalysisData, [
    'averageMin' => 'required|numeric',
    'averageMax' => 'required|numeric',
    'value' => 'required|numeric',
    'analysisDate' => 'required|date',
    'notes' => 'required|string|max:255',
    'quarter' => 'required|string|max:255',
    'analysisTypeID' => [
        'required',
        'integer',
        Rule::exists('analysis_types', 'id'),
    ],
     'userID' => [
        'required',
        'integer',
        Rule::exists('users', 'id')->where(function ($query) {
            $query->where('role', 'patient');
        }),

    ],   ]);

if ($validator->fails()) {
    throw new LogicException($validator->errors()->first());
}

DB::beginTransaction();
try {
     $MedicalAnalysis = MedicalAnalysis::create($MedicalAnalysisData);
    DB::commit();
    return $MedicalAnalysis;
} catch (\Exception $e) {
    DB::rollBack();
    throw new LogicException('Error creating MedicalAnalysis: ' . $e->getMessage());
}
}





public function createAnalysisType(array $AnalysisTypeData)
{
    $validator = Validator::make($AnalysisTypeData, [
        'analysisName' => [
            'required',
            'string',
            'max:255',
            Rule::unique('analysis_types', 'analysisName'),
        ],
        'recurrenceInterval' => 'required|Integer',
     'unitOfMeasurement' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        throw new LogicException($validator->errors()->first());
    }

    DB::beginTransaction();
    try {
        $AnalysisType = AnalysisType::create($AnalysisTypeData);
        DB::commit();
        return $AnalysisType;
    } catch (\Exception $e) {
        DB::rollBack();
        throw new LogicException('Error creating AnalysisType: ' . $e->getMessage());
    }
}





public function getMedicalAnalysisWithAnalysisType($userID)
{
    $medicalAnalyses = MedicalAnalysis::with('analysisType')
                                      ->where('userID', $userID)
                                      ->get()
                                      ->map(function ($analysis) {
                                          $analysisDate = Carbon::parse($analysis->analysisDate);
                                          $quarter = $analysisDate->quarter;
                                          
                                          $quarterArabic = '';
                                          switch ($quarter) {
                                              case 1:
                                                  $quarterArabic = 'الربع الأول';
                                                  break;
                                              case 2:
                                                  $quarterArabic = 'الربع الثاني';
                                                  break;
                                              case 3:
                                                  $quarterArabic = 'الربع الثالث';
                                                  break;
                                              case 4:
                                                  $quarterArabic = 'الربع الرابع';
                                                  break;
                                          }

                                          return [
                                              'analysisName' => $analysis->analysisType->analysisName,
                                              'value' => $analysis->value,
                                              'unitOfMeasurement' => $analysis->analysisType->unitOfMeasurement ?? 'null',
                                              'analysisDate' => $analysisDate->format('YYY-mm-dd'), 
                                              'quarter' => $quarterArabic,
                                              'notes' => $analysis->notes
                                          ];
                                      });

    return $medicalAnalyses;
}





}
