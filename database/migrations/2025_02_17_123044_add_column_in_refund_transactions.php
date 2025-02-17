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
            $table->decimal("admin_commission")->default(0)->after("amount");
            $table->decimal("referral_commission")->default(0)->after("admin_commission");
            $table->decimal("tax")->default(0)->after("referral_commission");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refund_transactions', function (Blueprint $table) {
            //
            $table->dropColumn("admin_commission");
            $table->dropColumn("referral_commission");
            $table->dropColumn("tax");
        });
    }
};
