<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aliexpress_import_queues')) {
            return;
        }

        Schema::table('aliexpress_import_queues', function (Blueprint $table) {
            if (!Schema::hasColumn('aliexpress_import_queues', 'batch_id')) {
                $table->string('batch_id', 80)->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('aliexpress_import_queues', 'attempts')) {
                $table->unsignedInteger('attempts')->default(0)->after('status');
            }
            if (!Schema::hasColumn('aliexpress_import_queues', 'error_message')) {
                $table->text('error_message')->nullable()->after('message');
            }
            if (!Schema::hasColumn('aliexpress_import_queues', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->index()->after('queued_by_admin_id');
            }
            if (!Schema::hasColumn('aliexpress_import_queues', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('finished_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('aliexpress_import_queues')) {
            return;
        }

        Schema::table('aliexpress_import_queues', function (Blueprint $table) {
            foreach (['batch_id', 'attempts', 'error_message', 'created_by', 'cancelled_at'] as $column) {
                if (Schema::hasColumn('aliexpress_import_queues', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
