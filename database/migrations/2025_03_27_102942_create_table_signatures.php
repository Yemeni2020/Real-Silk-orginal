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
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("seller");
            $table->longText('signature_path'); // مسار حفظ التوقيع
            $table->string('contract_path'); // مسار حفظ التوقيع
            $table->timestamps();

            $table->index("seller");

            $table->foreign("seller")->references("id")->on("sellers")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
