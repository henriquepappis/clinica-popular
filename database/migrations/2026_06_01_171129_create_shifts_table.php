<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // ex: "Manhã", "Tarde"
            $table->string('period'); // MORNING, AFTERNOON, EVENING
            $table->time('start_time'); // hora de início
            $table->time('end_time'); // hora de término
            $table->integer('max_appointments')->default(20); // capacidade máxima
            $table->string('status')->default('active');
            $table->timestamps();
            
            // Indexes
            $table->index('period');
            $table->index('status');
            $table->unique(['period', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};