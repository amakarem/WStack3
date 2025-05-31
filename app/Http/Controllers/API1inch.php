<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class API1inch extends Controller
{
    private $api_key;
    public function __construct()
    {
        $this->api_key = 'KikRlBQi8QbcbbV09wnZZ2PPa5UsyCA2';
    }

    public function get($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->api_key,
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public function wallet($address)
    {
        header("Content-Type: application/json");
        $wallet = $this->get('https://api.1inch.dev/swap/v5.2/1/tokens');
        $wallet = $wallet["tokens"];
        $balances = $this->get('https://api.1inch.dev/balance/v1.2/1/balances/' . $address);
        foreach ($balances as $key => $value) {
            $wallet[$key]["balance"] = $value;
        }
        unset($balances);
        $prices = $this->get('https://api.1inch.dev/price/v1.1/1');
        foreach ($prices as $key => $value) {
            $wallet[$key]["price"] = $value;
        }
        unset($prices);
        print_r(json_encode($wallet));
    }
}
