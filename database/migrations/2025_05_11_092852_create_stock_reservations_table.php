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
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('part_number')->nullable();
            $table->string('sales_order_id')->nullable();
            $table->integer('reserved_quantity')->nullable();
            $table->dateTime('reservation_date')->nullable();
            $table->enum('status', ['ACTIVE', 'RELEASED'])->nullable();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
