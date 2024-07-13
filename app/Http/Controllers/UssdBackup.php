<?php

namespace App\Http\Controllers;


use App\Repositories\ServersProductIDRepository;
use App\Repositories\SimServerRepository;
use App\Repositories\AirtimeRepository;
use App\Repositories\HistoryRepository;
use App\Repositories\WalletRepository;
use App\Repositories\DataRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ReferralBonus;
use Illuminate\Http\Request;
use App\Models\OtherProduct;
use App\Models\SimServer;
use App\Models\Referral;
use App\Models\History;
use App\Models\User;

use Illuminate\Support\Facades\Log;

session_start();


class UssdServiceController extends Controller
{
    //

    private $ServersProductIDRepository;
    private $SimServerRepository;
    private $HistoryRepository;
    private $AirtimeRepository;
    private $WalletRepository;
    private $UserRepository;
    private $DataRepository;
    private $Name;
    private $UserId;
    private $Balance;
    private $UserName;
    private $CreatePin;


    public function __construct(HistoryRepository $HistoryRepository, UserRepository $UserRepository, DataRepository $DataRepository, WalletRepository $WalletRepository, AirtimeRepository $AirtimeRepository, SimServerRepository $SimServerRepository, ServersProductIDRepository $ServersProductIDRepository, Request $resquest )
    {

        $this->ServersProductIDRepository = $ServersProductIDRepository;
        $this->SimServerRepository = $SimServerRepository;
        $this->AirtimeRepository = $AirtimeRepository;
        $this->HistoryRepository = $HistoryRepository;
        $this->WalletRepository = $WalletRepository;
        $this->UserRepository = $UserRepository;
        $this->DataRepository = $DataRepository;

    }


