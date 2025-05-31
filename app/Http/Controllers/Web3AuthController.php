<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\User;
use Elliptic\EC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use kornrunner\Keccak;

class Web3AuthController extends Controller
{
    public function __invoke(Request $request)
    {
        $request = json_decode($request->getContent());
        if (!isset($request->message) || !isset($request->signature) || !isset($request->address)) {
            echo json_encode(['error' => 'Invalid reqiest']);
        }
        if (!$this->authenticate($request)) {
            echo json_encode(['error' => 'Invalid signature', 'request-address' => $request->address]);
        }

        $user = User::where('eth_address', $request->address)->first();
        if (!$user) {
            $uuid = \Str::uuid()->toString();
            $user = new User();
            $user->name = $request->address;
            $user->email = $request->address . "@web3.eth";
            $user->eth_address = $request->address;
            //$user->avatar = $userData->avatar;
            $user->password = \Hash::make($uuid . now());
            $user->authtype = 'web3';
            $user->save();
        }
        Auth::login($user);
        return redirect('/dashboard');
        //echo json_encode(['id' => Auth::user()->id]);
        //return redirect('/dashboard');
    }

    protected function authenticate($request): bool
    {
        return $this->verifySignature(
            $request->message,
            $request->signature,
            $request->address,
        );
    }

    protected function verifySignature($message, $signature, $address): bool
    {
        $messageLength = strlen($message);
        $hash = Keccak::hash("\x19Ethereum Signed Message:\n{$messageLength}{$message}", 256);
        $sign = [
            "r" => substr($signature, 2, 64),
            "s" => substr($signature, 66, 64)
        ];

        $recId  = ord(hex2bin(substr($signature, 130, 2))) - 27;

        if ($recId != ($recId & 1)) {
            return false;
        }

        $publicKey = (new EC('secp256k1'))->recoverPubKey($hash, $sign, $recId);

        return $this->pubKeyToAddress($publicKey) === Str::lower($address);
    }

    protected function pubKeyToAddress($publicKey): string
    {
        return "0x" . substr(Keccak::hash(substr(hex2bin($publicKey->encode("hex")), 1), 256), 24);
    }
}
