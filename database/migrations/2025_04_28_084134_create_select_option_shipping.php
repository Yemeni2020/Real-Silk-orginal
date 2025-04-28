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
        Schema::create('select_option_shipping', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->unsignedBigInteger("option_shipping");
            $table->timestamps();

            $table->foreign('option_shipping')->references('id')->on('options_shipping')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('select_option_shipping');
    }
};
