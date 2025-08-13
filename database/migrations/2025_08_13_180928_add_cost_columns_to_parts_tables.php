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
        Schema::table('sub_parts', function (Blueprint $table) {
            // Menambahkan kolom harga modal setelah kolom 'price'
            $table->decimal('cost', 12, 2)->default(0.00)->after('price');
        });

        Schema::table('master_part', function (Blueprint $table) {
            // Menambahkan kolom total harga modal setelah kolom 'part_price'
            $table->decimal('total_cost', 12, 2)->default(0.00)->after('part_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_parts', function (Blueprint $table) {
            $table->dropColumn('cost');
        });

        Schema::table('master_part', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });
    }
};
