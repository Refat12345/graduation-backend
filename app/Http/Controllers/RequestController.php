<?php

namespace App\Http\Controllers;

use App\Models\GlobalRequest;
use Illuminate\Http\Request;   
use App\Contracts\Services\UserService\RequestsServiceInterface;   




    class RequestController extends Controller
{
    protected $requestsService;

    public function __construct(RequestsServiceInterface $requestsService)
    {
        $this->requestsService = $requestsService;
    }



    public function createPatientTransferRequest(Request $request)
    {
       try{
        $result = $this->requestsService->addPatientTransferRequest($request->all());

        if ($result instanceof PatientTransferRequest) {
            return response()->json([$result], 200);
        }

        return response()->json([$result], 400);
    } catch (\Exception $e) {
           
        return response()->json(['error' => $e->getMessage()], 400);
    }
    }
 






}
