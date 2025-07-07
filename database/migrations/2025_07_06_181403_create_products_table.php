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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name', 45);
            $table->longText('description');
            $table->string('image', 455)->nullable();
            $table->double('price');
            $table->integer('minimum_total_stock');
            $table->integer('total_stock');
            $table->string('unit_name', 45);
            $table->integer('expired_date_settings');
            $table->double('cost')->nullable();
            $table->foreignId('categories_id')->constrained('categories');
            $table->integer('starting_stock_periodic')->nullable();
            $table->date('periodic_start_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
