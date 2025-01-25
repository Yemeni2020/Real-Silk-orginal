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
        if(!Schema::hasColumn("brands","seller_id")){

            Schema::table('brands', function (Blueprint $table) {
                $table->integer('seller_id')->nullable()->after('name'); // إضافة الحقل seller_id
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('seller_id'); // حذف الحقل
        });
    }
};
