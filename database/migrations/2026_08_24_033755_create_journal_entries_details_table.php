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
        Schema::create('journal_entries_details', function (Blueprint $table) {
            $table->id();
            $table->integer('debit');
            $table->integer('credit');
            $table->foreignId('accounts_id')->references('id')->on('accounts');
            $table->foreignId('journal_entries_id')->references('id')->on('journal_entries');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries_details');
    }
};
