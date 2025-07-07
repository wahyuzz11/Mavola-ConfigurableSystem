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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 45);
            $table->double('total_price');
            $table->dateTime('purchase_date');
            $table->enum('status', ['In delivery', 'completed']);
            $table->enum('receive_method', ['RE-01', 'RE-02']);
            $table->enum('payment_method', ['P-PAY-01', 'P-PAY-02', 'P-PAY-03']);
            $table->double('delivery_cost')->nullable();
            $table->foreignId('users_id')->constrained('users');
            $table->foreignId('suppliers_id')->constrained('suppliers');
            $table->timestamp('receive_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
