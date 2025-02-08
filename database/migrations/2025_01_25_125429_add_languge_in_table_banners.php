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
        if(!Schema::hasColumn("banners","language")){
            Schema::table('banners', function (Blueprint $table) {
                //
                $table->string('language', 255)->nullable()->after('resource_id'); 
    
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if(!Schema::hasColumn("banners","language")){

            Schema::table('banners', function (Blueprint $table) {
                //
                $table->dropColumn('language');

            });
        }
    }
};
