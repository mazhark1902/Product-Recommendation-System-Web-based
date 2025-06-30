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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('inventory_movement_id')->nullable();
            $table->string('product_id')->nullable();
            $table->enum('movement_type', ['IN', 'OUT', 'RETURN'])->nullable();
            $table->integer('quantity')->nullable();
            $table->dateTime('movement_date')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->integer('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