    public function UssdService(Request $request)
    {
        // Public service function starts here ..................................................................
        date_default_timezone_set("Africa/Lagos");
        $response      =   "";
        $operator      =   "";
        $sessioId      =   $request->sessionId;
        $serviceCode   =   $request->serviceCode;
        $text          =   $request->text;
        $input         =   $request->input;
        $requestID     =   date('YmdHi').rand(99, 9999999);
        $customer_reference = rand(99, 999999999999);

        // Get User Details From Database ------------------------------------------------->
        $mobile             =   $request->phoneNumber;
        $phoneNumber        =   str_replace("+234", "0", $mobile);
        $User               =   User::join('wallets', 'wallets.user_id', 'users.id')->where('users.mobile', $phoneNumber)->first();

        if (empty($User)) {

            $response    =   "END The user with $phoneNumber does not exist on our app, try laters !!! ";
            return response($response)->header('Content-Type', 'text/plain');
        }

        $this->Name         =   $User->name;
        $this->UserId       =   $User->id;
        $this->Username     =   $User->username;
        $this->Balance      =   $User->balance;
        $this->CreatePin    =   $User->create_pin;

        // -------------------------------------------------------------------------------->

        // Validating response =============================================================>
        $response = "";
        $inputArray = explode('*', $text);

        // Determine the number of elements in the array
        $arrayLength = count($inputArray);



        // Main Menu
        if ( empty($text) || $text == "") {

            $response     =   $this->startMenu();

        }

        // Account Balance
        elseif( $text == "1")
        {
            $response    =   "END Dear $this->Name, Your Account Balance Is : ₦ $this->Balance ";
        }

        // Buy Data
        elseif( $text == "2")
        {
            $response    =   $this->BuyData();
        }
        // --------------------------------------- INTERNET DATA PROCESSING ----------------------------------------------------------------->
        // .................................................................................................
        // ..............................................................................
        // ...................................................


            elseif ($arrayLength === 2 && $inputArray[0] === '2' && $inputArray[1] === '1') {

                $response = $this->MtnData();

            } elseif ($arrayLength === 2 && $inputArray[0] === '2' && $inputArray[1] === '2') {

                $response = $this->AirtelData();

            } elseif ($arrayLength === 2 && $inputArray[0] === '2' && $inputArray[1] === '3') {

                $response = $this->nMobileData();

            } elseif ($arrayLength === 2 && $inputArray[0] === '2' && $inputArray[1] === '4') {

                $response = $this->GloData();

            } elseif ($arrayLength === 3 && $inputArray[0] === '2') {

                $response   =   $this->enterPhonenumber();

            } elseif ($arrayLength === 4 && $inputArray[0] === '2') {

                $response   =   $this->inputPIN();

            }
            // Process MTN Data
            elseif ($arrayLength === 5 && $inputArray[0] === '2' && $inputArray[1] === '1') {

                $activeServer   =   $this->SwitchServer(1);
                $networks       =   $inputArray[1];
                $planSelect     =   (int)$inputArray[2];
                $phoneNo        =   $inputArray[3];
                $pin            =   $inputArray[4];
                $prices         =   [250, 500, 750, 1250, 2500];
                $pVals          =   [1, 2, 3, 5, 10];
                $pCodes         =   ['950', '215', '516', '439', '14525'];
                $planCode       =   $pCodes[$planSelect - 1];
                $planVal        =   $pVals[$planSelect - 1];
                $planPrice      =   $prices[$planSelect - 1];
                $balance        =   $this->walletBalance($this->UserId);


                if( Hash::check($pin, $this->CreatePin )){

                    if( $balance < $planPrice )
                    {

                        $response   =   "END Insufficient fund, try later ";

                    } else {

                        if( $activeServer == 'Smeplug' )
                        {

                            $planIDs        =   ['2', '3', '4', '5', '7'];
                            $planId         =   $planIDs[$planSelect-1];
                            $createData     =   $this->SmeplugData(1, 1, $phoneNo);

                            if( $createData && $createData->status == true){

                                Log::debug(['Error Received' => $createData]);
                                // Update wallet .......................................................................................................

                                $new_bal_process = $balance - $planPrice;
                                $walletDetails = [ 'balance' => $new_bal_process, 'updated_at'=> NOW() ];
                                $this->WalletRepository->updateWallet($uid, $walletDetails);

                                // .....................................................................................................................

                                $savedHistory    =   $this->saveData($this->UserId, 'Smeplug', 'mtn', $planVal, $planCode, $createData->data->reference, $phoneNo, $customer_reference, $planPrice, $createData->data->msg);

                                if($savedHistory) {

                                    $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";

                                } else {

                                    $response   =   "END Operation failed, try later";
                                }

                            } else {
                                Log::debug(['Error Received' => $createData]);

                                $response   =   "END Error occured, try later";
                            }
                        }
                        else
                        {

                            $planIDs        =   ['data_share_1gb:device:USSD_SHARE_FULL', 'data_share_2gb:device:USSD_SHARE_FULL', 'data_share_3gb:device:USSD_SHARE_FULL', 'data_share_5gb:device:USSD_SHARE_FULL', 'data_share_10gb:device:USSD_SHARE_FULL'];
                            $planId         =   $planIDs[$planSelect-1];
                            $reqstID        =   date('YmdHi').rand(99, 9999999);
                            $getCredentials =   SimServer::where('sim_server', 'Smeplug')->first();
                            $createData     =   $this->SimserverData(1, $getCredentials->access_token, $planId, $phoneNo, $reqstID);

                            if( $createData ){

                                // Update wallet .......................................................................................................

                                $new_bal_process = $balance - $planPrice;
                                $walletDetails = [ 'balance' => $new_bal_process, 'updated_at'=> NOW() ];
                                $this->WalletRepository->updateWallet($uid, $walletDetails);

                                // .....................................................................................................................

                                Log::debug(['Success Data' => $createData]);

                                $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";

                            } else {
                                Log::debug(['Error Received' => $createData]);

                                $response   =   "END Error occured, try later";
                            }
                        }
                    }

                } else {

                    $response   =   "END Invalid PIN";
                }

            }

            // Process AIRTEL Data
            elseif ($arrayLength === 5 && $inputArray[0] === '2' && $inputArray[1] === '2') {

                $activeServer   =   $this->SwitchServer(2);
                $networks       =   $inputArray[1];
                $planSelect     =   (int)$inputArray[2];
                $phoneNo        =   $inputArray[3];
                $pin            =   $inputArray[4];
                $prices         =   [350, 700, 1750, 3400, 6800];
                $pVals          =   [1, 2, 5, 10, 20];
                $pCodes         =   ['293', '666', '454', '119', '481'];
                $planCode       =   $pCodes[$planSelect - 1];
                $planVal        =   $pVals[$planSelect - 1];
                $planPrice      =   $prices[$planSelect - 1];
                $balance        =   $this->walletBalance($this->UserId);


                if( Hash::check($pin, $this->CreatePin )){

                    if( $balance < $planPrice )
                    {

                        $response   =   "END Insufficient fund, try later ";

                    } else {

                        if( $activeServer == 'Smeplug' )
                        {

                            $planIDs        =   ['AIR1000', 'AIR2000', 'AIR2500', '5', '7'];
                            $planId         =   $planIDs[$planSelect-1];
                            $createData     =   $this->SmeplugData(2, $planId, $phoneNo);

                            if( $createData && $createData->status == true){

                                Log::debug(['Error Received' => $createData]);
                                // Update wallet .......................................................................................................

                                $new_bal_process = $balance - $planPrice;
                                $walletDetails = [ 'balance' => $new_bal_process, 'updated_at'=> NOW() ];
                                $this->WalletRepository->updateWallet($uid, $walletDetails);

                                // .....................................................................................................................

                                $savedHistory    =   $this->saveData($this->UserId, 'Smeplug', 'airtel', $planVal, $planCode, $createData->data->reference, $phoneNo, $customer_reference, $planPrice, $createData->data->msg);

                                if($savedHistory) {

                                    $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";

                                } else {

                                    $response   =   "END Operation failed, try later";
                                }

                            } else {
                                Log::debug(['Error Received' => $createData]);

                                $response   =   "END Error occured, try later";
                            }
                        }
                        else
                        {

                            $planIDs        =   ['airtel_1gb_30days:cg:nil', 'airtel_2gb_30days:cg:nil', 'airtel_5gb_30days:cg:nil', 'airtel_10gb_30days:cg:nil', 'airtel_20gb_30days:cg:nil'];
                            $planId         =   $planIDs[$planSelect-1];
                            $reqstID        =   date('YmdHi').rand(99, 9999999);
                            $getCredentials =   SimServer::where('sim_server', 'Smeplug')->first();
                            $createData     =   $this->SimserverData(3, $getCredentials->access_token, $planId, $phoneNo, $reqstID);

                            if( $createData ){

                                // Update wallet .......................................................................................................

                                $new_bal_process = $balance - $planPrice;
                                $walletDetails = [ 'balance' => $new_bal_process, 'updated_at'=> NOW() ];
                                $this->WalletRepository->updateWallet($uid, $walletDetails);

                                // .....................................................................................................................

                                Log::debug(['Success Data' => $createData]);

                                $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";

                            } else {
                                Log::debug(['Error Received' => $createData]);

                                $response   =   "END Error occured, try later";
                            }
                        }
                    }

                } else {

                    $response   =   "END Invalid PIN";
                }

            }

            // Process 9MOBILE Data
            elseif ($arrayLength === 5 && $inputArray[0] === '2' && $inputArray[1] === '3') {

                $networks       =   $inputArray[1];
                $planSelect     =   (int)$inputArray[2];
                $phoneNo        =   $inputArray[3];
                $pin            =   $inputArray[4];
                $prices         =   [900, 1080, 1350, 1800, 3600];
                $pVals          =   [1.5, 2, 3, 4.5, 11];
                $pCodes         =   ['167', '479', '587', '731', '109'];
                $planCode       =   $pCodes[$planSelect - 1];
                $planVal        =   $pVals[$planSelect - 1];
                $planPrice      =   $prices[$planSelect - 1];
                $balance        =   $this->walletBalance($this->UserId);


                if( Hash::check($pin, $this->CreatePin )){

                    if( $balance < $planPrice )
                    {

                        $response   =   "END Insufficient fund, try later ";

                    } else {

                        $planIDs        =   ['9MOB1000', '9MOB2000', '9MOB3000', '9MOB34500', '9MOB4000'];
                        $planId         =   $planIDs[$planSelect-1];
                        $createData     =   $this->SmeplugData(3, $planId, $phoneNo);

                        if( $createData && $createData->status == true){

                            Log::debug(['Error Received' => $createData]);
                            // Update wallet .......................................................................................................

                            $new_bal_process = $balance - $planPrice;
                            $walletDetails = [ 'balance' => $new_bal_process, 'updated_at'=> NOW() ];
                            $this->WalletRepository->updateWallet($uid, $walletDetails);

                            // .....................................................................................................................

                            $savedHistory    =   $this->saveData($this->UserId, 'Smeplug', 'airtel', $planVal, $planCode, $createData->data->reference, $phoneNo, $customer_reference, $planPrice, $createData->data->msg);

                            if($savedHistory) {

                                $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";

                            } else {

                                $response   =   "END Operation failed, try later";
                            }

                        } else {
                            Log::debug(['Error Received' => $createData]);

                            $response   =   "END Error occured, try later";
                        }

                    }

                } else {

                    $response   =   "END Invalid PIN";
                }

            }

            // Process GLO Data
            elseif ($arrayLength === 5 && $inputArray[0] === '2' && $inputArray[1] === '4') {

                $networks       =   $inputArray[1];
                $planSelect     =   (int)$inputArray[2];
                $phoneNo        =   $inputArray[3];
                $pin            =   $inputArray[4];
                $prices         =   [475, 950, 1425, 1900, 2375];
                $pVals          =   [1, 2.3, 3.7, 5.2, 7];
                $pCodes         =   ['ussd_glo_1gb', 'ussd_glo_2.3gb', 'ussd_glo_3.7gb', 'ussd_glo_5.2gb', 'ussd_glo_7gb'];
                $planCode       =   $pCodes[$planSelect - 1];
                $planVal        =   $pVals[$planSelect - 1];
                $planPrice      =   $prices[$planSelect - 1];
                $balance        =   $this->walletBalance($this->UserId);


                if( Hash::check($pin, $this->CreatePin )){

                    if( $balance < $planPrice )
                    {

                        $response   =   "END Insufficient fund, try later ";

                    } else {

                        $planIDs        =   ['49', '39', '52', '48', '44'];
                        $planId         =   $planIDs[$planSelect-1];
                        $createData     =   $this->SmeplugData(4, $planId, $phoneNo);

                        if( $createData && $createData->status == true){

                            Log::debug(['Error Received' => $createData]);
                            // Update wallet .......................................................................................................

                            $new_bal_process = $balance - $planPrice;
                            $walletDetails = [ 'balance' => $new_bal_process, 'updated_at'=> NOW() ];
                            $this->WalletRepository->updateWallet($uid, $walletDetails);

                            // .....................................................................................................................

                            $savedHistory    =   $this->saveData($this->UserId, 'Smeplug', 'airtel', $planVal, $planCode, $createData->data->reference, $phoneNo, $customer_reference, $planPrice, $createData->data->msg);

                            if($savedHistory) {

                                $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";

                            } else {

                                $response   =   "END Operation failed, try later";
                            }

                        } else {
                            Log::debug(['Error Received' => $createData]);

                            $response   =   "END Error occured, try later";
                        }

                    }

                } else {

                    $response   =   "END Invalid PIN";
                }

            }

        // ................................................
        // ...............................................................
        // ........................................................................................

        // END INTERNET DATA PROCESS -------------------------------------------------------------------------------------------------------->

        // Buy Airtime Sub-Menu
        elseif ($text == "3") {
            $response   = $this->BuyAirtime();
        }

        // ----------------------------------------------- AIRTIME PROCESSING FOR ----------------------------------------------------------->
        // .......................................................................
        // ....................................................
        // ...................................
        // ................



            elseif ($arrayLength === 2 && $inputArray[0] === '3' && $inputArray[1] === '1') {
                // Enter Amount for others
                $response = $this->enterAmount();

            } elseif ($arrayLength === 3 && $inputArray[0] === '3' && $inputArray[1] === '1') {
                // Save Phone Number in Session and Request Network Selection

                $response = $this->savePhoneNumber($phoneNumber);

            } elseif ($arrayLength === 4 && $inputArray[0] === '3' && $inputArray[1] === '1') {

                $response   =   $this->inputPIN() ;

            } elseif ($arrayLength === 5 && $inputArray[0] === '3' && $inputArray[1] === '1') {
                // Process Airtime Purchase
                $networkSelection   =   (int)$inputArray[3];
                $newamount          =   (int)$inputArray[2] ;
                $mobileNumber       =   $phoneNumber ;
                $pin                =   $inputArray[4];
                $balance            =   $this->walletBalance($this->UserId);

                // Check if the "amount" and "phone_number" are set in the session
                if ($newamount === null || $mobileNumber === null) {

                    $response = "END Invalid input. Please start the airtime purchase process again.";

                } else {

                    if( Hash::check($pin, $this->CreatePin )){

                        if( $balance < $newamount ) {

                            $response   =   "END Insufficient fund, try later ";

                        } else {
                            $networkSelection = (int)$inputArray[3];

                            // Reset the session data after processing
                            unset($_SESSION['amount']);
                            unset($_SESSION['phone_number']);

                            // Convert network selection to the actual network name
                            $networks = ['mtn', 'glo', 'airtel', 'etisalat'];
                            $network = isset($networks[$networkSelection - 1]) ? $networks[$networkSelection - 1] : 'Unknown Network';

                            // Get Airtime Percentage ------------------------------------------------------------------------->
                            $airtimPerc     =   OtherProduct::where('name', 'airtime')->first();
                            $percVal        =   $airtimPerc->variation_amount / 100;
                            $amount2Purchase=   $newamount - ($percVal * $newamount);
                            // Get Wallet Details ----------------------------------------------------------------------------->
                            $req_Account_process = $this->WalletRepository->getWalletBalance($this->UserId);
                            $req_bal_process = $req_Account_process->balance;

                            if($req_bal_process < $amount2Purchase){
                                $response   =   'END Insufficient fund';
                            }else{

                                $response   =   $this->VTPassAirtimePurchase($requestID, $network, $newamount, $mobileNumber, $amount2Purchase, $customer_reference);
                                $response   =   "END Thank you for purchasing $newamount airtime for $mobileNumber on $network network.";
                            }
                        }
                    } else {

                        $response   =   "END Invalid PIN";

                    }

                }
            }


            // ----------------------------------------------- AIRTIME PROCESSING FOR OTHERS ---------------------------------------->

            elseif ($arrayLength === 2 && $inputArray[0] === '3' && $inputArray[1] === '2') {
                // Enter Amount for self
                $response = $this->enterAmount();

            } elseif ($arrayLength === 3 && $inputArray[0] === '3' && $inputArray[1] === '2') {
                // Save Amount in Session
                $response = $this->saveAmount((int)$inputArray[2]);

            } elseif ($arrayLength === 4 && $inputArray[0] === '3' && $inputArray[1] === '2') {
                // Save Phone Number in Session and Request Network Selection

                $response = $this->savePhoneNumber($inputArray[3]);

            } elseif ($arrayLength === 5 && $inputArray[0] === '3' && $inputArray[1] === '2') {

                $response   =   $this->inputPIN();

            } elseif ($arrayLength === 6 && $inputArray[0] === '3' && $inputArray[1] === '2') {
                // Process Airtime Purchase
                $networkSelection   =   (int)$inputArray[4];
                $newamount          =   (int)$inputArray[2] ;
                $pin                =   $inputArray[5];
                $mobileNumber       =   ($inputArray[3]) ;
                $balance            =   $this->walletBalance($this->UserId);

                // Check if the "amount" and "phone_number" are set in the session
                if ($newamount === null || $mobileNumber === null) {

                    $response = "END Invalid input. Please start the airtime purchase process again.";

                } else {

                    if( Hash::check($pin, $this->CreatePin )){

                        if( $balance < $newamount ) {

                            $response   =   "END Insufficient fund, try later ";

                        } else {

                            // Reset the session data after processing
                            unset($_SESSION['amount']);
                            unset($_SESSION['phone_number']);

                            // Convert network selection to the actual network name
                            $networks = ['mtn', 'glo', 'airtel', 'etisalat'];
                            $network = isset($networks[$networkSelection - 1]) ? $networks[$networkSelection - 1] : 'Unknown Network';

                            // Get Airtime Percentage ------------------------------------------------------------------------->
                            $airtimPerc     =   OtherProduct::where('name', 'airtime')->first();
                            $percVal        =   $airtimPerc->variation_amount / 100;
                            $amount2Purchase=   $newamount - ($percVal * $newamount);
                            // Get Wallet Details ----------------------------------------------------------------------------->
                            $req_Account_process = $this->WalletRepository->getWalletBalance($this->UserId);
                            $req_bal_process = $req_Account_process->balance;

                            if($req_bal_process < $amount2Purchase){
                                $response   =   'END Insufficient fund';
                            }else{

                                $response   =   $this->VTPassAirtimePurchase($requestID, $network, $newamount, $mobileNumber, $amount2Purchase, $customer_reference);
                                $response   =   "END Thank you for purchasing $newamount airtime for $mobileNumber on $network network.";
                            }
                        }
                    } else {
                        $response   =   "END Invalid PIN";
                    }
                }
            }


            // ....................
            // ...........................
            // ..................................
            // ............................................
            // ................................................................
            // ..............................................................................

            // ---------------------------------------------- AIRTIME PROCESSING ------------------------------------------------------->

        // Buy Cable Plan
        elseif( $text == "4")
        {
            $response    =   "END Dear $this->Name, This Service Is Coming Soon ... ";
        }

        // Pay Your Bill
        elseif( $text == "5")
        {
            $response    =   "END Dear $this->Name, This Service Is Coming Soon ... ";
        }

        // Register An Account
        elseif( $text == "6")
        {
            $response    =   "END Dear $this->Name, This Service Is Coming Soon ... ";
        }

        // History Menu
        elseif( $text == "7")
        {
            $response   =   $this->LatestHistory();
        }


        // End USSD Session
        else {
            $response = "END Invalid input. Please try again.";
        }

        // Send the response to the USSD gateway
        header('Content-type: text/plain');
        echo $response;
    // Public service function ends here ....................................................................
    }

