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
        Schema::create('reclamations', function (Blueprint $table) {
            $table->id();
            $table->string('apoL_a01_code', 20);
            $table->string('default_name')->nullable();
            $table->decimal('note', 5, 2)->nullable();
            $table->string('prof')->nullable();
            $table->string('groupe')->nullable();
            $table->string('class')->nullable();
            $table->text('info')->nullable();
            $table->string('Semestre')->nullable();
            $table->string('category')->nullable();
            $table->string('session_type')->nullable();
            $table->string('result_type')->nullable();
            $table->timestamps();

            // Index for foreign key
            $table->index(['apoL_a01_code']);

            // Foreign key constraint (optional - uncomment if needed)
            // $table->foreign('apoL_a01_code')->references('apoL_a01_code')->on('students_base')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
