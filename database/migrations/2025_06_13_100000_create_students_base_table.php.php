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
        Schema::create('students_base', function (Blueprint $table) {
            $table->string('apoL_a01_code', 20)->primary();
            $table->string('apoL_a02_nom', 100);
            $table->string('apoL_a03_prenom', 100);
            $table->string('apoL_a04_naissance', 20)->nullable();
            $table->string('cod_etu', 20)->nullable();
            $table->string('cod_etp', 20)->nullable();
            $table->string('cod_anu', 10)->nullable();
            $table->string('cod_dip', 20)->nullable();
            $table->string('cod_sex_etu', 5)->nullable();
            $table->string('lib_vil_nai_etu', 100)->nullable();
            $table->string('cin_ind', 20)->nullable();
            $table->string('lib_etp', 200)->nullable();
            $table->string('lic_etp', 200)->nullable();
            $table->timestamps();

            $table->index(['cod_etu']);
            $table->index(['cin_ind']);
            $table->index(['cod_etp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students_base');
    }
};