    // Function Main Menu
    private function startMenu()
    {
        $response     =   "CON Dear $this->Name, Welcome to Kettlesub. What Would You Like To Do ? \n";
        $response    .=   "1. Account Balance \n";
        $response    .=   "2. Buy Data \n";
        $response    .=   "3. Buy Airtime \n";
        $response    .=   "4. Buy Cable \n";
        $response    .=   "5. Pay Your Bill \n";
        $response    .=   "6. Register New Account \n";
        $response    .=   "7. Latest Transaction \n";

        return $response;
    }

    // Function Airtime SubMenu
    private function BuyAirtime()
    {
        // Buy Airtime submenu
        $response = "CON Buy Airtime:\n";
        $response .= "1. Buy for Self\n";
        $response .= "2. Buy for Others";
        return $response;
    }

    // Function Airtime SubMenu
    private function BuyData()
    {
        // Buy Airtime submenu
        $response = "CON Purchase Data:\n";
        $response .= "1. MTN DATA \n";
        $response .= "2. Airtel Data \n";
        $response .= "3. 9Mobile Data \n";
        $response .= "4. Glo Data";
        return $response;
    }

    // Buy MTN Data SubMenu
    private function MtnData()
    {
        // Buy MTN Data submenu
        $response = "CON MTN Data:\n";
        $response .= "1. 1Gb/₦250/30days \n";
        $response .= "2. 2Gb/₦500/30days \n";
        $response .= "3. 3Gb/₦750/30days \n";
        $response .= "4. 5Gb/₦1,250/30days \n";
        $response .= "5. 10Gb/₦2500/30days";
        return $response;
    }

