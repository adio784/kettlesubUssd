<?php
namespace App\services;

use App\Models\VtuappNetwork;

class SimServerServices
{

    public function getActiveSimServers($operatorID)
    {
        return VtuappNetwork::where('status', 'Strong')->where('name', $operatorID)->first();
    }


}
