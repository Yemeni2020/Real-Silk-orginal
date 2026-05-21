<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aliexpress_products', function (Blueprint $table) {
            $table->id();
            $table->string('ali_express_product_id')->unique();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->decimal('supplier_price', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->decimal('margin', 8, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->string('currency', 10)->nullable();
            $table->json('images')->nullable();
            $table->json('variants')->nullable();
            $table->text('supplier_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aliexpress_products');
    }
};
