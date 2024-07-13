<?php
namespace App\services;

use Illuminate\Support\Facades\Http;

class AirtimeServices
{

    public function createVtpassAirtime(array $AirtimeDetails)
    {
        $response = Http::withBasicAuth(
            'moyofolatimothy@gmail.com',
            'Damola24..'
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

}
