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
            $table->string('default_name', 200);
            $table->string('note', 100)->nullable();
            $table->string('prof', 100)->nullable();
            $table->string('groupe', 10)->nullable();
            $table->string('class', 50)->nullable();
            $table->text('info')->nullable();
            $table->string('Semestre', 10)->nullable();
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])->default('pending');
            $table->enum('reclamation_type', ['notes', 'correction', 'autre'])->default('notes');
            $table->string('category', 100)->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('admin_comment')->nullable();
            $table->string('session_type', 50)->nullable();
            $table->string('result_type', 50)->nullable();
            $table->timestamps();

            $table->index(['apoL_a01_code']);
            $table->index(['status']);
            $table->index(['reclamation_type']);
            $table->index(['category']);
            $table->index(['priority']);
            $table->index(['created_at']);
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