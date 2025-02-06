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
        Schema::table("order_transactions",function(Blueprint $table){
            $table->decimal("referral_seller")->default(0)->after("seller_id");
            $table->integer("referral_commission")->nullable()->after("admin_commission");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table("order_transactions",function(Blueprint $table){
            $table->dropColumn("referral_seller");
            $table->dropColumn("referral_commission");
        });
    }
};
