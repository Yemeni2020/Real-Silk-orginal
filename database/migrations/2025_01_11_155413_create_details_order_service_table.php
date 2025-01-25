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
        if(!Schema::hasTable("details_order_service")){
            Schema::create('details_order_service', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id'); // تطابق النوع
                $table->unsignedBigInteger('faild_id');
                $table->string('name_faild');
                $table->string('type_faild');
                $table->text('value');
                $table->timestamps();
            
                $table->foreign('order_id')->references('id')->on('order_service')->onDelete('cascade');
                $table->foreign('faild_id')->references('id')->on('form_items')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details_order_service');
    }
};
