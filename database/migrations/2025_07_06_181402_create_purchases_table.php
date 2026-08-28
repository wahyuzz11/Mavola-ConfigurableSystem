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
            $table->decimal('total_price',15,2);
            $table->dateTime('purchase_date');
            $table->enum('payment_method', ['P-PAY-01', 'P-PAY-02', 'P-PAY-03']);
            $table->decimal('delivery_cost',15,2)->nullable();
            $table->foreignId('users_id')->constrained('users');
            $table->foreignId('suppliers_id')->constrained('suppliers');
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
