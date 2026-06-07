<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->updateOrInsert(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Master Admin',
                'phone' => '01759412381',
                'admin_role_id' => 1,
                'image' => 'def.png',
                'password' => bcrypt(12345678),
                'remember_token' => Str::random(10),
            ]
        );
    }
}
