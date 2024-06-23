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
Route::post('/updateUser', 'App\Http\Controllers\UserController@updateUser');
Route::post('/updateMedicalCenter', 'App\Http\Controllers\UserController@updateMedicalCenter');
Route::post('/updatePatientInfo', 'App\Http\Controllers\UserController@updatePatientInfo');

Route::post('/getUserByVerificationCode', [UserController::class, 'getUserByVerificationCode']);
Route::post('/verify', [UserController::class, 'verifyUser']);
Route::post('/change', [UserController::class, 'changeStatus']);



Route::post('add-general-patient-info', [UserController::class, 'addGeneralPatientInformation']);
Route::post('add-patient-companion', [UserController::class, 'addPatientCompanion']);
Route::post('assign-permissions', [UserController::class, 'assignPermissions']);
Route::post('updatePermissionsUser', [UserController::class, 'updatePermissionsUser']);
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



Route::get('/getCenterUsersByRole/{centerId}/{role}/{pat?}', [UserController::class, 'getCenterUsersByRole']);
Route::get('/user/{userId}', [UserController::class, 'showUserDetails']);



Route::get('/center/{centerId}', [UserController::class, 'showMedicalCenterDetails']);


Route::post('/createCenterTelecoms', [UserController::class, 'createCenterTelecoms']);
Route::post('/createNote', [UserController::class, 'createNote']);
Route::get('/getNotesByMedicalCenter/{centerId}', [UserController::class, 'getNotesByMedicalCenter']);
Route::get('/getlogs/{centerId}', [UserController::class, 'getlogs']);
Route::get('/getNotesByreceiverID/{receiverID}', [UserController::class, 'getNotesByreceiverID']);

Route::get('/getDialysisSessionDetails/{sessionId}', [MedicalSessionController::class, 'getDialysisSessionDetails']);
Route::post('/createDialysisSession', [MedicalSessionController::class, 'createDialysisSession']);

Route::get('/getNurseDialysisSessions/{sessionStatus}/{day?}/{month?}/{year?}', [MedicalSessionController::class, 'getNurseDialysisSessions']);
Route::post('/startAppointment/{appointmentId}', [MedicalSessionController::class, 'startAppointment']);



Route::post('/addMedicine', [DisbursedMaterialController::class, 'addMedicine']);
Route::get('/getMedicines', [DisbursedMaterialController::class, 'getMedicines']);
Route::get('/getMaterialNames', [DisbursedMaterialController::class, 'getMaterialNames']);

Route::post('/addPrescription', [PrescriptionController::class, 'addPrescription']);
Route::post('/updatePrescription/{PrescriptionId}', [PrescriptionController::class, 'updatePrescription']);
Route::get('/getPrescriptionsByPatient/{patientID?}', [PrescriptionController::class, 'getPrescriptionsByPatient']);


Route::get('/getPatientPrescriptions', [PrescriptionController::class, 'getPatientPrescriptions']);

Route::post('/change-request-status', [UserController::class, 'changeReruestStatus']);

Route::post('/addPatientInfo', [UserController::class, 'addPatientInfo']);

Route::get('/getPatientsByCenter/{centerID}', [UserController::class, 'getPatientsByCenter']);
Route::post('/updatePatientStatus/{patientID}/{status}', [UserController::class, 'updatePatientStatus']);


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
Route::post('/updateMedicalAnalysis', [MedicalAnalysisController::class, 'updateMedicalAnalysis']);



Route::post('/addAnalysisType', [MedicalAnalysisController::class, 'addAnalysisType']);


Route::get('/getPatientDialysisSessions/{patientId?}/{month?}/{year?}', [MedicalSessionController::class, 'getPatientDialysisSessions']);
Route::get('/getDialysisSessions/{centerId}/{month?}/{year?}', [MedicalSessionController::class, 'getDialysisSessions']);

Route::get('getAllMedicalCenters', [UserController::class, 'getAllMedicalCenters']);
Route::get('getPieCharts/{month?}/{year?}', [StatisticsController::class, 'getPieCharts']);


Route::get('getMedicineNames', [UserController::class, 'getMedicineNames']);


Route::get('causeRenalFailure', [StatisticsController::class, 'causeRenalFailure']);
Route::get('getCenterStatistics', [StatisticsController::class, 'getCenterStatistics']);


Route::post('/createMedicalRecord', [MedicalRecordController::class, 'createMedicalRecord']);
Route::post('/updateMedicalRecord', [MedicalRecordController::class, 'updateMedicalRecord']);



Route::post('acceptaddShift', [UserController::class, 'acceptaddShift']);
Route::post('acceptAddChair', [UserController::class, 'acceptAddChair']);
Route::post('acceptAddMedicalRecord', [UserController::class, 'acceptAddMedicalRecord']);
Route::post('acceptAddDisbursedMaterialsUser', [UserController::class, 'acceptAddDisbursedMaterialsUser']);
Route::post('acceptPatientInformation', [UserController::class, 'acceptPatientInformation']);

Route::get('getAddShiftsRequests/{centerId}', [UserController::class, 'getAddShiftsRequests']);
Route::get('getMedicalRecordRequests/{centerId}', [UserController::class, 'getMedicalRecordRequests']);
Route::get('getChairsInCenter/{centerId}', [UserController::class, 'getChairsInCenter']);
Route::get('getAllPatientInfoRequests/{centerId}', [UserController::class, 'getAllPatientInfoRequests']);


// Route::post('/acceptaddShift', 'UserController@acceptaddShift');
// Route::post('/acceptAddChair', 'UserController@acceptAddChair');
// Route::post('/acceptAddMedicalRecord', 'UserController@acceptAddMedicalRecord');
// Route::post('/acceptAddDisbursedMaterialsUser', 'UserController@acceptAddDisbursedMaterialsUser');
// Route::post('/acceptPatientInformation', 'UserController@acceptPatientInformation');
// Route::get('/getAddShiftsRequests/{centerId}', 'UserController@getAddShiftsRequests');
// Route::get('/getMedicalRecordRequests/{centerId}', 'UserController@getMedicalRecordRequests');
// Route::get('/getChairsInCenter/{centerId}', 'UserController@getChairsInCenter');
// Route::get('/getAllPatientInfoRequests/{centerId}', 'UserController@getAllPatientInfoRequests');





$roles = [ 'admin'];
$permissions = ['view-dashboard', 'edit-dashboard', 'view-reports'];



Route::middleware(CheckRole::class . ':' . implode(',', $roles))->group(function () {
   
 
});






    Route::middleware([

        CheckRole::class . ':' . implode(',', $roles),
        SecondMiddleware::class . ':' . implode(',', $permissions)
    ])->group(function () {
     
    });



