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
        Schema::create('_service_', function (Blueprint $table) {
            $table->id();
            $table->string('outlet_code')->nullable();
            $table->string('service_code')->nullable();
            $table->date('service_date')->nullable();
            $table->integer('kilometer')->nullable();
            $table->string('service_type')->nullable();
            $table->unsignedBigInteger('car_id')->nullable();
            $table->string('car_model')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_service_');
    }
};
