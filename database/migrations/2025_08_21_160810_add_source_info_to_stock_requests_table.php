<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('warehouse_id'); // e.g., VENDOR, WAREHOUSE_TRANSFER
            $table->string('source_reference')->nullable()->after('source_type'); // e.g., PO-123, TO-456
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_reference']);
        });
    }
};