    // Buy Airtel Data SubMenu
    private function AirtelData()
    {
        // Buy Airtel Data submenu
        $response = "CON Airtel Data:\n";
        $response .= "1. 1Gb/₦350/30days \n";
        $response .= "2. 2Gb/₦700/30days \n";
        $response .= "3. 5Gb/₦1,750/30days \n";
        $response .= "4. 10Gb/₦3,400/30days";
        return $response;
    }

    // Buy Airtel Data SubMenu
    private function nMobileData()
    {
        // Buy 9Mobile Data submenu
        $response = "CON 9Mobile Data:\n";
        $response .= "1. 1.5Gb/₦900/30days \n";
        $response .= "2. 2Gb/₦1080/30days \n";
        $response .= "3. 3Gb/₦1,350/30days \n";
        $response .= "4. 4.5Gb/₦1,800/30days \n";
        $response .= "5. 11Gb/₦3600/30days";
        return $response;
    }

    // Buy Glo Data SubMenu
    private function GloData()
    {
        // Buy Glo Data submenu
        $response = "CON Glo Data:\n";
        $response .= "1. 1Gb/₦475/14days \n";
        $response .= "2. 2.3Gb/₦950/30days \n";
        $response .= "3. 3.7Gb/₦1,425/30days \n";
        $response .= "4. 5.2Gb/₦1,900/30days \n";
        $response .= "5. 7Gb/₦2375/30days";
        // $response .= "1. 1.3Gb/₦475/14days \n";
        // $response .= "2. 3.9Gb/₦950/30days \n";
        // $response .= "3. 4.1Gb/₦1,425/30days \n";
        // $response .= "4. 5.8Gb/₦1,900/30days \n";
        // $response .= "5. 7.7Gb/₦2375/30days";
        return $response;
    }

