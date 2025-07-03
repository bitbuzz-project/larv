<?php
// Create this migration: database/migrations/2025_01_XX_XXXXXX_create_notes_table.php

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
        // Create notes table for old sessions
        if (!Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->id();
                $table->string('apoL_a01_code');
                $table->string('code_module');
                $table->string('nom_module');
                $table->decimal('note', 5, 2)->nullable();
                $table->string('annee_scolaire');
                $table->boolean('is_current_session')->default(false);
                $table->timestamps();

                $table->index(['apoL_a01_code', 'code_module', 'annee_scolaire']);
            });
        }

        // Make sure notes_actu table exists for current sessions
        if (!Schema::hasTable('notes_actu')) {
            Schema::create('notes_actu', function (Blueprint $table) {
                $table->id();
                $table->string('apoL_a01_code');
                $table->string('code_module');
                $table->string('nom_module');
                $table->decimal('note', 5, 2)->nullable();
                $table->enum('session_type', ['printemps', 'automne']);
                $table->enum('result_type', ['normale', 'rattrapage']);
                $table->string('annee_scolaire');
                $table->boolean('is_current_session')->default(true);
                $table->timestamps();

                $table->index(['apoL_a01_code', 'code_module', 'session_type', 'result_type'], 'notes_actu_composite_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
        Schema::dropIfExists('notes_actu');
    }
};
?>
