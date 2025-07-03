<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add session_type column if it doesn't exist
        if (!Schema::hasColumn('peda_modules', 'session_type')) {
            Schema::table('peda_modules', function (Blueprint $table) {
                $table->string('session_type', 20)->nullable()->after('schedule');
            });
        }

        // Update existing modules with correct semester information from modules table
        $this->updateSemestersFromModulesTable();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data migration, so we don't reverse the semester updates
        // Just remove the session_type column if it was added
        if (Schema::hasColumn('peda_modules', 'session_type')) {
            Schema::table('peda_modules', function (Blueprint $table) {
                $table->dropColumn('session_type');
            });
        }
    }

    /**
     * Update semester information based on modules table cod_pel
     */
    private function updateSemestersFromModulesTable()
    {
        // Get all peda_modules that need semester updates
        $pedaModules = DB::table('peda_modules')
            ->leftJoin('modules', 'peda_modules.module_code', '=', 'modules.cod_elp')
            ->select('peda_modules.id', 'peda_modules.module_code', 'peda_modules.semester', 'modules.cod_pel')
            ->get();

        foreach ($pedaModules as $pedaModule) {
            if ($pedaModule->cod_pel) {
                $determinedSemester = $this->determineSemesterFromCodPel($pedaModule->cod_pel);

                // Update if different from current semester
                if ($determinedSemester !== $pedaModule->semester) {
                    DB::table('peda_modules')
                        ->where('id', $pedaModule->id)
                        ->update(['semester' => $determinedSemester]);
                }
            }
        }

        echo "Updated semester information for peda_modules based on modules.cod_pel\n";
    }

    /**
     * Determine semester from cod_pel
     */
    private function determineSemesterFromCodPel($codPel)
    {
        if (!$codPel) return 'S1';

        // Convert to uppercase for consistent matching
        $codPel = strtoupper($codPel);

        // Direct semester patterns
        if (str_contains($codPel, 'S1') || preg_match('/\b1\b/', $codPel)) {
            return 'S1';
        } elseif (str_contains($codPel, 'S2') || preg_match('/\b2\b/', $codPel)) {
            return 'S2';
        } elseif (str_contains($codPel, 'S3') || preg_match('/\b3\b/', $codPel)) {
            return 'S3';
        } elseif (str_contains($codPel, 'S4') || preg_match('/\b4\b/', $codPel)) {
            return 'S4';
        } elseif (str_contains($codPel, 'S5') || preg_match('/\b5\b/', $codPel)) {
            return 'S5';
        } elseif (str_contains($codPel, 'S6') || preg_match('/\b6\b/', $codPel)) {
            return 'S6';
        }

        // More complex patterns
        if (preg_match('/SEM(\d+)|SEMESTER(\d+)|(\d+)EM/i', $codPel, $matches)) {
            $semNumber = $matches[1] ?: $matches[2] ?: $matches[3];
            if ($semNumber >= 1 && $semNumber <= 6) {
                return 'S' . $semNumber;
            }
        }

        // Extract any number between 1-6
        if (preg_match('/[^\d]*(\d+)[^\d]*/', $codPel, $matches)) {
            $number = intval($matches[1]);
            if ($number >= 1 && $number <= 6) {
                return 'S' . $number;
            }
        }

        // Default fallback
        return 'S1';
    }
};
