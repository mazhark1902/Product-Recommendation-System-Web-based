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
    Schema::create('import_logs', function (Blueprint $table) {
        $table->id();
        $table->string('file_name');
        $table->string('file_checksum')->comment('MD5 checksum of the file content');
        $table->enum('status', ['pending', 'success', 'failed', 'partial_success', 'duplicate_file_skipped']);
        $table->unsignedInteger('total_rows')->nullable();
        $table->unsignedInteger('processed_rows')->nullable();
        $table->unsignedInteger('new_records')->nullable();
        $table->unsignedInteger('updated_records')->nullable();
        $table->text('error_message')->nullable();
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamps();

        $table->index('file_checksum');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
