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
        Schema::table('refund_transactions', function (Blueprint $table) {
            //
            $table->decimal("seller_amount")->default(0)->after("amount");
            $table->integer("referral")->default(0)->after("referral_commission");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refund_transactions', function (Blueprint $table) {
            //
            $table->dropColumn("seller_amount");
            $table->dropColumn("referral");
        });
    }
};
