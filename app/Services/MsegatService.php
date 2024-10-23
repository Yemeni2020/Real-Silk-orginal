<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MsegatService
{
    protected $baseUrl;
    protected $username;
    protected $apiKey;
    protected $userSender;

    public function __construct()
    {
        $this->baseUrl = env('MSEGAT_BASE_URL');
        $this->username = env('MSEGAT_USERNAME');
        $this->apiKey = env('MSEGAT_API_KEY');
        $this->userSender = env('MSEGAT_USER_SENDER');
    }

    public function sendOtp($number, $message)
    {
        $response = Http::post($this->baseUrl, [
            'userName' => $this->username,
            'apiKey' => $this->apiKey,
            'numbers' => $number,
            'userSender' => $this->userSender,
            'msg' => $message,
            'msgEncoding' => 'UTF8'
        ]);

        return $response->json();
    }
}
