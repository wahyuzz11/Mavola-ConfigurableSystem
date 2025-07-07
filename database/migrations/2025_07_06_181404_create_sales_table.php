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
            $table->double('total_price');
            $table->dateTime('sale_date');
            $table->enum('delivery_method', ['DEL-01', 'DEL-02']);
            $table->enum('payment_methods', ['S-PAY-01', 'S-PAY-02', 'S-PAY-03']);
            $table->enum('status', ['In delivery', 'completed']);
            $table->double('global_discount')->nullable();
            $table->timestamp('shipped_date')->nullable();
            $table->double('discount_cashback')->nullable();
            $table->string('recipient_name', 45)->nullable();
            $table->longText('customer_address')->nullable();
            $table->integer('delivery_cost')->nullable();
            $table->foreignId('users_id')->constrained('users');
            $table->foreignId('customers_id')->constrained('customers');
            $table->timestamps();
            $table->softDeletes();
            $table->string('cogs_method', 45)->nullable();
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
