<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peda_modules', function (Blueprint $table) {
            $table->id();
            $table->string('apogee', 20);
            $table->string('module_code', 20);
            $table->string('module_name', 200);
            $table->string('module_name_ar', 200)->nullable();
            $table->integer('credits')->default(0);
            $table->decimal('coefficient', 5, 2)->default(1.00);
            $table->enum('semester', ['S1', 'S2', 'S3', 'S4', 'S5', 'S6'])->default('S1');
            $table->string('annee_scolaire', 20)->default('2024-2025');
            $table->enum('status', ['active', 'completed', 'failed', 'withdrawn'])->default('active');
            $table->string('professor', 100)->nullable();
            $table->text('schedule')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['apogee']);
            $table->index(['semester']);
            $table->index(['annee_scolaire']);
            $table->index(['status']);

            // Foreign key constraint
            $table->foreign('apogee')->references('apoL_a01_code')->on('students_base')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peda_modules');
    }
};
