<?php

declare(strict_types=1);

namespace App\Services\UserService;

use App\Contracts\Services\UserService\StatisticsServiceInterface;
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




class StatisticsService implements StatisticsServiceInterface {



    // public function getPieCharts($month, $year)
    // {
    //     $user = auth('user')->user();
    //     $centerId = $user->userCenter->centerID;
    
    //     $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
    
    //     $materials = ['heparin', 'iron', 'epoetin'];
    //     $totalValues = [];
    
    //     foreach ($materials as $material) {
    //         $disbursedValue = DisbursedMaterialsUser::whereHas('disbursedMaterial', function ($query) use ($material) {
    //             $query->where('materialName', $material);
    //         })->where('centerID', $centerId)
    //           ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$dateString])
    //           ->sum('quantity');
    
    //         $takenValue = MedicineTaken::whereHas('medicine', function ($query) use ($material) {
    //             $query->where('name', $material);
    //         })->whereHas('dialysisSession', function ($query) use ($centerId) {
    //             $query->where('centerID', $centerId);
    //         })->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$dateString])
    //           ->sum('value');
    
    //         $totalValues[$material] = $disbursedValue + $takenValue;
    //     }
    
    //     return $totalValues;
    // }

    public function getAllPieCharts($month = null, $year = null)
{
    $materials = ['الهيبارين', 'الحديد', 'الايبوتين'];
    $totalValues = [];
    
    foreach ($materials as $material) {
        $disbursedQuery = DisbursedMaterialsUser::whereHas('disbursedMaterial', function ($query) use ($material) {
            $query->where('materialName', $material);
        })->where('valid', -1);
        
        $takenQuery = MedicineTaken::whereHas('medicine', function ($query) use ($material) {
            $query->where('name', $material);
        })->whereHas('dialysisSession');

        if ($month !== null && $year !== null) {
            $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
            $disbursedQuery->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$dateString]);
            $takenQuery->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$dateString]);
        }

        $disbursedValue = $disbursedQuery->sum('quantity');
        $takenValue = $takenQuery->sum('value');
        
        $totalValues[$material] = $disbursedValue + $takenValue;
    }
    
    return $totalValues;
}

    public function getPieCharts($month = null, $year = null)
{
    $user = auth('user')->user();
    $centerId = $user->userCenter->centerID;
    
    $materials = ['الهيبارين', 'الحديد', 'الايبوتين'];
    $totalValues = [];
    
    foreach ($materials as $material) {
        $disbursedQuery = DisbursedMaterialsUser::whereHas('disbursedMaterial', function ($query) use ($material) {
            $query->where('materialName', $material);
        })->where('centerID', $centerId)->where('valid', -1);
        
        $takenQuery = MedicineTaken::whereHas('medicine', function ($query) use ($material) {
            $query->where('name', $material);
        })->whereHas('dialysisSession', function ($query) use ($centerId) {
            $query->where('centerID', $centerId);
        });
        
        if ($month !== null && $year !== null) {
            $dateString = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
            $disbursedQuery->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$dateString]);
            $takenQuery->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$dateString]);
        }

        $disbursedValue = $disbursedQuery->sum('quantity');
        $takenValue = $takenQuery->sum('value');
        
        $totalValues[$material] = $disbursedValue + $takenValue;
    }
    
    return $totalValues;
}

    
    
    // public function causeRenalFailure()
    // {
    //     $user = auth('user')->user();
    //     $centerID = $user->userCenter->centerID;
    
    //     $causes = ['diabetes', 'heartDiseases', 'bloodPressure', 'otherDiseases'];
    //     $totalCounts = [];
    
    //     foreach ($causes as $cause) {
    //         $count = User::whereHas('medicalRecord', function ($query) use ($centerID, $cause) {
    //             $query->whereHas('user.userCenter', function ($query) use ($centerID) {
    //                 $query->where('centerID', $centerID);
    //             })->where('causeRenalFailure', $cause);
    //         })->count();
    
    //         $totalCounts[$cause] = $count;
    //     }
    
    //     return $totalCounts;
    // }

    

    public function causeRenalFailure()
{
    $user = auth('user')->user();
    $centerID = $user->userCenter->centerID;

    $causes = ['diabetes', 'heartDiseases', 'bloodPressure'];
    $totalCounts = [];

    foreach ($causes as $cause) {
        $count = User::whereHas('medicalRecord', function ($query) use ($centerID, $cause) {
            $query->whereHas('user.userCenter', function ($query) use ($centerID) {
                $query->where('centerID', $centerID);
            })->where('causeRenalFailure', $cause)->where('valid', -1);
        })->count();

        $totalCounts[$cause] = $count;
    }

    $otherCount = User::whereHas('medicalRecord', function ($query) use ($centerID, $causes) {
        $query->whereHas('user.userCenter', function ($query) use ($centerID) {
            $query->where('centerID', $centerID);
        })->whereNotIn('causeRenalFailure', $causes);
    })->count();

    $totalCounts['otherDiseases'] = $otherCount;

    return $totalCounts;
}

    
    
    
    
    public function getCenterStatistics()
    {
        $user = auth('user')->user();
        $centerID = $user->userCenter->centerID;
    
        $statistics = [
            'patients' => 0,
            'dialysisSessions' => 0,
            'waitingList' => 0
        ];
    
        $statistics['patients'] = GeneralPatientInformation::whereHas('user', function ($query) use ($centerID) {
            $query->whereHas('userCenter', function ($query) use ($centerID) {
                $query->where('centerID', $centerID);
            });
        })->where('status', 'accepted')->count();
    
        $statistics['dialysisSessions'] = DialysisSession::where('centerID', $centerID)->count();
    
        $statistics['waitingList'] = GeneralPatientInformation::whereHas('user', function ($query) use ($centerID) {
            $query->whereHas('userCenter', function ($query) use ($centerID) {
                $query->where('centerID', $centerID);
            });
        })->where('status', 'waiting')->count();
    
        return $statistics;
    }




    public function getAllCenterStatistics()
    {
        $statistics = [
            'patients' => 0,
            'dialysisSessions' => 0,
            'waitingList' => 0,
            'nurses' => 0,
            'doctors' => 0,
            'secretaries' => 0
        ];
    
        $statistics['patients'] = GeneralPatientInformation::whereHas('user', function ($query) {
            $query->whereHas('userCenter');
        })->where('status', 'accepted')->count();
    
        $statistics['dialysisSessions'] = DialysisSession::count();
    
        $statistics['waitingList'] = GeneralPatientInformation::whereHas('user', function ($query) {
            $query->whereHas('userCenter');
        })->where('status', 'waiting')->count();
    
        $statistics['nurses'] = User::where('role', 'nurse')->where('valid', -1)->count();
    
        $statistics['doctors'] = User::where('role', 'doctor')->where('valid', -1)->count();
    
        $statistics['secretaries'] = User::where('role', 'secretary')->where('valid', -1)->count();
    
        return $statistics;
    }








    public function getCterStatistics()
    {
        $statistics = MedicalCenter::withCount([
            'users as doctors_count' => function ($query) {
                $query->where('role', 'doctor');
            },
            'users as nurses_count' => function ($query) {
                $query->where('role', 'nurse');
            },
            'users as secretaries_count' => function ($query) {
                $query->where('role', 'secretary');
            },
            'users as patients_count' => function ($query) {
                $query->whereHas('generalPatientInformation', function ($subQuery) {
                    $subQuery->where('status', 'accepted');
                });
            },
            'dialysisSessions_count' => function ($query) {
                $query->where('valid', 1);
            },
            'users as waiting_list_count' => function ($query) {
                $query->whereHas('generalPatientInformation', function ($subQuery) {
                    $subQuery->where('status', 'waiting');
                });
            }
        ])->get();
    
        $totalDoctors = 0;
        $totalNurses = 0;
        $totalSecretaries = 0;
        $totalPatients = 0;
        $totalDialysisSessions = 0;
        $totalWaitingList = 0;
    
        foreach ($statistics as $center) {
            $totalDoctors += $center->doctors_count;
            $totalNurses += $center->nurses_count;
            $totalSecretaries += $center->secretaries_count;
            $totalPatients += $center->patients_count;
            $totalDialysisSessions += $center->dialysisSessions_count;
            $totalWaitingList += $center->waiting_list_count;
        }
    
        $totalStatistics = [
            'totalDoctors' => $totalDoctors,
            'totalNurses' => $totalNurses,
            'totalSecretaries' => $totalSecretaries,
            'totalPatients' => $totalPatients,
            'totalDialysisSessions' => $totalDialysisSessions,
            'totalWaitingList' => $totalWaitingList
        ];
    
        return $totalStatistics;
    }
    
    
    }


