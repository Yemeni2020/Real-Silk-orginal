<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('integration_logs')) {
            return;
        }

        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->index();
            $table->string('action', 80)->index();
            $table->string('external_id')->nullable()->index();
            $table->string('status', 30)->index();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->string('request_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
