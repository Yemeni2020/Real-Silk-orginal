<?php

namespace App\Services;

use GuzzleHttp\Client;

class TapPaymentService
{
    protected $client;

    public function __construct()
    {
        // تهيئة عميل Guzzle مع إعدادات الطلب
        $this->client = new Client([
            'base_uri' => 'https://api.tap.company/v2/',
            'timeout' => 30.0,
        ]);
    }

    public function authorizePayment($paymentData)
    {
        try {
            $response = $this->client->post('authorize/', [
                'json' => $paymentData,
                'headers' => [
                    'Authorization' => 'Bearer sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            // التعامل مع الأخطاء
            return ['error' => $e->getMessage()];
        }
    }
}
