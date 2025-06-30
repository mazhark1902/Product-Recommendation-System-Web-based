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
        Schema::create('master_part', function (Blueprint $table) {
            $table->id();
            $table->string('part_number');
            $table->string('part_name');
            $table->decimal('part_price', 10, 2); // Adjust precision as needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_part');
    }
};
