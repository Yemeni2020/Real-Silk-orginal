<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        if(!Schema::hasColumn("sellers","referral_code")){
            Schema::table("sellers",function (Blueprint $table){
                $table->string("referral_code",255)->nullable()->unique();
            });

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        if(!Schema::hasColumn("sellers","referral_code")){

            Schema::table("sellers",function (Blueprint $table){
                $table->dropColumn("referral_code");
            });
        }
    }
};
