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
        Schema::create('administative', function (Blueprint $table) {
            $table->id();
            $table->string('apogee', 20);
            $table->string('filliere', 100);
            $table->string('annee_scolaire', 20)->default('2024-2025');
            $table->timestamps();

            $table->index(['apogee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administative');
    }
};