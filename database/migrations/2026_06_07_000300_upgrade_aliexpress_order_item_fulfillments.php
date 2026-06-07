<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aliexpress_order_item_fulfillments')) {
            return;
        }

        Schema::table('aliexpress_order_item_fulfillments', function (Blueprint $table) {
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'status')) {
                $table->string('status', 50)->default('not_started')->index()->after('order_detail_id');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'supplier_tracking_number')) {
                $table->string('supplier_tracking_number')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'supplier_carrier')) {
                $table->string('supplier_carrier')->nullable()->after('carrier');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'supplier_order_url')) {
                $table->text('supplier_order_url')->nullable()->after('supplier_line_id');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'supplier_paid_amount')) {
                $table->decimal('supplier_paid_amount', 12, 2)->nullable()->after('supplier_order_url');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'supplier_currency')) {
                $table->string('supplier_currency', 10)->nullable()->after('supplier_paid_amount');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'placed_by_admin_id')) {
                $table->unsignedBigInteger('placed_by_admin_id')->nullable()->index()->after('updated_by_admin_id');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'placed_at')) {
                $table->timestamp('placed_at')->nullable()->after('placed_by_admin_id');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'tracking_synced_at')) {
                $table->timestamp('tracking_synced_at')->nullable()->after('placed_at');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'notes')) {
                $table->text('notes')->nullable()->after('note');
            }
            if (!Schema::hasColumn('aliexpress_order_item_fulfillments', 'last_error')) {
                $table->text('last_error')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('aliexpress_order_item_fulfillments')) {
            return;
        }

        Schema::table('aliexpress_order_item_fulfillments', function (Blueprint $table) {
            foreach ([
                'status',
                'supplier_tracking_number',
                'supplier_carrier',
                'supplier_order_url',
                'supplier_paid_amount',
                'supplier_currency',
                'placed_by_admin_id',
                'placed_at',
                'tracking_synced_at',
                'notes',
                'last_error',
            ] as $column) {
                if (Schema::hasColumn('aliexpress_order_item_fulfillments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
