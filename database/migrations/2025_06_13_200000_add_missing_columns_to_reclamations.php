<?php
// Create this file: database/migrations/2025_06_13_200000_add_missing_columns_to_reclamations.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            if (!Schema::hasColumn('reclamations', 'status')) {
                $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])->default('pending');
            }
            if (!Schema::hasColumn('reclamations', 'reclamation_type')) {
                $table->enum('reclamation_type', ['notes', 'correction', 'autre'])->default('notes');
            }
            if (!Schema::hasColumn('reclamations', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            }
            if (!Schema::hasColumn('reclamations', 'admin_comment')) {
                $table->text('admin_comment')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->dropColumn(['status', 'reclamation_type', 'priority', 'admin_comment']);
        });
    }
};