    // Mobile Data Server Switching
    private function SwitchServer($networkId)
    {
        $simServer = $this->SimServerRepository->getActiveSimServers($networkId);
        return $simServer->sim_server;
    }

    // Create Smeplug Data
    private function SmeplugData($networkId, $smePlugPlanID, $phoneNumber)
    {
        $DataDetails = [
            'network_id'       => $networkId,
            'plan_id'          => $smePlugPlanID ,
            'phone'            => $phoneNumber,
        ];

        $createSmeplugData = json_decode($this->DataRepository->createSmePlugData($DataDetails));

        return $createSmeplugData;
    }

        // Create Smeplug Data
    private function SimserverData($networkId, $apiKey, $simServersPlanID, $phoneNumber, $requestID)
    {
        $DataDetails = [
            'process'           => 'buy',
            'api_key'           => $apiKey, //$getCredentials->access_token,
            'product_code'      => $simServersPlanID,
            'amount'            => '50',
            'recipient'         => $phoneNumber,
            'callback'          => URL('/').'/api/verifySimserverWebhook',
            'user_reference'    => $requestID

        ];

        $createSmeplugData = json_decode($this->DataRepository->createSimserversData($DataDetails));

        return $createSmeplugData;
    }

    // Store Data in History Table
    private function saveData($uid, $apiMd, $nwt, $planValue, $product_code, $ref, $phoneNumber, $customer_ref, $data_price, $msg)
    {
        $commission = 0.0;
        $HistoryDetails = [
            'user_id'               =>  $uid,
            'purchase'              =>  'Data',
            'api_mode'              =>  $apiMd,
            'network'               =>  $nwt,
            'plan'                  =>  $planValue,
            'product_code'          =>  $product_code,
            'transfer_ref'          =>  $ref,
            'phone_number'          =>  $phoneNumber,
            'distribe_ref'          =>  $customer_ref ,
            'selling_price'         =>  $data_price,
            'send_value'            =>  $data_price,
            'description'           =>  $msg,
            'commission_applied'    =>  $commission,
            'processing_state'      =>  'processed'
        ];

        $output =   $this->HistoryRepository->createHistory($HistoryDetails);

        return $output;
    }


