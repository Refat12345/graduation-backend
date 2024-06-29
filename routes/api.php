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


    // دكتور: doctor
    // ممرض: nurse
    // مريض: patient
    // مدير مركز: admin
    //  مدير برنامج : superAdmin
    // سكرتاريا: secretary
  
// request status : pending,approved,rejected

//  account status material status :       
//          active
//          nonActive



Route::post('/login', [UserController::class, 'loginUser']);
Route::post('/search', [UserController::class, 'findUser']);



$admin_superAdmin_secretary = [ 'admin','superAdmin','secretary'];
Route::middleware(CheckRole::class . ':' . implode(',', $admin_superAdmin_secretary))->group(function () {
    Route::post('/createUser', 'App\Http\Controllers\UserController@createUser');
 
});







Route::post('/updateMedicalCenter', 'App\Http\Controllers\UserController@updateMedicalCenter');

Route::post('/getUserByVerificationCode', [UserController::class, 'getUserByVerificationCode']);
Route::post('/verify', [UserController::class, 'verifyUser']);
Route::post('/change', [UserController::class, 'changeStatus']);


Route::post('/associateUserWithMedicalCenter', [UserController::class, 'associateUserWithMedicalCenter']);






Route::post('/createMedicalCenter', [UserController::class, 'createMedicalCenter']);


Route::post('/global-requests', [UserController::class, 'createGlobalRequest']);



Route::post('/modify-appointment-requests', [RequestController::class, 'createRequestModifyAppointment']);








Route::get('/centerappointments/{centerId}', [AppointmentController::class, 'showAppointmentsByCenter']);

Route::get('/getAppointmentsByCenterAndDate/{centerId}/{year}/{month}/{day}', [AppointmentController::class, 'getAppointmentsByCenterAndDate']);


Route::get('/userappointments/{userId}', [AppointmentController::class, 'showUserAppointments']);



Route::post('/user-shifts', [UserController::class, 'assignUserToShift']);
Route::get('/shifts/center/{centerId}', [UserController::class, 'showShiftsByCenter']);
Route::get('/doctors/shift/{shiftId}', [UserController::class, 'showDoctorsInShift']);

Route::get('getAllCenters', [UserController::class, 'getAllCenters']);


Route::get('/getCenterDoctors/{centerId}', [UserController::class, 'getCenterDoctors']);
Route::get('/getCenterUsersByRole/{centerId}/{role}/{pat?}', [UserController::class, 'getCenterUsersByRole']);
Route::get('/user/{userId}', [UserController::class, 'showUserDetails']);



Route::get('/center/{centerId}', [UserController::class, 'showMedicalCenterDetails']);


Route::post('/createCenterTelecoms', [UserController::class, 'createCenterTelecoms']);
Route::post('/createNote', [UserController::class, 'createNote']);
Route::get('/getNotesByMedicalCenter/{centerId}', [UserController::class, 'getNotesByMedicalCenter']);
Route::get('/getlogs/{centerId}', [UserController::class, 'getlogs']);
Route::get('/getNotesByreceiverID/{receiverID}', [UserController::class, 'getNotesByreceiverID']);

Route::get('/getDialysisSessionDetails/{sessionId}', [MedicalSessionController::class, 'getDialysisSessionDetails']);

Route::get('/getNurseDialysisSessions/{sessionStatus}/{day?}/{month?}/{year?}', [MedicalSessionController::class, 'getNurseDialysisSessions']);
Route::post('/startAppointment/{appointmentId}', [MedicalSessionController::class, 'startAppointment']);



Route::post('/addMedicine', [DisbursedMaterialController::class, 'addMedicine']);
Route::get('/getMedicines', [DisbursedMaterialController::class, 'getMedicines']);
Route::get('/getMaterialNames', [DisbursedMaterialController::class, 'getMaterialNames']);


Route::get('/getPrescriptionsByPatient/{patientID?}', [PrescriptionController::class, 'getPrescriptionsByPatient']);


