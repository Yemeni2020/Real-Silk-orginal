<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aliexpress_import_queues', function (Blueprint $table) {
            $table->id();
            $table->text('source_input');
            $table->string('ali_express_product_id')->nullable()->index();
            $table->string('status', 30)->default('pending')->index();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('store_product_id')->nullable()->index();
            $table->unsignedBigInteger('queued_by_admin_id')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aliexpress_import_queues');
    }
};
