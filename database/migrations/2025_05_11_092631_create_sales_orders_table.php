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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('quotation_id')->nullable();
            $table->date('order_date')->nullable();
            $table->string('status', 50)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->text('delivery_address')->nullable();
            $table->timestamps(); // Adds created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
