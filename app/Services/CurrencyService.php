<?php
namespace App\Services;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Models\Currency;

class CurrencyService
{
    protected $apiKey;
    protected $baseCurrency;

    public function __construct(
        private readonly CurrencyRepositoryInterface $currency,
    )
    {
        $this->apiKey=getWebConfig(name: 'Currency_exchangerate');
        $this->baseCurrency = getCurrencyCode(); // العملة الأساسية

        // $this->apiKey= "8ad8a56b569c4fc56e0ad068"; // جلب مفتاح API من ملف .env
        // $this->baseCurrency = 'SAR'; // العملة الأساسية
    }

    public function getExchangeRates()
    {
        $reqUrl = "https://v6.exchangerate-api.com/v6/{$this->apiKey}/latest/{$this->baseCurrency}";
        $responseJson = file_get_contents($reqUrl);

        if ($responseJson !== false) {
            try {
                $response = json_decode($responseJson, true); // ✅ تحويل إلى مصفوفة
                $currency_auto = Currency::where("auto_change", "=", "1")->get();
    
                if (isset($response['result']) && $response['result'] === 'success') {
                    $rates = [];
                    foreach ($currency_auto as $c) {
                        if (isset($response['conversion_rates'][$c->code])) {
                            $rates[$c->code] = $response['conversion_rates'][$c->code];
                            $c->exchange_rate=$response['conversion_rates'][$c->code];
                            $c->save();
                        } else {
                            $rates[$c->code] = "Error: The Currency Code is invalid";
                        }
                    }
                    return $rates;
                } else {
                    return ['error' => 'API request failed or invalid response'];
                }
            } catch (\Exception $e) {
                return ['error' => 'JSON parse error'];
            }
        }

        return (object) ['error' => 'API request failed'];
    }
}



?>