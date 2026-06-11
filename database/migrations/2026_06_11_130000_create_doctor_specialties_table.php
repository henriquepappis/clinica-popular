<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_specialties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignUuid('specialty_id')->constrained('specialties')->onDelete('cascade');

            // Indexes
            $table->index('doctor_id');
            $table->index('specialty_id');
            $table->unique(['doctor_id', 'specialty_id']);
        });

        // Migrar dados existentes: specialty_id único do médico vira linha no pivot
        DB::table('doctors')->whereNotNull('specialty_id')->orderBy('id')
            ->each(function ($doctor) {
                DB::table('doctor_specialties')->insert([
                    'id' => (string) Str::uuid(),
                    'doctor_id' => $doctor->id,
                    'specialty_id' => $doctor->specialty_id,
                ]);
            });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('specialty_id');
        });
    }

    public function down(): void
    {
        // Nullable pois médicos podem ter mais de uma especialidade no pivot;
        // o rollback restaura apenas a primeira
        Schema::table('doctors', function (Blueprint $table) {
            $table->foreignUuid('specialty_id')->nullable()->constrained('specialties')->onDelete('cascade');
            $table->index('specialty_id');
        });

        DB::table('doctor_specialties')->orderBy('id')->each(function ($pivot) {
            DB::table('doctors')
                ->where('id', $pivot->doctor_id)
                ->whereNull('specialty_id')
                ->update(['specialty_id' => $pivot->specialty_id]);
        });

        Schema::dropIfExists('doctor_specialties');
    }
};
