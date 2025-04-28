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
        Schema::create('shipping_country', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("from-country");
            $table->string("to-country");
            $table->unsignedBigInteger("shipping_method");
            $table->timestamps();

            $table->foreign('shipping_method')->references('id')->on('shipping_methods')->onDelete('cascade');

        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_country');
    }
};
