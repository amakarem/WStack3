<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $api_key;
    public function __construct()
    {
        $this->api_key = 'KikRlBQi8QbcbbV09wnZZ2PPa5UsyCA2';
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
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

    public function wallet(Request $request)
    {
        header("Content-Type: application/json");
        $wallet = get('https://api.1inch.dev/swap/v5.2/1/tokens');
        $balances = get('https://api.1inch.dev/balance/v1.2/1/balances/0xB00554B62F8830533CCAD1112479b0f95BD8fB20');
        foreach ($balances as $key => $value) {
            $wallet["tokens"][$key]["balance"] = $value;
        }
        unset($balances);
        $prices = get('https://api.1inch.dev/price/v1.1/1');
        foreach ($prices as $key => $value) {
            $wallet["tokens"][$key]["price"] = $value;
        }
        unset($prices);
        print_r(json_encode($wallet));
    }
}
