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
        if(!Schema::hasTable("form_items")){

            Schema::create('form_items', function (Blueprint $table) {
                $table->id(); // مفتاح رئيسي
                $table->integer('item'); //  العنصر
                $table->string('item_name'); // اسم العنصر
                $table->string('item_type'); // نوع العنصر
                $table->integer('item_order')->default(0); // ترتيب العنصر
                $table->boolean('is_required')->default(false); // هل العنصر مطلوب
                $table->integer('item_length')->default(255); // طول العنصر
                $table->string('default_value')->nullable(); // القيمة الافتراضية
                $table->json('select_options')->nullable(); // خيارات التحديد (للـ select/radios/checkbox)
                $table->timestamps(); // لإنشاء created_at و updated_at
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_items');
    }
};
