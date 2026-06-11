<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors')->onDelete('cascade');
            $table->foreignUuid('specialty_id')->nullable()->constrained('specialties')->onDelete('cascade');
            $table->integer('duration_minutes')->nullable();
            $table->decimal('value', 10, 2);
            $table->timestamps();

            // Indexes
            $table->index('specialty_id');
            $table->index('duration_minutes');
            $table->unique(['doctor_id', 'specialty_id', 'duration_minutes']); // Config única por combinação
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
