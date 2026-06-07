<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aliexpress_products')) {
            return;
        }

        Schema::table('aliexpress_products', function (Blueprint $table) {
            if (!Schema::hasColumn('aliexpress_products', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('aliexpress_products', 'sync_status')) {
                $table->string('sync_status', 30)->default('pending')->index()->after('last_synced_at');
            }
            if (!Schema::hasColumn('aliexpress_products', 'sync_error')) {
                $table->text('sync_error')->nullable()->after('sync_status');
            }
            if (!Schema::hasColumn('aliexpress_products', 'supplier_stock_status')) {
                $table->string('supplier_stock_status', 50)->nullable()->after('sync_error');
            }
            if (!Schema::hasColumn('aliexpress_products', 'supplier_shipping_price')) {
                $table->decimal('supplier_shipping_price', 12, 2)->nullable()->after('supplier_price');
            }
            if (!Schema::hasColumn('aliexpress_products', 'supplier_currency')) {
                $table->string('supplier_currency', 10)->nullable()->after('currency');
            }
            if (!Schema::hasColumn('aliexpress_products', 'supplier_product_url')) {
                $table->text('supplier_product_url')->nullable()->after('supplier_url');
            }
            if (!Schema::hasColumn('aliexpress_products', 'is_available')) {
                $table->boolean('is_available')->default(true)->index()->after('is_active');
            }
            if (!Schema::hasColumn('aliexpress_products', 'source_updated_at')) {
                $table->timestamp('source_updated_at')->nullable()->after('is_available');
            }
            if (!Schema::hasColumn('aliexpress_products', 'block_reason')) {
                $table->text('block_reason')->nullable()->after('sync_error');
            }
            if (!Schema::hasColumn('aliexpress_products', 'warning_flags')) {
                $table->json('warning_flags')->nullable()->after('block_reason');
            }
            if (!Schema::hasColumn('aliexpress_products', 'local_product_id')) {
                $table->unsignedBigInteger('local_product_id')->nullable()->index()->after('ali_express_product_id');
            }
            if (!Schema::hasColumn('aliexpress_products', 'variant_mappings')) {
                $table->json('variant_mappings')->nullable()->after('variants');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('aliexpress_products')) {
            return;
        }

        Schema::table('aliexpress_products', function (Blueprint $table) {
            foreach ([
                'last_synced_at',
                'sync_status',
                'sync_error',
                'supplier_stock_status',
                'supplier_shipping_price',
                'supplier_currency',
                'supplier_product_url',
                'is_available',
                'source_updated_at',
                'block_reason',
                'warning_flags',
                'local_product_id',
                'variant_mappings',
            ] as $column) {
                if (Schema::hasColumn('aliexpress_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
