<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aliexpress_product_previews')) {
            return;
        }

        Schema::create('aliexpress_product_previews', function (Blueprint $table) {
            $table->id();
            $table->text('source_input');
            $table->string('ali_express_product_id')->index();
            $table->string('title')->nullable();
            $table->string('normalized_title')->nullable();
            $table->decimal('supplier_price', 12, 2)->nullable();
            $table->decimal('supplier_shipping_price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->nullable();
            $table->decimal('estimated_profit', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->json('images')->nullable();
            $table->json('variants')->nullable();
            $table->text('supplier_url')->nullable();
            $table->string('availability_status', 50)->nullable();
            $table->json('warnings')->nullable();
            $table->json('block_reasons')->nullable();
            $table->string('policy_status', 30)->default('allowed')->index();
            $table->json('pricing_payload')->nullable();
            $table->json('normalized_payload')->nullable();
            $table->json('raw_payload')->nullable();
            $table->string('status', 30)->default('previewed')->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('local_product_id')->nullable()->index();
            $table->text('message')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aliexpress_product_previews');
    }
};
