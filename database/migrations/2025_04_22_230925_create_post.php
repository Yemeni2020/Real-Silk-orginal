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
        Schema::create('post', function (Blueprint $table) {
            $table->id();
            
            $table->integer("user_id");
            $table->string("title");
            $table->string("slug");
            $table->longText("details");
            $table->integer("category_id");
            $table->longText("images");
            $table->string("thumbnail");
            $table->string("thumbnail_storage_type",10);
            $table->string("video_provider",30);
            $table->string("video_url");
            $table->string("meta_title");
            $table->text("meta_description");
            $table->text("meta_keywords");
            $table->string("meta_image");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post');
    }
};
