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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
