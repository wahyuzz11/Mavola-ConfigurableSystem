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
            $table->decimal('price',15,2);
            $table->decimal('minimum_total_stock',10,2);
            $table->decimal('total_stock',10,2);
            $table->string('unit_name', 45);
            $table->integer('expired_date_settings');
            $table->decimal('price',15,2);
            $table->decimal('cogs_cost',15,2);
            $table->foreignId('categories_id')->constrained('categories');
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
