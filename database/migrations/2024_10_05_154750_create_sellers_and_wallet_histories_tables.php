<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // جدول factories
        Schema::create('factories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('f_name', 30)->nullable();
            $table->string('l_name', 30)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('image', 30)->default('def.png');
            $table->string('email', 80)->unique();
            $table->string('password', 80)->nullable();
            $table->string('status', 15)->default('pending');
            $table->rememberToken();
            $table->timestamps(); // for created_at and updated_at fields
            $table->string('bank_name', 191)->nullable();
            $table->string('branch', 191)->nullable();
            $table->string('account_no', 191)->nullable();
            $table->string('holder_name', 191)->nullable();
            $table->text('auth_token')->nullable();
            $table->double('sales_commission_percentage', 8, 2)->nullable();
            $table->string('gst', 191)->nullable();
            $table->string('cm_firebase_token', 191)->nullable();
            $table->boolean('pos_status')->default(0);
            $table->double('minimum_order_amount', 8, 2)->default(0.00);
            $table->integer('free_delivery_status')->default(0);
            $table->double('free_delivery_over_amount', 8, 2)->default(0.00);
            $table->string('app_language', 191)->default('en');
        });

        // جدول factory_wallet_histories
        Schema::create('factory_wallet_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('factory_id')->nullable();
            $table->double('amount', 8, 2)->default(0);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('payment', 191)->default('received');
            $table->timestamps(); // for created_at and updated_at fields

            // إضافة الفهارس والعلاقات
            $table->foreign('factory_id')->references('id')->on('factories')->onDelete('cascade');
        });

        // جدول factory_wallets
        Schema::create('factory_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('factory_id')->nullable();
            $table->double('total_earning', 8, 2)->default(0);
            $table->double('withdrawn', 8, 2)->default(0);
            $table->timestamps(); // for created_at and updated_at fields
            $table->double('commission_given', 8, 2)->default(0.00);
            $table->double('pending_withdraw', 8, 2)->default(0.00);
            $table->double('delivery_charge_earned', 8, 2)->default(0.00);
            $table->double('collected_cash', 8, 2)->default(0.00);
            $table->double('total_tax_collected', 8, 2)->default(0.00);

            // إضافة الفهارس والعلاقات
            $table->foreign('factory_id')->references('id')->on('factories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factory_wallets');
        Schema::dropIfExists('factory_wallet_histories');
        Schema::dropIfExists('factories');
    }
};
