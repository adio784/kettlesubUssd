<?php

namespace App\services;

use App\Models\VtuappAirtimetopup;
use App\Models\VtuappBillpayment;
use App\Models\VtuappCablesub;
use App\Models\VtuappCustomuser;
use App\Models\VtuappData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class USSDService
{
    private $SimserAccesstoken;
    private $customerReference;
    private $simServerServices;
    private $dataServices;
    private $historyServices;
    private $userServices;
    private $airtimeServices;
    private $Name;
    private $UserId;
    private $Balance;
    private $UserName;
    private $CreatePin;

    public function __construct(
        HistoryServices $historyServices,
        SimServerServices  $simServerServices,
        DataServices $dataServices,
        UserServices $userServices,
        AirtimeServices $airtimeServices
    ) {

        $this->simServerServices = $simServerServices;
        $this->historyServices = $historyServices;
        $this->dataServices = $dataServices;
        $this->userServices = $userServices;
        $this->airtimeServices = $airtimeServices;
        $this->customerReference = rand(99, 999999999999);
        $this->SimserAccesstoken = "y7vyy268rjxxxxxxw6bsn4slfnolxetcxzkg5m6id";
    }

    // Public service function starts here ....................................................................
    public function handleServices($sessionId, $phoneNumber, $serviceCode, $text, $input, $requestID, $customer_reference)
    {
        // Public service function starts here ..................................................................
        date_default_timezone_set("Africa/Lagos");
        $response      =   "";
        $operator      =   "";

        // Get User Details From Database ------------------------------------------------->
        $User           =   VtuappCustomuser::where('Phone', $phoneNumber)->first();

        if (empty($User)) {

            $response    =   "END The user with $phoneNumber does not exist on our app, try laters !!! ";
            return response($response)->header('Content-Type', 'text/plain');
        }

        $this->Name         =   $User->last_name . ' ' . $User->first_name;
        $this->UserId       =   $User->id;
        $this->UserName     =   $User->username;
        $this->Balance      =   $User->Account_Balance;
        $this->CreatePin    =   $User->pin;

        // -------------------------------------------------------------------------------->

        // Validating response =============================================================>
        $response = "";
        $inputArray = explode('*', $text);

        // Determine the number of elements in the array
        $arrayLength = count($inputArray);



        // Main Menu
        if (empty($text) || $text == "") {

            $response     =   $this->startMenu();
        }

        // Account Balance
        elseif ($text == "1") {
            $response    =   "END Dear $this->Name, Your Account Balance Is : ₦ $this->Balance ";
        }

        // Buy Data
        elseif ($text == "2") {
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

            $activeServer   =   $this->SwitchServer('MTN');
            $networks       =   $inputArray[1];
            $planSelect     =   (int)$inputArray[2];
            $phoneNo        =   $inputArray[3];
            $pin            =   $inputArray[4];
            $prices         =   [285, 570, 855, 1425, 2850];
            $pVals          =   [1, 2, 3, 5, 10];
            $planVal        =   $pVals[$planSelect - 1];
            $planPrice      =   $prices[$planSelect - 1];
            $balance        =   $this->walletBalance($this->UserId);


            if ($this->userServices->checkPin($this->UserId, $pin) == true) {

                if ($balance < $planPrice) {

                    $response   =   "END Insufficient fund, try later ";
                } else {

                    if ($activeServer == 'SMEPLUG') {

                        $planIDs        =   ['2', '3', '4', '5', '109'];
                        $planId         =   $planIDs[$planSelect - 1];
                        $createData     =   $this->SmeplugData(1, $planId, $phoneNo);

                        if ($createData && $createData->status == true) {
                            Log::debug(['Data Received' => $createData]);
                            // Update wallet .......................................................................................................

                            $new_bal_process = $balance - $planPrice;
                            $walletDetails = ['Account_Balance' => $new_bal_process];
                            $this->userServices->update($this->UserId, $walletDetails);

                            // .....................................................................................................................
                            $planCodes      =   ['7', '8', '44', '11', '207'];
                            $planCode       =   $planCodes[$planSelect - 1];
                            $savedHistory   =   $this->saveData(1, $planCode, $phoneNo, $createData->data->reference, $balance, $new_bal_process, $planPrice);


                            if ($savedHistory) {

                                $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";
                            } else {

                                Log::debug(['Failed to save data' => $createData]);
                                $response   =   "END Operation failed, try later";
                            }
                        } else {

                            Log::debug(['Error Received' => $createData]);
                            $response   =   "END Error occured, try later";
                        }
                    } else {

                        $planIDs        =   ['data_share_1gb:device:USSD_SHARE_FULL', 'data_share_2gb:device:USSD_SHARE_FULL', 'data_share_3gb:device:USSD_SHARE_FULL', 'data_share_5gb:device:USSD_SHARE_FULL', 'data_share_10gb:device:USSD_SHARE_FULL'];
                        $planId         =   $planIDs[$planSelect - 1];
                        $reqstID        =   date('YmdHi') . rand(99, 9999999);
                        $createData     =   $this->SimserverData($planId, $phoneNo, $reqstID);

                        if ($createData) {

                            // Update wallet .......................................................................................................

                            $new_bal_process = $balance - $planPrice;
                            $walletDetails = ['Account_Balance' => $new_bal_process];
                            $this->userServices->update($this->UserId, $walletDetails);

                            // .....................................................................................................................

                            Log::debug(['Success Data' => $createData]);
                            $planCodes      =   ['7', '8', '44', '11', '207'];
                            $planCode       =   $planCodes[$planSelect - 1];
                            $savedHistory   =   $this->saveData(1, $planCode, $phoneNo, $createData->data->reference, $balance, $new_bal_process, $planPrice);


                            if ($savedHistory) {

                                $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";
                            } else {

                                Log::debug(['failed to save data' => $savedHistory]);
                                $response   =   "END Operation failed, try later";
                            }

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

            $activeServer   =   $this->SwitchServer('AIRTEL');
            $networks       =   $inputArray[1];
            $planSelect     =   (int)$inputArray[2];
            $phoneNo        =   $inputArray[3];
            $pin            =   $inputArray[4];
            $prices         =   [160, 320, 640, 1600, 3200];
            $pVals          =   [500, 1, 2, 5, 10];
            $planVal        =   $pVals[$planSelect - 1];
            $planPrice      =   $prices[$planSelect - 1];
            $balance        =   $this->walletBalance($this->UserId);


            if ($this->userServices->checkPin($this->UserId, $pin) == true) {

                if ($balance < $planPrice) {

                    $response   =   "END Insufficient fund, try later ";
                } else {

                    $planIDs        =   ['500MB', '1GB', '2GB', '5GB', '10GB'];
                    $planId         =   $planIDs[$planSelect - 1];
                    $createData     =   $this->CheapestData($planId, $phoneNo);

                    if ($createData && $createData->status == 'success') {
                        Log::debug(['Data Received' => $createData]);
                        // Update wallet .......................................................................................................

                        $new_bal_process = $balance - $planPrice;
                        $walletDetails = ['Account_Balance' => $new_bal_process];
                        $this->userServices->update($this->UserId, $walletDetails);

                        // .....................................................................................................................
                        $planCodes      =   ['220', '221', '222', '224', '2311'];
                        $planCode       =   $planCodes[$planSelect - 1];
                        $savedHistory   =   $this->saveData(4, $planCode, $phoneNo, $createData->reference, $balance, $new_bal_process, $planPrice);

                        if ($savedHistory) {

                            $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";
                        } else {
                            Log::debug(['Failed to saveData' => $savedHistory]);
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


            if ($this->userServices->checkPin($this->UserId, $pin) == true) {

                if ($balance < $planPrice) {

                    $response   =   "END Insufficient fund, try later ";
                } else {

                    $planIDs        =   ['9MOB1000', '9MOB2000', '9MOB3000', '9MOB34500', '9MOB4000'];
                    $planId         =   $planIDs[$planSelect - 1];
                    $createData     =   $this->SmeplugData(3, $planId, $phoneNo);

                    if ($createData && $createData->status == true) {

                        Log::debug(['Data Received' => $createData]);
                        // Update wallet .......................................................................................................

                        $new_bal_process = $balance - $planPrice;
                        $walletDetails = ['Account_Balance' => $new_bal_process];
                        $this->userServices->update($this->UserId, $walletDetails);

                        // .....................................................................................................................
                        $planCodes      =   ['183', '184', '185', '186', '187'];
                        $planCode       =   $planCodes[$planSelect - 1];
                        $savedHistory   =   $this->saveData(3, $planCode, $phoneNo, $createData->data->reference, $balance, $new_bal_process, $planPrice);

                        if ($savedHistory) {

                            $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";
                        } else {
                            Log::debug(['Failed to save data' => $createData]);
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
            $prices         =   [950, 1425, 1900, 2375, 2850];
            $pVals          =   [3.9, 4.1, 5.8, 7.7, 10];
            $planVal        =   $pVals[$planSelect - 1];
            $planPrice      =   $prices[$planSelect - 1];
            $balance        =   $this->walletBalance($this->UserId);


            if ($this->userServices->checkPin($this->UserId, $pin) == true) {

                if ($balance < $planPrice) {

                    $response   =   "END Insufficient fund, try later ";
                } else {

                    $planIDs        =   ['49', '39', '52', '48', '44'];
                    $planId         =   $planIDs[$planSelect - 1];
                    $createData     =   $this->SmeplugData(2, $planId, $phoneNo);

                    if ($createData && $createData->status == true) {

                        Log::debug(['Data Received' => $createData]);
                        // Update wallet .......................................................................................................

                        $new_bal_process = $balance - $planPrice;
                        $walletDetails = ['Account_Balance' => $new_bal_process];
                        $this->userServices->update($this->UserId, $walletDetails);

                        // .....................................................................................................................
                        $planCodes      =   ['194', '195', '196', '197', '198'];
                        $planCode       =   $planCodes[$planSelect - 1];
                        $savedHistory   =   $this->saveData(2, $planCode, $phoneNo, $createData->data->reference, $balance, $new_bal_process, $planPrice);


                        if ($savedHistory) {

                            $response    =  "END Successfully purchase $planVal Gb data to: $phoneNo ";
                        } else {

                            Log::debug(['Failed to save data' => $createData]);
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

        // ----------------------------------------------- AIRTIME PROCESSING FOR SELF ----------------------------------------------------------->
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

            $response   =   $this->inputPIN();
        } elseif ($arrayLength === 5 && $inputArray[0] === '3' && $inputArray[1] === '1') {
            // Process Airtime Purchase
            $networkSelection   =   (int)$inputArray[3];
            $newamount          =   (int)$inputArray[2];
            $mobileNumber       =   $phoneNumber;
            $pin                =   $inputArray[4];
            $balance            =   $this->walletBalance($this->UserId);

            // Check if the "amount" and "phone_number" are set in the session
            if ($newamount === null || $mobileNumber === null) {

                $response = "END Invalid input. Please start the airtime purchase process again.";
            } else {

                if ($this->userServices->checkPin($this->UserId, $pin) == true) {

                    if ($balance < $newamount) {

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
                        $pervalue       =   $this->getAirtimePercentage($network);
                        $percVal        =   $pervalue / 100;
                        $amount2Purchase =   $newamount - ($percVal * $newamount);
                        // Get Wallet Details ----------------------------------------------------------------------------->
                        $req_bal_process = $this->walletBalance($this->UserId);
                        $new_balance     = $req_bal_process - $amount2Purchase;


                        if ($req_bal_process < $amount2Purchase) {
                            $response   =   'END Insufficient fund';
                        } else {

                            $response   =   $this->VTPassAirtimePurchase($requestID, $network, $newamount, $mobileNumber, $amount2Purchase, $customer_reference, $req_bal_process, $req_bal_process + $amount2Purchase);
                            $response   =   $this->updateWallet($this->UserId, $new_balance);
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
            $newamount          =   (int)$inputArray[2];
            $pin                =   $inputArray[5];
            $mobileNumber       =   ($inputArray[3]);
            $balance            =   $this->walletBalance($this->UserId);

            // Check if the "amount" and "phone_number" are set in the session
            if ($newamount === null || $mobileNumber === null) {

                $response = "END Invalid input. Please start the airtime purchase process again.";
            } else {

                if ($this->userServices->checkPin($this->UserId, $pin) == true) {

                    if ($balance < $newamount) {

                        $response   =   "END Insufficient fund, try later ";
                    } else {

                        // Reset the session data after processing
                        unset($_SESSION['amount']);
                        unset($_SESSION['phone_number']);

                        // Convert network selection to the actual network name
                        $networks = ['mtn', 'glo', 'airtel', 'etisalat'];
                        $network = isset($networks[$networkSelection - 1]) ? $networks[$networkSelection - 1] : 'Unknown Network';

                        // Get Airtime Percentage ------------------------------------------------------------------------->
                        $percVal        =   2 / 100;
                        $amount2Purchase =   $newamount - ($percVal * $newamount);
                        // Get Wallet Details ----------------------------------------------------------------------------->
                        $req_bal_process = $this->walletBalance($this->UserId);
                        $new_balance     = $req_bal_process - $amount2Purchase;

                        if ($req_bal_process < $amount2Purchase) {
                            $response   =   'END Insufficient fund';
                        } else {

                            $response   =   $this->VTPassAirtimePurchase($requestID, $network, $newamount, $mobileNumber, $amount2Purchase, $customer_reference, $req_bal_process, $req_bal_process + $amount2Purchase);
                            $response   =   $this->updateWallet($this->UserId, $new_balance);
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
        elseif ($text == "4") {
            $response    =   "END Dear $this->Name, This Service Is Coming Soon ... ";
        }

        // Pay Your Bill
        elseif ($text == "5") {
            $response    =   "END Dear $this->Name, This Service Is Coming Soon ... ";
        }

        // Register An Account
        elseif ($text == "6") {
            $response    =   "END Dear $this->Name, This Service Is Coming Soon ... ";
        }

        // History Menu
        elseif ($text == "7") {
            $response   =   $this->LatestHistory();
        }


        // End USSD Session
        else {
            $response = "END Invalid input. Please try again.";
        }

        // Send the response to the USSD gateway
        header('Content-type: text/plain');
        echo $response;
    }
    // Public service function ends here ....................................................................

    // Function Main Menu
    private function startMenu()
    {
        $response     =   "CON Dear $this->Name, Welcome to Kettlesub. What Would You Like To Do ? \n";
        $response    .=   "1. Account Balance \n";
        $response    .=   "2. Buy Data \n";
        $response    .=   "3. Buy Airtime \n";
        // $response    .=   "4. Buy Cable \n";
        // $response    .=   "5. Pay Your Bill \n";
        $response    .=   "6. Register New Account \n";
        $response    .=   "7. Latest Transaction \n";

        return $response;
    }

    public function generateMenu()
    {
        $response = "CON Dear $this->Name, Welcome to Kettlesub. What Would You Like To Do ? \n";
        $response .= "1. Account Balance \n";
        $response .= "2. Buy Data \n";
        $response .= "3. Buy Airtime \n";
        $response .= "4. Buy Cable \n";
        $response .= "5. Pay Your Bill \n";
        $response .= "6. Register New Account \n";
        $response .= "7. Latest Transaction \n";

        return $response;
    }

    // Mobile Data Server Switching
    private function SwitchServer($networkId)
    {
        $simServer = $this->simServerServices->getActiveSimServers($networkId);
        return $simServer->data_vending_medium;
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
        $response .= "1. 1Gb/₦285/30days \n";
        $response .= "2. 2Gb/₦570/30days \n";
        $response .= "3. 3Gb/₦855/30days \n";
        $response .= "4. 5Gb/₦1,425/30days \n";
        $response .= "5. 10Gb/₦2,850/30days";
        return $response;
    }

    // Buy Airtel Data SubMenu
    private function AirtelData()
    {
        // Buy Airtel Data submenu
        $response = "CON Airtel Data:\n";
        $response .= "1. 500MB/₦160/30days \n";
        $response .= "2. 1Gb/₦320/30days \n";
        $response .= "3. 2Gb/₦640/30days \n";
        $response .= "4. 5Gb/₦1,600/30days \n";
        $response .= "5. 10Gb/₦3200/30days";
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
        $response .= "1. 3.9Gb/₦950/30days \n";
        $response .= "2. 4.1Gb/₦1,425/30days \n";
        $response .= "3. 5.8Gb/₦1,900/30days \n";
        $response .= "4. 7.7Gb/₦2,375/30days \n";
        $response .= "5. 10Gb/₦2,850/30days";
        return $response;
    }

    // Create Smeplug Data
    private function SmeplugData($networkId, $smePlugPlanID, $phoneNumber)
    {
        $DataDetails = [
            'network_id'       => $networkId,
            'plan_id'          => $smePlugPlanID,
            'phone'            => $phoneNumber,
        ];

        $createSmeplugData = json_decode($this->dataServices->createSmePlugData($DataDetails));

        return $createSmeplugData;
    }

    private function CheapestData($smePlugPlanID, $phoneNumber)
    {
        $DataDetails = [
            'bundle'    => $smePlugPlanID,
            'mobile'    => $phoneNumber,
        ];

        $createCheapestData = json_decode($this->dataServices->createCheapestData($DataDetails));

        return $createCheapestData;
    }

    private function SimserverData($simServersPlanID, $phoneNumber, $requestID)
    {
        $DataDetails = [
            'process'           => 'buy',
            'api_key'           => $this->SimserAccesstoken,
            'product_code'      => $simServersPlanID,
            'amount'            => '50',
            'recipient'         => $phoneNumber,
            'callback'          => URL('/') . '/api/verifySimserverWebhook',
            'user_reference'    => $requestID
        ];

        $createSmeplugData = json_decode($this->dataServices->createSimserversData($DataDetails));

        return $createSmeplugData;
    }

    // Store Data in History Table
    private function saveData($nwt, $plan_id, $phoneNumber, $customer_ref, $bb, $ba, $data_price)
    {
        $commission = 0.0;
        $HistoryDetails = [
            'data_type' => '',
            'mobile_number' => $phoneNumber,
            'Status' => 'Processing',
            'medium' => 'USSD',
            'create_date' => Carbon::now(),
            'balance_before' => $bb,
            'balance_after' => $ba,
            'plan_amount' => $data_price,
            'Ported_number' => 0,
            'ident' => $customer_ref,
            'refund' => 0,
            'network_id' => $nwt,
            'plan_id' => $plan_id,
            'user_id' => $this->UserId,
            'api_response' => '',
        ];

        $output =   $this->historyServices->createDataHistory($HistoryDetails);

        return $output;
    }

    // Store Airtime in History Table
    private function saveAirtime($uid, $nwt, $amount, $paidMoney, $phoneNumber, $customer_ref, $bb, $ba)
    {
        $commission = 0.0;
        $HistoryDetails = [
            'mobile_number' => $phoneNumber,
            'airtime_type' => 'VTU',
            'amount' => $amount,
            'paid_amount' => $paidMoney,
            'Status' => 'Processing',
            'create_date' => Carbon::now(),
            'balance_before' => $bb,
            'balance_after' => $ba,
            'Ported_number' => 0,
            'medium' => 'USSD',
            'ident' => $customer_ref,
            'refund' => 0,
            'network_id' => $nwt,
            'user_id' => $uid,
        ];

        $output =   $this->historyServices->createAirtimeHistory($HistoryDetails);

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

        // Reset the session data after processing
        session()->forget('amount');
        session()->forget('phone_number');

        // Convert network selection to the actual network name
        $networks = ['MTN', 'Glo', 'Airtel'];
        $network = isset($networks[$networkSelection - 1]) ? $networks[$networkSelection - 1] : 'Unknown Network';

        return $this->endSession("Thank you for purchasing $amount airtime for $phoneNumber on $network network.");
    }

    private function VTPassAirtimePurchase($reqID, $ntw, $amt, $phn, $pamt, $cstReff, $bb, $ba)
    {
        $DataDetails = [
            'request_id'        => $reqID,
            'serviceID'         => $ntw,
            'amount'            => $amt,
            'phone'             => $phn,
        ];
        $createNigAirt = json_decode($this->airtimeServices->createVtpassAirtime($DataDetails));
        Log::info('createNigAirt', ['createNigAirt' => $createNigAirt]);
        if ($createNigAirt) {

            $AirtimeTransDetail = [
                'mobile_number' => $phn,
                'airtime_type'  => $createNigAirt->content->transactions->product_name,
                'amount'        => $amt,
                'paid_amount'   => $pamt,
                'Status'        => 'Processing',
                'create_date'   => Carbon::now(),
                'balance_before' => $bb,
                'balance_after' => $ba,
                'Ported_number' => 0,
                'medium'        => 'USSD',
                'ident'         => $cstReff,
                'refund'        => 0,
                'network_id'    => $ntw,
                'user_id'       => $this->UserId,
            ];

            $this->historyServices->createAirtimeHistory($AirtimeTransDetail);
            $response = "END Thank you for purchasing $amt airtime for $phn on $ntw network.";
        } else {

            $response   =   "Error Occured, try later";
        }

        return $response;
    }



    // Function Latest History
    private function LatestHistory()
    {

        try {
            $historyD    =   VtuappData::where("user_id", $this->UserId)->latest('create_date')->first();
            $historyA    =   VtuappAirtimetopup::where("user_id", $this->UserId)->latest('create_date')->first();
            $historyB    =   VtuappBillpayment::where("user_id", $this->UserId)->latest('create_date')->first();
            $historyC    =   VtuappCablesub::where("user_id", $this->UserId)->latest('create_date')->first();

            $latestHistory = collect([$historyD, $historyA, $historyB, $historyC])->filter()->sortByDesc('create_date')->first();

            if ($latestHistory) {
                $transPhn   =   $latestHistory->phone_number ?? $latestHistory->mobile_number ?? $latestHistory->Customer_Phone ?? $latestHistory->smart_card_number;
                $transDvc   =   $latestHistory->deviceNo ?? 'N/A';
                $transOpe   =   $latestHistory->network ?? 'N/A';
                $transPrc   =   $latestHistory->selling_price ?? 'N/A';
                $transPhr   =   $latestHistory->purchase ?? 'N/A';
                $response   = "END Your Latest Transaction Is : $transPhr | ₦ $transPrc | $transOpe | $transDvc $transPhn";
            } else {
                $response   =   "END No Transaction Found !!!";
            }

            return $response;
        } catch (\Throwable $th) {
            $response   =   "END Something went wrong, try later !!!";
            return $response;
        }
    }

    // get Wallet Balance
    private function walletBalance($uid)
    {
        // Get Wallet Details ----------------------------------------------------------------------------->
        $req_Account_process = $this->userServices->wallet($uid);
        return $req_Account_process;
    }

    private function updateWallet($uid, $bal)
    {
        $request_user = $this->userServices->update($uid, ['Account_Balance' => $bal]);
        return $request_user;
    }

    private function getAirtimePercentage($ntk)
    {
        $get_perc = $this->airtimeServices->getPercentage($ntk);
        return $get_perc;
    }

    // CREATE PIN
    private function inputPIN()
    {
        $request    =   "CON Enter your 4 digit PIN";
        return $request;
    }
}
