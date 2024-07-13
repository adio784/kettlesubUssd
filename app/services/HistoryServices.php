<?php
namespace App\services;

use App\Models\VtuappAirtime;
use App\Models\VtuappBillpayment;
use App\Models\VtuappCablesub;
use App\Models\VtuappData;
use App\Models\VtuappNetwork;

class HistoryServices
{

    public function createDataHistory(array $HistoryDetails)
    {
        return VtuappData::create($HistoryDetails);
    }

    public function createAirtimeHistory(array $HistoryDetails)
    {
        return VtuappAirtime::create($HistoryDetails);
    }

    public function createBillHistory(array $HistoryDetails)
    {
        return VtuappBillpayment::create($HistoryDetails);
    }

    public function createCableHistory(array $HistoryDetails)
    {
        return VtuappCablesub::create($HistoryDetails);
    }


}
