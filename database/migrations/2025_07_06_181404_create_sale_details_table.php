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
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->double('subtotal');
            $table->float('quantity');
            $table->double('discount_value')->nullable();
            $table->date('recalculate_date')->nullable();
            $table->foreignId('sales_id')->constrained('sales');
            $table->foreignId('products_id')->constrained('products');
            $table->timestamps();
            $table->softDeletes();
            $table->double('cogs_sale')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
