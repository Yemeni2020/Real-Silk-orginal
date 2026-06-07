<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('flash_deal_products')) {
            Schema::create('flash_deal_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('flash_deal_id');
                $table->unsignedBigInteger('product_id');
                $table->decimal('discount', 10, 2)->default(0);
                $table->string('discount_type')->nullable();
                $table->timestamps();

                $table->foreign('flash_deal_id')->references('id')->on('flash_deals')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_deal_products');
    }
};
