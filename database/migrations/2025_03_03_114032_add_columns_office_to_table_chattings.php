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
        if(!Schema::hasColumn("chattings","office_id")){
            Schema::table('chattings', function (Blueprint $table) {
                //
                $table->integer("office_id")->nullable()->after("seller_id");
                $table->boolean("sent_by_office")->default(0)->after("sent_by_seller");
                $table->boolean("seen_by_office")->default(1)->after("seen_by_seller");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chattings', function (Blueprint $table) {
            //
            $table->dropColumn("office_id");
            $table->dropColumn("sent_by_office");
            $table->dropColumn("seen_by_office");
        });
    }
};
