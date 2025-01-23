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
        Schema::table('currencies', function (Blueprint $table) {
            //
            $table->string('language', 10)->nullable()->after('exchange_rate'); // ضع اسم العمود السابق الذي تريد أن يكون الحقل بعده بدلاً من "column_name"

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            //
            $table->dropColumn('language');
        });
    }
};
