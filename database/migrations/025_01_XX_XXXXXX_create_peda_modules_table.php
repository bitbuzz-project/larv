<?php
// Create this migration file: database/migrations/2025_01_XX_XXXXXX_create_peda_modules_table.php

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
        Schema::create('peda_modules', function (Blueprint $table) {
            $table->id();
            $table->string('apogee', 20); // Student Apogee code
            $table->string('module_code', 20); // Module code (COD_ELP)
            $table->string('module_name', 200); // Module name
            $table->string('module_name_ar', 200)->nullable(); // Arabic module name
            $table->integer('credits')->default(0); // ECTS credits
            $table->decimal('coefficient', 5, 2)->default(1.00); // Module coefficient
            $table->enum('semester', ['S1', 'S2', 'S3', 'S4', 'S5', 'S6'])->default('S1'); // Semester
            $table->string('annee_scolaire', 20)->default('2024-2025'); // Academic year
            $table->enum('status', ['active', 'completed', 'failed', 'withdrawn'])->default('active'); // Status
            $table->string('professor', 100)->nullable(); // Professor name
            $table->text('schedule')->nullable(); // Class schedule
            $table->enum('session_type', ['printemps', 'automne'])->nullable(); // For current session imports
            $table->timestamps();

            // Indexes for better performance
            $table->index(['apogee']);
            $table->index(['module_code']);
            $table->index(['semester']);
            $table->index(['annee_scolaire']);
            $table->index(['status']);
            $table->index(['session_type']);

            // Composite index for uniqueness check
            $table->index(['apogee', 'module_code', 'annee_scolaire']);

            // Foreign key constraint (optional - only if students_base table exists)
            // $table->foreign('apogee')->references('apoL_a01_code')->on('students_base')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peda_modules');
    }
};
?>
