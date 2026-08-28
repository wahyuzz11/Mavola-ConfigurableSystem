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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 45);
            $table->decimal('total_sales', 15, 2);
            $table->dateTime('sale_date');
            $table->enum('delivery_method', ['DEL-01', 'DEL-02']);
            $table->enum('payment_methods', ['S-PAY-01', 'S-PAY-02', 'S-PAY-03']);
            $table->string('cogs_method', 45)->nullable();
            $table->decimal('global_discount')->nullable();
            $table->decimal('total_discount')->nullable();
            $table->decimal('discount_cashback')->nullable();
            $table->double('discount_cashback')->nullable();
            $table->decimal('delivery_cost',15,2)->nullable();
            $table->foreignId('users_id')->constrained('users');
            $table->foreignId('customers_id')->constrained('customers');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
