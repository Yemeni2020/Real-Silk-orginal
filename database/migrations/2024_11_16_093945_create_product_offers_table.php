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
        if (!Schema::hasTable('product_offers')) {

            Schema::create('product_offers', function (Blueprint $table) {
                $table->id(); // حقل ID
                $table->unsignedBigInteger('product_id'); // ربط مع جدول المنتجات
                $table->integer('q_from'); // الكمية الدنيا
                $table->integer('q_to'); // الكمية العليا
                $table->decimal('price_unit', 10, 2); // سعر الوحدة
                $table->timestamps();

                // إضافة مفتاح أجنبي لربط المنتج
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_offers');
    }
};
