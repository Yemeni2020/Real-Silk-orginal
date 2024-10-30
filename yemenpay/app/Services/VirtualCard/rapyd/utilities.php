<?php


use App\Models\VirtualCardMethod;

function generate_string($length=12) {
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($permitted_chars), 0, $length);
}

function make_request($method, $path, $body = null) {
    $cardMethod = VirtualCardMethod::where('code', 'rapyd')->first();
    if (!$cardMethod){
        throw new Exception("Card method not found");
    }
    $access_key = $cardMethod->parameters?->public_key;
    $secret_key =  $cardMethod->parameters?->secret_key;

    // $base_url = 'https://sandboxapi.rapyd.net';
    $base_url = 'https://api.rapyd.net';

    $idempotency = generate_string();      // Unique for each request.
    $http_method = $method;                // Lower case.
    $salt = generate_string();             // Randomly generated for each request.
    $date = new DateTime();
    $timestamp = $date->getTimestamp();    // Current Unix time.

    $body_string = !is_null($body) ? json_encode($body,JSON_UNESCAPED_SLASHES) : '';
    $sig_string = "$http_method$path$salt$timestamp$access_key$secret_key$body_string";

    $hash_sig_string = hash_hmac("sha256", $sig_string, $secret_key);
    $signature = base64_encode($hash_sig_string);

    $request_data = NULL;

    if ($method === 'post') {
        $request_data = array(
            CURLOPT_URL => "$base_url$path",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body_string

        );
    } else {
        $request_data = array(
            CURLOPT_URL => "$base_url$path",
            CURLOPT_RETURNTRANSFER => true,
        );
    }

    $curl = curl_init();
    curl_setopt_array($curl, $request_data);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "access_key: $access_key",
        "salt: $salt",
        "timestamp: $timestamp",
        "signature: $signature",
        "idempotency: $idempotency"
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        throw new Exception("cURL Error #:".$err);
    } else {
        return json_decode($response, true);
    }
}
?>
