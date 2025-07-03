<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\PedaModule;
use App\Models\Module;

class UpdateModuleSemesters extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'modules:update-semesters {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Update module semesters based on cod_pel from modules table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Starting module semester update...');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get all peda_modules with their corresponding module details
        $pedaModules = DB::table('peda_modules')
            ->leftJoin('modules', 'peda_modules.module_code', '=', 'modules.cod_elp')
            ->select(
                'peda_modules.id',
                'peda_modules.module_code',
                'peda_modules.module_name',
                'peda_modules.semester as current_semester',
                'modules.cod_pel'
            )
            ->get();

        $updates = [];
        $stats = [
            'total' => $pedaModules->count(),
            'updated' => 0,
            'no_change' => 0,
            'no_cod_pel' => 0
        ];

        $this->info("Analyzing {$stats['total']} modules...");

        foreach ($pedaModules as $module) {
            if (!$module->cod_pel) {
                $stats['no_cod_pel']++;
                continue;
            }

            $determinedSemester = $this->determineSemesterFromCodPel($module->cod_pel);

            if ($determinedSemester !== $module->current_semester) {
                $updates[] = [
                    'id' => $module->id,
                    'module_code' => $module->module_code,
                    'module_name' => $module->module_name,
                    'current_semester' => $module->current_semester,
                    'new_semester' => $determinedSemester,
                    'cod_pel' => $module->cod_pel
                ];
                $stats['updated']++;
            } else {
                $stats['no_change']++;
            }
        }

        // Display results
        if (!empty($updates)) {
            $this->table(
                ['Module Code', 'Module Name', 'Current', 'New', 'cod_pel'],
                array_map(function ($update) {
                    return [
                        $update['module_code'],
                        substr($update['module_name'], 0, 30) . '...',
                        $update['current_semester'],
                        $update['new_semester'],
                        $update['cod_pel']
                    ];
                }, array_slice($updates, 0, 10)) // Show first 10 for preview
            );

            if (count($updates) > 10) {
                $this->info("... and " . (count($updates) - 10) . " more modules to update");
            }
        }

        $this->info("Statistics:");
        $this->line("- Total modules: {$stats['total']}");
        $this->line("- Modules to update: {$stats['updated']}");
        $this->line("- No change needed: {$stats['no_change']}");
        $this->line("- Missing cod_pel: {$stats['no_cod_pel']}");

        if ($isDryRun) {
            $this->warn('This was a dry run. Use without --dry-run to apply changes.');
            return 0;
        }

        if (empty($updates)) {
            $this->info('No updates needed!');
            return 0;
        }

        // Ask for confirmation
        if (!$this->confirm("Do you want to update {$stats['updated']} modules?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Perform updates
        $progressBar = $this->output->createProgressBar(count($updates));
        $progressBar->start();

        foreach ($updates as $update) {
            DB::table('peda_modules')
                ->where('id', $update['id'])
                ->update(['semester' => $update['new_semester']]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully updated {$stats['updated']} modules!");

        return 0;
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
}