Route::get('/getPatientPrescriptions', [PrescriptionController::class, 'getPatientPrescriptions']);




Route::get('/getPatientsByCenter/{centerID}', [UserController::class, 'getPatientsByCenter']);
Route::post('/updatePatientStatus/{patientID}/{status}', [UserController::class, 'updatePatientStatus']);


Route::post('/createDisbursedMaterial', [DisbursedMaterialController::class, 'createDisbursedMaterial']);
Route::get('/getDisbursedMaterialsDetailsForUser', [DisbursedMaterialController::class, 'getDisbursedMaterialsDetailsForUser']);



Route::get('/getDisbursedMaterialsForCenterInTimeRange', [DisbursedMaterialController::class, 'getDisbursedMaterialsForCenterInTimeRange']);





/////////////////////////////////////////////////////




Route::get('/showMedicalRecord/{userID}', [MedicalRecordController::class, 'showMedicalRecord']);



Route::get('/showMedicalAnalysis/{userID}', [MedicalAnalysisController::class, 'showMedicalAnalysis']);



Route::get('/getAnalysisTypes', [MedicalAnalysisController::class, 'getAnalysisTypes']);

Route::get('/getPatientDialysisSessions/{patientId?}/{month?}/{year?}', [MedicalSessionController::class, 'getPatientDialysisSessions']);
Route::get('/getDialysisSessions/{centerId}/{month?}/{year?}', [MedicalSessionController::class, 'getDialysisSessions']);

Route::get('getAllMedicalCenters', [UserController::class, 'getAllMedicalCenters']);



Route::get('getMedicineNames/{type}', [UserController::class, 'getMedicineNames']);




$secretary_doctor = ['secretary','doctor'];

Route::middleware(CheckRole::class . ':' . implode(',', $secretary_doctor))->group(function () {

Route::post('/createMedicalRecord', [MedicalRecordController::class, 'createMedicalRecord']);
Route::post('/updateMedicalRecord', [MedicalRecordController::class, 'updateMedicalRecord']);

Route::post('/addSurgicalHistory', [MedicalRecordController::class, 'addSurgicalHistory']);
Route::post('/addPathologicalHistory', [MedicalRecordController::class, 'addPathologicalHistory']);
Route::post('/addPharmacologicalHistory', [MedicalRecordController::class, 'addPharmacologicalHistory']);

Route::post('add-general-patient-info', [UserController::class, 'addGeneralPatientInformation']);
Route::post('add-patient-companion', [UserController::class, 'addPatientCompanion']);

Route::post('/addMedicalAnalysis', [MedicalAnalysisController::class, 'addMedicalAnalysis']);
Route::post('/updateMedicalAnalysis', [MedicalAnalysisController::class, 'updateMedicalAnalysis']);

Route::post('/createAllergicCondition', [MedicalRecordController::class, 'createAllergicCondition']);

Route::post('/addAnalysisType', [MedicalAnalysisController::class, 'addAnalysisType']);
Route::post('/updatePatientInfo', 'App\Http\Controllers\UserController@updatePatientInfo');

Route::post('/addPatientInfo', [UserController::class, 'addPatientInfo']);

Route::post('/addPrescription', [PrescriptionController::class, 'addPrescription']);
Route::post('/updatePrescription/{PrescriptionId}', [PrescriptionController::class, 'updatePrescription']);

});






$secretary = ['secretary'];
Route::middleware(CheckRole::class . ':' . implode(',', $secretary))->group(function () {

Route::post('assignMaterialToUserCenter', [DisbursedMaterialController::class, 'assignMaterialToUserCenter']);
Route::post('/appointments', [AppointmentController::class, 'createAppointment']);
Route::post('/updateUser', 'App\Http\Controllers\UserController@updateUser');
});


$nurse = ['nurse'];
Route::middleware(CheckRole::class . ':' . implode(',', $nurse))->group(function () {

    Route::post('/createDialysisSession', [MedicalSessionController::class, 'createDialysisSession']);
    Route::post('/updateDialysisSession', [MedicalSessionController::class, 'updateDialysisSession']);
    






});






