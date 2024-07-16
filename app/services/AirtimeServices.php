<?php
namespace App\services;

use App\Models\VtuappTopuppercentage;
use Illuminate\Support\Facades\Http;

class AirtimeServices
{

    public function createVtpassAirtime(array $AirtimeDetails)
    {
        $response = Http::withBasicAuth(
            'timothydamola@gmail.com',
            '24..Moyofola'
            )->post(
                'https://api-service.vtpass.com/api/pay',
                $AirtimeDetails
            );
        // $response = Http::withHeaders([
        //     'api-key' => $this->ApiKey,
        //     'secret-key' => $this->Secrete_Key,
        //     'Content-Type' => 'application/json'
        // ])->post('https://sandbox.vtpass.com/api/pay', $AirtimeDetails);
        return $response;
    }

    public function getPercentage($networkId)
    {
        $perc = VtuappTopuppercentage::where('network_id', $networkId)->first();
        return $perc->percent;
    }

}
