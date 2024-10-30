<?php

namespace App\Console;

use App\Console\Commands\CurrencyRateUpdate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected $commands = [
        CurrencyRateUpdate::class
    ];

    protected function schedule(Schedule $schedule): void
    {
        if (basicControl()->currency_layer_auto_update == 1) {
            $schedule->command('app:currency-rate-update')
                ->{basicControl()->currency_layer_auto_update_at}();
        }

        $schedule->command('model:prune')->days(2);

        $schedule->call(function () {
            DB::table('users')->update(['verify_otp' => null]);
        })->everyFiveMinutes();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }



}
