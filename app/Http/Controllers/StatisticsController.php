<?php

 namespace App\Http\Controllers;
    
    use App\Contracts\Services\UserService\StatisticsServiceInterface;
    
    use Illuminate\Http\Request;
    use App\Models\Prescription;
    use App\Models\User;
    use App\Models\GlobalRequest;
    use App\Models\PatientTransferRequest;
    use App\Models\RequestModifyAppointment;
    use App\Models\Requests;
   
class StatisticsController extends Controller {
    protected $service;

    public function __construct(StatisticsServiceInterface $service) {
        $this->service = $service;
    }
    public function getPieCharts($month = null, $year = null)
    {
        return response()->json(['pieChart' => $this->service->getPieCharts($month, $year)], 200);
    }


    public function causeRenalFailure()
    {
        return response()->json(['causeRenalFailure' => $this->service->causeRenalFailure()], 200);
    }


    public function getCenterStatistics()
    {
        return response()->json([$this->service->getCenterStatistics()], 200);
    }


    
}