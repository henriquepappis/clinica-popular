<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiting_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignUuid('specialty_id')->constrained('specialties')->onDelete('cascade');
            $table->integer('priority')->default(1); // 1 = baixa, 2 = média, 3 = alta
            $table->string('status')->default('waiting'); // waiting, notified, cancelled
            $table->text('reason')->nullable();
            $table->timestamp('added_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('patient_id');
            $table->index('specialty_id');
            $table->index('status');
            $table->index('priority');
            $table->unique(['patient_id', 'specialty_id']); // Um paciente por especialidade na fila
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_lists');
    }
};
