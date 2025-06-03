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
        $walletcache_file = base_path() . '/wallet.php';
        if (file_exists($walletcache_file)) {
            $walletcache = include $walletcache_file;
            if (isset($walletcache['created_at']) && (time() - $walletcache['created_at']) <= 72000) {
                $wallet = $walletcache;
            }
        }
        if (!isset($wallet)) {
            $wallet = $this->get('https://api.1inch.dev/swap/v6.0/1/tokens');
            if (!isset($wallet["tokens"]) && isset($walletcache)) {
                $wallet = $walletcache;
                $wallet['message'] = "WARNING: 1inch API token is expired this is cached data, update the API token.";
            } else {
                $wallet = $wallet["tokens"];
                $wallet['created_at'] = time();
                file_put_contents($walletcache_file, '<?php return ' . var_export($wallet, true) . ';');
            }
        }
        unset($wallet['created_at']);

        $balances = $this->get('https://api.1inch.dev/balance/v1.2/1/balances/' . $address);
        if (is_array($balances)) {
            foreach ($balances as $key => $value) {
                if (!isset($wallet[$key])) {
                    continue;
                }
                $decimals = $wallet[$key]["decimals"];
                $wallet[$key]["balance"] = $this->convertBigIntToDecimal($value, $decimals);;
            }
        } else {
            foreach ($wallet as $key => $value) {
                if (is_array($value)) {
                    $wallet[$key]["balance"] = 0;
                }
            }
        }
        unset($balances);
        $pricescache_file = base_path() . '/prices.php';
        if (file_exists($pricescache_file)) {
            $pricescache = include $pricescache_file;
            if (isset($pricescache['created_at']) && (time() - $pricescache['created_at']) <= 60) {
                $prices = $pricescache;
            }
        }
        if (!isset($prices)) {
            $prices = $this->get('https://api.1inch.dev/price/v1.1/1'); //?currency=USD
            if ($prices == NULL && isset($pricescache)) {
                $prices = $pricescache;
            } else {
                $prices['created_at'] = time();
                file_put_contents($pricescache_file, '<?php return ' . var_export($prices, true) . ';');
            }
        }
        unset($prices['created_at']);
        foreach ($prices as $key => $value) {
            if (!isset($wallet[$key])) {
                continue;
            }
            $decimals = $wallet[$key]["decimals"];
            $wallet[$key]["price"] = $this->convertBigIntToDecimal($value, $decimals);
        }
        unset($prices);
        //clean up unused data
        foreach ($wallet as $key => $value) {
            if (!isset($value["price"]) || $value["price"] === 0) {
                unset($wallet[$key]);
            } else if (!isset($value["tags"]["crosschain"])) {
                //unset($wallet[$key]);
            } else {
                // unset($wallet[$key]["decimals"]);
                unset($wallet[$key]["eip2612"]);
                unset($wallet[$key]["tags"]);
                $wallet[$key]["address"] = trim(str_replace(" ", "", $wallet[$key]["address"]));
            }
        }
        uasort($wallet, function ($a, $b) {
            return $b['balance'] <=> $a['balance'];
        });
        if (!isset($_GET["getAll"])) {
            $wallet = array_slice($wallet, 0, 30, true);
        }
        print_r(json_encode($wallet));
    }

    public function getswapquote(Request $request)
    {
        $input = $request->all();
        $chainID = $input["chainID"];
        $params = http_build_query([
            'src' => $input["from"],
            'dst' => $input["to"],
            'amount' => $input["amount"]
        ]);
        $url = "https://api.1inch.dev/swap/v6.0/$chainID/quote?" . $params;
        print_r(json_encode($this->get($url)));
    }

    public function swapnow(Request $request)
    {
        $input = $request->all();
        $chainID = $input["chainID"];
        $params = http_build_query([
            'src' => $input["from"],
            'dst' => $input["to"],
            'amount' => $input["amount"],
            'from' => $input["address"],
            'origin' => $input["origin"],
            'slippage' => $input["slippage"]
        ]);
        $url = "https://api.1inch.dev/swap/v6.0/$chainID/swap?" . $params;
        print_r(json_encode($this->get($url)));
    }

    public function spender(Request $request)
    {
        $input = $request->all();
        $chainID = $input["chainID"];
        $url = "https://api.1inch.dev/swap/v6.0/$chainID/approve/spender?";
        print_r(json_encode($this->get($url)));
    }
}
