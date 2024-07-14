<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\ReferralBonus;
use Illuminate\Http\Request;
use App\Models\VtuappCustomuser;
use App\services\USSDService;
use Illuminate\Support\Facades\Log;

session_start();


class UssdController extends Controller
{

    public function __construct(protected USSDService $ussdService)
    {
        $this->ussdService = $ussdService;
    }
    public function handleServices(Request $request)
    {
        date_default_timezone_set("Africa/Lagos");
        $sessionId  = $request->sessionId;
        $serviceCode= $request->serviceCode;
        $text       = $request->text;
        $input      = $request->input;
        $requestID  = date('YmdHi') . rand(99, 9999999);
        $mobile     = '07035743427';//$request->phoneNumber;
        $phoneNumber= str_replace("+234", "0", $mobile);
        $customerReference = rand(99, 999999999999);

        $response = $this->ussdService->handleServices($sessionId, $phoneNumber, $serviceCode, $text, $input, $requestID, $customerReference);

        header('Content-type: text/plain');
        echo $response;
    }


}
