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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable();
            }

            if (!Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 8, 2)->nullable();
            }

            if (!Schema::hasColumn('products', 'width')) {
                $table->decimal('width', 8, 2)->nullable();
            }

            if (!Schema::hasColumn('products', 'height')) {
                $table->decimal('height', 8, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach (['weight', 'length', 'width', 'height'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
