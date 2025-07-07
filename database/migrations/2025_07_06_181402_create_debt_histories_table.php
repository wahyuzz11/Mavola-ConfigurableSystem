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
        Schema::create('debt_histories', function (Blueprint $table) {
            $table->id();
            $table->double('debt_nominal');
            $table->dateTime('bill_date');
            $table->dateTime('due_date');
            $table->enum('status', ['pending', 'paid', 'late']);
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('purchases_id')->constrained('purchases');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_histories');
    }
};
