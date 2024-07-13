<?php
namespace App\services;

use App\Models\VtuappNetwork;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;

class DataServices
{

    public static function getToken(){
        $DataDetails = [
            'client_id'=> '919c366c-4645-46f8-80cc-35c77040014b',
            'client_secret' => '71apN0bg3CXO7ACVWe9mjjaibZu6sd4uC0VA2rH10GI=',
            'grant_type' => 'client_credentials'
        ];
        $response = Http::asForm()->post('https://idp.ding.com/connect/token', $DataDetails);
        return $response['access_token'];
    }

    public static function getSimserversToken()
    {
        $DataDetails = [
            'process'       => 'getAPIKey',
            'logintoken'    => 'kettlesub',
            'password'      => 'Developer1234',
        ];
        $response = json_decode( Http::asForm()->post('https://api.simservers.io', $DataDetails) );
        return $response->data->api_key;
    }

    // Integrating SmePlug Data
    public function createSmePlugData(array $DataDetails)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer a9f05dcbfcec8f00b4b7cc43b46adbf6bf0d6bdc39442b80e1479686f4d82898',
            'Content-Type' => 'application/json'
        ])->post('https://smeplug.ng/api/v1/data/purchase', $DataDetails);
        return $response;
    }

    public function createZoeData(array $DataDetails)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token dcb14bd771c1d83c3441e9189cf0b72cdec34733',
            'Content-Type' => 'application/json'
        ])->post('https://zoedatahub.com/api/data/', $DataDetails);
        return $response;
    }


    // Integrating SiIM Hosting Data
    public function createSimHostingData(array $DataDetails)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer 90c5b5fa939ca9e9df10d517bd3915b651aa6810f1fddb8a3977e8c5a1e2b74a',
            'Content-Type' => 'application/json'
        ])->post('https://smeplug.ng/api/v1/data/purchase', $DataDetails);
        return $response;
    }

    // Integrating VTpass Data
    public function createSimserversData(array $DataDetails)
    {
        $response = Http::post('https://api.simservers.io', $DataDetails);
        return $response;
    }


    // Integrating UssdSimHost Data
    public function createUssdSimHostData(array $DataDetails)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer 90c5b5fa939ca9e9df10d517bd3915b651aa6810f1fddb8a3977e8c5a1e2b74a',
            'Content-Type' => 'application/json'
        ])->post('https://smeplug.ng/api/v1/data/purchase', $DataDetails);
        return $response;
    }


    public function findUser()
    {
        //$response = Http::withToken('Token 7449197381ad06f36b660461759a4f4d9c3ead05')->get('https://tentendata.com.ng/api/user');
        $response = Http::withHeaders([
            'Authorization' => 'Token 7449197381ad06f36b660461759a4f4d9c3ead05',
            'Content-Type' => 'application/json'
        ])->get('https://tentendata.com.ng/api/user');

        return $response;
    }


}
