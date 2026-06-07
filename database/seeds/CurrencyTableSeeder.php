<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CurrencyTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $payload = [
            'name' => 'Saudi Riyal',
            'symbol' => 'ريال',
            'code' => 'SAR',
            'exchange_rate' => '0.27',
            'status' => 1,
        ];

        if (Schema::hasColumn('currencies', 'auto_change')) {
            $payload['auto_change'] = 0;
        }

        if (Schema::hasColumn('currencies', 'updated_at')) {
            $payload['updated_at'] = $now;
        }

        if (Schema::hasColumn('currencies', 'created_at')) {
            $payload['created_at'] = $now;
        }

        DB::table('currencies')->updateOrInsert(
            ['code' => 'SAR'],
            $payload
        );
    }
}
