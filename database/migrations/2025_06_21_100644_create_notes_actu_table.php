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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes_actu');
    }
};
