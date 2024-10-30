<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyLayerService
{
    public function getCurrencyRate($currencies)
    {
        $endpoint = 'live';
        $source = basicControl()->base_currency;
        $currency_layer_url = "http://api.currencylayer.com";
        $currency_layer_access_key = basicControl()->currency_layer_access_key;

        $currencyLists = array();
        foreach ($currencies as $currency) {
            foreach ($currency as $singleCurrency) {
                $currencyLists[] = $singleCurrency;
            }
        }

        $currencyLists = array_unique($currencyLists);
        $currencies = implode(',', $currencyLists);
        $CurrencyAPIUrl = "$currency_layer_url/$endpoint?access_key=$currency_layer_access_key&source=$source&currencies=$currencies";

        $response = Http::acceptJson()->get($CurrencyAPIUrl);

        if ($response->status() == 200) {
            return json_decode($response->body());
        }

        return back()->with('error', "Something went wrong while fetching currency layer's api.");

    }

}
