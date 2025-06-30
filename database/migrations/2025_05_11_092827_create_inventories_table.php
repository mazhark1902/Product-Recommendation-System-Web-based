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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id(); // Primary key, auto-increment
            $table->string('product_id')->nullable();
            $table->string('location', 100)->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->unsignedInteger('quantity_available')->nullable();
            $table->unsignedInteger('quantity_reserved')->nullable()->default(0);
            $table->unsignedInteger('quantity_damaged')->nullable()->default(0);
            $table->dateTime('last_updated')->nullable();
            $table->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
