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
        Schema::create('signatures_admin', function (Blueprint $table) {
            $table->id();
            $table->longText('signature_path'); // مسار حفظ التوقيع
            $table->string('code_change'); // مسار حفظ التوقيع
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures_admin');
    }
};
