<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('sub_part_number');
            $table->string('sub_part_name');
            $table->decimal('average_monthly_sales', 8, 2);
            $table->integer('recommended_stock_level');
            $table->integer('current_stock');
            $table->date('forecast_date');
            $table->timestamps();

            $table->foreign('sub_part_number')->references('sub_part_number')->on('sub_parts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demand_forecasts');
    }
};