    // Enter Amount for self
    private function enterAmount()
    {
        return "CON Enter Amount:";
    }

    // Save Amount in Session
    private function saveAmount($amount)
    {
        session(['amount' => (int)$amount]);
        return "CON Enter Phone Number:";
    }

    // Enter phone number
    private function enterPhonenumber()
    {
        return "CON Enter Phone Number:";
    }

    // Save Phone Number in Session and Request Network Selection
    private function savePhoneNumber($phoneNumber)
    {
        session(['phone_number' => $phoneNumber]);
        $response = "CON Select Network:\n";
        $response .= "1. MTN\n";
        $response .= "2. Glo\n";
        $response .= "3. Airtel \n";
        $response .= "4. Etisalat";
        return $response;
    }

    // Process Airtime Purchase
    private function processPurchase($networkSelection)
    {
        $amount = session('amount');
        $phoneNumber = session('phone_number');

        // Check if the "amount" and "phone_number" are set in the session
        if (!$amount || !$phoneNumber) {
            return $this->endSession("Invalid input. Please start the airtime purchase process again.");
        }

        // Process the airtime purchase using the collected data
        // For example, make an API call to a service provider to purchase the airtime
        // Replace this with the actual code to purchase the airtime

        // Reset the session data after processing
        session()->forget('amount');
        session()->forget('phone_number');

        // Convert network selection to the actual network name
        $networks = ['MTN', 'Glo', 'Airtel'];
        $network = isset($networks[$networkSelection - 1]) ? $networks[$networkSelection - 1] : 'Unknown Network';

        return $this->endSession("Thank you for purchasing $amount airtime for $phoneNumber on $network network.");
    }

