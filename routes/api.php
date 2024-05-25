<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MedicalSessionController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicalAnalysisController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\DisbursedMaterialController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckPermission;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
//Route::post('/createUser', [UserController::class, 'createUser']); 


Route::post('/login', [UserController::class, 'loginUser']);
Route::post('/search', [UserController::class, 'findUser']);
Route::post('/createUser', 'App\Http\Controllers\UserController@createUser');
Route::post('/verify', [UserController::class, 'verifyUser']);

Route::post('/change', [UserController::class, 'changeStatus']);



Route::post('add-general-patient-info', [UserController::class, 'addGeneralPatientInformation']);
Route::post('add-patient-companion', [UserController::class, 'addPatientCompanion']);
Route::post('assign-permissions', [UserController::class, 'assignPermissions']);
Route::get('getUserPermissions/{userId}', [UserController::class, 'getUserPermissions']);

Route::post('/createMedicalCenter', [UserController::class, 'createMedicalCenter']);



Route::post('/global-requests', [UserController::class, 'createGlobalRequest']);
Route::post('/patient-transfer-requests', [UserController::class, 'createPatientTransferRequest']);
Route::post('/modify-appointment-requests', [UserController::class, 'createRequestModifyAppointment']);
Route::get('/all-requests', [UserController::class, 'getAllRequests']);



Route::post('/associateUserWithMedicalCenter', [UserController::class, 'associateUserWithMedicalCenter']);

Route::post('/chairs', [UserController::class, 'createChair']);
Route::post('/shifts', [UserController::class, 'createShift']);




Route::post('/appointments', [AppointmentController::class, 'createAppointment']);
Route::get('/centerappointments/{centerId}', [AppointmentController::class, 'showAppointmentsByCenter']);
Route::get('/userappointments/{userId}', [AppointmentController::class, 'showUserAppointments']);



Route::post('/user-shifts', [UserController::class, 'assignUserToShift']);
Route::get('/shifts/center/{centerId}', [UserController::class, 'showShiftsByCenter']);
Route::get('/doctors/shift/{shiftId}', [UserController::class, 'showDoctorsInShift']);



Route::get('/getCenterUsersByRole/{centerId}/{role}', [UserController::class, 'getCenterUsersByRole']);
Route::get('/user/{userId}', [UserController::class, 'showUserDetails']);



Route::get('/center/{centerId}', [UserController::class, 'showMedicalCenterDetails']);



Route::post('/createNote', [UserController::class, 'createNote']);
Route::get('/getNotesByMedicalCenter/{centerId}', [UserController::class, 'getNotesByMedicalCenter']);



Route::get('/getDialysisSessionDetails/{sessionId}', [MedicalSessionController::class, 'getDialysisSessionDetails']);
Route::post('/createDialysisSession', [MedicalSessionController::class, 'createDialysisSession']);



Route::post('/addMedicine', [DisbursedMaterialController::class, 'addMedicine']);
Route::get('/getMedicines', [DisbursedMaterialController::class, 'getMedicines']);


Route::post('/addPrescription', [PrescriptionController::class, 'addPrescription']);
Route::get('/getPrescriptionsByPatient/{patientID?}', [PrescriptionController::class, 'getPrescriptionsByPatient']);


Route::get('/getPatientPrescriptions', [PrescriptionController::class, 'getPatientPrescriptions']);

Route::post('/change-request-status', [UserController::class, 'changeReruestStatus']);




Route::post('/createDisbursedMaterial', [DisbursedMaterialController::class, 'createDisbursedMaterial']);
Route::post('assignMaterialToUserCenter', [DisbursedMaterialController::class, 'assignMaterialToUserCenter']);
Route::get('/getDisbursedMaterialsDetailsForUser', [DisbursedMaterialController::class, 'getDisbursedMaterialsDetailsForUser']);
Route::get('/getDisbursedMaterialsForCenterInTimeRange', [DisbursedMaterialController::class, 'getDisbursedMaterialsForCenterInTimeRange']);





/////////////////////////////////////////////////////


Route::post('/createAllergicCondition', [MedicalRecordController::class, 'createAllergicCondition']);



Route::post('/addSurgicalHistory', [MedicalRecordController::class, 'addSurgicalHistory']);
Route::post('/addPathologicalHistory', [MedicalRecordController::class, 'addPathologicalHistory']);
Route::post('/addPharmacologicalHistory', [MedicalRecordController::class, 'addPharmacologicalHistory']);
Route::get('/showMedicalRecord/{userID}', [MedicalRecordController::class, 'showMedicalRecord']);



Route::get('/showMedicalAnalysis/{userID}', [MedicalAnalysisController::class, 'showMedicalAnalysis']);
Route::post('/addMedicalAnalysis', [MedicalAnalysisController::class, 'addMedicalAnalysis']);
Route::post('/addAnalysisType', [MedicalAnalysisController::class, 'addAnalysisType']);



Route::get('/getDialysisSessions/{centerId}/{month}/{year}', [MedicalSessionController::class, 'getDialysisSessions']);

Route::get('getAllMedicalCenters', [UserController::class, 'getAllMedicalCenters']);
Route::get('getPieCharts/{month}/{year}', [StatisticsController::class, 'getPieCharts']);

Route::get('causeRenalFailure', [StatisticsController::class, 'causeRenalFailure']);
Route::get('getCenterStatistics', [StatisticsController::class, 'getCenterStatistics']);



$roles = [ 'admin'];
$permissions = ['view-dashboard', 'edit-dashboard', 'view-reports'];



Route::middleware(CheckRole::class . ':' . implode(',', $roles))->group(function () {
   
    Route::post('/createMedicalRecord', [MedicalRecordController::class, 'createMedicalRecord']);
});




    Route::middleware([

        CheckRole::class . ':' . implode(',', $roles),
        SecondMiddleware::class . ':' . implode(',', $permissions)
    ])->group(function () {
     
    });



