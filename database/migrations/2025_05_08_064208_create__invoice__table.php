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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('outlet_code');
            $table->string('service_id');
            $table->string('invoice_no')->unique();
            $table->date('invoice_date');
            $table->string('part_no_supplied')->nullable();
            $table->string('part_desc_supplied')->nullable();
            
            $table->integer('supplied_qty')->nullable();
            $table->decimal('part_sales_amount', 10, 2)->nullable();
            // $table->decimal('part_discount', 10, 2)->nullable();
            
            $table->decimal('total_labor_price', 10, 2)->nullable();
           
            $table->decimal('invoice_amount', 10, 2);
            $table->string('payment_method');
            $table->timestamps();

            // Optional: Add foreign key if service_id is related to another table
            // $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
