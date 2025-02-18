<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CurrencyService;

class UpdateCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update';
    protected $description = 'تحديث سعر صرف العملات وطباعتها في الكونسول';
    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currencyService = app(CurrencyService::class);
        $rates = $currencyService->getExchangeRates();

        if (isset($rates->error)) { // ✅ تصحيح الوصول إلى الكائن
            $this->error("❌ خطأ: " . $rates->error);
            return;
        }

        // ✅ التأكد من وجود العملات المطلوبة قبل الطباعة
        // $usdRate = $rates->USD ?? 'غير متوفر';
        // $eurRate = $rates->EUR ?? 'غير متوفر';
        // $cnyRate = $rates->CNY ?? 'غير متوفر';

        foreach ($rates as $key => $value) {
            # code...
            $this->info("1 SAR = " . $value . " $key");

        }
        // // طباعة أسعار العملات المطلوبة
        // $this->info("✅ أسعار الصرف من SAR:");
        // $this->info("1 SAR = " . $usdRate . " USD");
        // $this->info("1 SAR = " . $eurRate . " EUR");
        // $this->info("1 SAR = " . $cnyRate . " CNY");
    }
}
