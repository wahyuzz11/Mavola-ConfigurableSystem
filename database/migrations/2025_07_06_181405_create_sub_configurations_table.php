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
        Schema::create('sub_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 45);
            $table->string('name', 45);
            $table->enum('types', ['mandatory', 'non-mandatory']);
            $table->tinyInteger('status');
            $table->integer('value')->nullable();
            $table->foreignId('configurations_id')->constrained('configurations');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_configurations');
    }
};