    private function VTPassAirtimePurchase($reqID, $ntw, $amt, $phn, $pamt, $cstReff)
    {
        $DataDetails = [
            'request_id'        => $reqID,
            'serviceID'         => $ntw,
            'amount'            => $amt,
            'phone'             => $phn,
        ];
        $createNigAirt = json_decode($this->AirtimeRepository->createVtpassAirtime($DataDetails));
        if($createNigAirt){

            $AirtimeTransDetail = [
                'user_id'           => $this->UserId ,
                'purchase'          => 'airtime',
                'network'           =>  $ntw,
                'api_mode'          => 'USSD',
                'plan'              => $createNigAirt->content->transactions->product_name,
                'product_code'      => 'AIRTIME_VTU_'.$cstReff,
                'transfer_ref'      => $createNigAirt->requestId,
                'phone_number'      =>  $createNigAirt->content->transactions->unique_element,
                'distribe_ref'      => $createNigAirt->content->transactions->transactionId,
                'selling_price'     => $pamt,
                'description'       => $createNigAirt->response_description,
                'send_value'        => $amt,
                'commission_applied'=> 0,
                'processing_state'  => $createNigAirt->content->transactions->status,
                'updated_at'        => NOW(),
            ];

            $this->HistoryRepository->createHistory($AirtimeTransDetail);

             // Getting Referer Details ..........................................
            $myReferral_process             =   Referral::where('referree_id', $this->UserId)->first();
            if( $myReferral_process )
            {
                $my_refferal                =   $myReferral_process->referral;
                $referral_Account_process   =   ReferralBonus::where('user_id', $my_refferal);
                $referral_bal               =   $referral_Account_process->amount;
                $perc_bonus                 =   (1/100) * $amt;
                $referral_bonus             =   $perc_bonus + $referral_bal;

                ReferralBonus::where('user_id', $my_refferal)->update([ 'amount'=>$referral_bonus , 'balance_before'=>$referral_bal, 'updated_at'=> NOW() ]);
                $response = "END Thank you for purchasing $amt airtime for $phn on $ntw network.";

            }
            // ............................................................................//
            else
            {
                $response = "END Thank you for purchasing $amt airtime for $phn on $ntw network.";
            }
        }
        else{

            $response   =   "Error Occured, try later";
        }

        return $response;
    }



    // Function Latest History
    private function LatestHistory()
    {

        $history    =   History::where("user_id", $this->UserId)->latest()->first(); //->OrderBy('id', 'DESC')->limit(1);

        if($history)
        {
            $transPhn   =   $history->phone_number;
            $transDvc   =   $history->deviceNo;
            $transOpe   =   $history->network;
            $transPrc   =   $history->selling_price;
            $transPhr   =   $history->purchase;
            $response   =   "END Your Latest Transaction Is : $transPhr | ₦ $transPrc | $transOpe | $transDvc $transPhn";
        }
        else
        {
            $response   =   "END No Transaction Found !!!";
        }

        return $response;

    }

    // get Wallet Balance
    private function walletBalance($uid)
    {
        // Get Wallet Details ----------------------------------------------------------------------------->
        $req_Account_process = $this->WalletRepository->getWalletBalance($uid);
        $req_bal_process = $req_Account_process->balance;

        return $req_bal_process;
    }

    // CREATE PIN
    private function inputPIN()
    {
        $request    =   "CON Enter your 4 digit PIN";
        return $request;
    }


}