$secretary_admin = ['secretary','admin'];

Route::middleware(CheckRole::class . ':' . implode(',', $secretary_admin))->group(function () {

Route::get('/getAllUsersWithDisbursedMaterials', [DisbursedMaterialController::class, 'getAllUsersWithDisbursedMaterials']);

Route::get('/all-requests', [RequestController::class, 'getAllRequests']);



Route::post('/chairs', [UserController::class, 'createChair']);
Route::post('/updateChair', [UserController::class, 'updateChair']);


Route::post('/shifts', [UserController::class, 'createShift']);
Route::post('/updateShift', [UserController::class, 'updateShift']);

Route::post('/updateShifts', [UserController::class, 'updateShifts']);




});






$admin = ['admin'];
Route::middleware(CheckRole::class . ':' . implode(',', $admin))->group(function () {

    Route::get('causeRenalFailure', [StatisticsController::class, 'causeRenalFailure']);
    Route::get('getCenterStatistics', [StatisticsController::class, 'getCenterStatistics']);

    Route::post('acceptaddShift', [UserController::class, 'acceptaddShift']);
    Route::post('acceptAddChair', [UserController::class, 'acceptAddChair']);
    Route::post('acceptAddMedicalRecord', [UserController::class, 'acceptAddMedicalRecord']);
    Route::post('acceptAddDisbursedMaterialsUser', [UserController::class, 'acceptAddDisbursedMaterialsUser']);
    Route::post('acceptPatientInformation', [UserController::class, 'acceptPatientInformation']);

    Route::get('getAddShiftsRequests/{centerId}', [RequestController::class, 'getAddShiftsRequests']);
    Route::get('getMedicalRecordRequests/{centerId}', [RequestController::class, 'getMedicalRecordRequests']);
    Route::get('getChairsInCenter/{centerId}', [UserController::class, 'getChairsInCenter']);
    Route::get('getAllPatientInfoRequests/{centerId}', [RequestController::class, 'getAllPatientInfoRequests']);
    
    Route::get('getPieCharts/{month?}/{year?}', [StatisticsController::class, 'getPieCharts']);

    Route::post('assign-permissions', [UserController::class, 'assignPermissions']);
    Route::post('updatePermissionsUser', [UserController::class, 'updatePermissionsUser']);
    Route::get('getUserPermissions/{userId}', [UserController::class, 'getUserPermissions']);

    Route::post('/change-request-status', [RequestController::class, 'changeReruestStatus']);
    
Route::post('/patient-transfer-requests', [RequestController::class, 'createPatientTransferRequest']);
});



// Route::post('/acceptaddShift', 'UserController@acceptaddShift');
// Route::post('/acceptAddChair', 'UserController@acceptAddChair');
// Route::post('/acceptAddMedicalRecord', 'UserController@acceptAddMedicalRecord');
// Route::post('/acceptAddDisbursedMaterialsUser', 'UserController@acceptAddDisbursedMaterialsUser');
// Route::post('/acceptPatientInformation', 'UserController@acceptPatientInformation');
// Route::get('/getAddShiftsRequests/{centerId}', 'UserController@getAddShiftsRequests');
// Route::get('/getMedicalRecordRequests/{centerId}', 'UserController@getMedicalRecordRequests');
// Route::get('/getChairsInCenter/{centerId}', 'UserController@getChairsInCenter');
// Route::get('/getAllPatientInfoRequests/{centerId}', 'UserController@getAllPatientInfoRequests');






$permissions = ['view-dashboard', 'edit-dashboard', 'view-reports'];



// Route::middleware(CheckRole::class . ':' . implode(',', $roles))->group(function () {
   
 
// });






    // Route::middleware([

    //     CheckRole::class . ':' . implode(',', $roles),
    //     SecondMiddleware::class . ':' . implode(',', $permissions)
    // ])->group(function () {
     
    // });



