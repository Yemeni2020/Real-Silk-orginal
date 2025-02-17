<?php
namespace App\Services;

class CurrencyService
{
    protected $apiKey;
    protected $baseCurrency;

    public function __construct()
    {
        $this->apiKey = "8ad8a56b569c4fc56e0ad068"; // جلب مفتاح API من ملف .env
        $this->baseCurrency = 'SAR'; // العملة الأساسية
    }

    public function getExchangeRates()
    {
        $reqUrl = "https://v6.exchangerate-api.com/v6/{$this->apiKey}/latest/{$this->baseCurrency}";
        $responseJson = file_get_contents($reqUrl);

        if ($responseJson !== false) {
            try {
                $response = json_decode($responseJson);

                if (isset($response->result) && $response->result === 'success') {
                    return $response->conversion_rates; // إرجاع الكائن مباشرة
                } else {
                    return (object) ['error' => 'API request failed or invalid response'];
                }
            } catch (\Exception $e) {
                return (object) ['error' => 'JSON parse error'];
            }
        }

        return (object) ['error' => 'API request failed'];
    }
}



?>