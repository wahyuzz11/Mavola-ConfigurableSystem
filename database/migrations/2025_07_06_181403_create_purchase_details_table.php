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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->double('subtotal');
            $table->integer('quantity');
            $table->timestamp('created_at')->nullable();
            $table->date('recalculate_date')->nullable();
            $table->foreignId('purchases_id')->constrained('purchases');
            $table->foreignId('products_id')->constrained('products');
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
