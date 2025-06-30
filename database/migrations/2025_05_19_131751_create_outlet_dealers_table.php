<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutletDealersTable extends Migration
{
    public function up()
    {
        Schema::create('outlet_dealers', function (Blueprint $table) {
            $table->string('outlet_code')->primary(); // PK
            $table->string('outlet_name');
            $table->string('dealer_code');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->timestamps(); // optional: created_at & updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('outlet_dealers');
    }
}
