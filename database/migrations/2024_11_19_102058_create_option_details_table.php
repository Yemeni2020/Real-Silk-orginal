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
        if(!Schema::hasTable("option_product_details")){

            Schema::create('option_product_details', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // اسم التفاصيل
                $table->foreignId('option_id')->constrained('product_options')->onDelete('cascade'); // ربط التفاصيل بخيار المنتج
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_details');
    }
};
