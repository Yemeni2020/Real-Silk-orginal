<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('flash_deals')) {
            Schema::create('flash_deals', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('status')->default(true);
                $table->boolean('featured')->default(false);
                $table->string('background_color')->nullable();
                $table->string('text_color')->nullable();
                $table->string('banner')->nullable();
                $table->string('slug')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('deal_type')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_deals');
    }
};
