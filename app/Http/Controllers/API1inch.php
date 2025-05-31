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

    public function convertBigIntToDecimal(string $bigInt, int $decimals, int $precision = 5): string
    {
        // Use BC Math for arbitrary precision math (make sure bc math is enabled)

        // Divide the big integer by 10^decimals
        $scale = $decimals + $precision; // extra precision for division

        $divisor = bcpow("10", (string)$decimals, $scale);
        $result = bcdiv($bigInt, $divisor, $scale);

        // Round the result to desired precision
        $dotPos = strpos($result, '.');
        if ($dotPos !== false) {
            // Truncate or round decimal part to $precision digits
            $integerPart = substr($result, 0, $dotPos);
            $decimalPart = substr($result, $dotPos + 1, $precision);

            // Optionally: round the last digit (simple round)
            if (strlen($decimalPart) == $precision) {
                $nextDigit = substr($result, $dotPos + 1 + $precision, 1);
                if ($nextDigit !== false && intval($nextDigit) >= 5) {
                    $decimalPart = bcadd($decimalPart, "1", 0);
                    if (strlen($decimalPart) > $precision) {
                        // Rounding overflow, increment integer part
                        $integerPart = bcadd($integerPart, "1", 0);
                        $decimalPart = str_repeat("0", $precision);
                    }
                }
            }
            return $integerPart . '.' . str_pad($decimalPart, $precision, '0', STR_PAD_RIGHT);
        }

        return $result; // no decimal point found, return as is
    }

    public function wallet($address)
    {
        header("Content-Type: application/json");
        $wallet = $this->get('https://api.1inch.dev/swap/v5.2/1/tokens');
        $wallet = $wallet["tokens"];
        $balances = $this->get('https://api.1inch.dev/balance/v1.2/1/balances/' . $address);
        foreach ($balances as $key => $value) {
            $decimals = $wallet[$key]["decimals"];
            $wallet[$key]["balance"] = $this->convertBigIntToDecimal($value, $decimals);;
        }
        unset($balances);
        $prices = $this->get('https://api.1inch.dev/price/v1.1/1');
        foreach ($prices as $key => $value) {
            $decimals = $wallet[$key]["decimals"];
            $wallet[$key]["price"] = $this->convertBigIntToDecimal($value, $decimals);
        }
        unset($prices);
        //clean up unused data
        foreach ($wallet as $key => $value) {
            if (!isset($value["price"]) || $value["price"] == 0) {
                unset($wallet[$key]);
                echo "removing $key \n";
            } else {
                unset($wallet["decimals"]);
                unset($wallet["eip2612"]);
                unset($wallet["tags"]);
            }
        }
        print_r(json_encode($wallet));
    }
}
