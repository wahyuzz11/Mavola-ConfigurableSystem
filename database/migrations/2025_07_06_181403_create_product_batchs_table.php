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
        Schema::create('product_batchs', function (Blueprint $table) {
            $table->id();
            $table->string('serial_code', 45);
            $table->float('stock');
            $table->double('cost_per_batch')->nullable();
            $table->date('purchase_date');
            $table->date('expired_date');
            $table->tinyInteger('empty_status');
            $table->foreignId('products_id')->constrained('products');
            $table->foreignId('purchase_details_id')->constrained('purchase_details');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batchs');
    }
};
