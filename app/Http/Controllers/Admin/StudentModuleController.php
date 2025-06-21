<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedaModule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class StudentModuleController extends Controller
{
    /**
     * Show the form for importing student modules
     */
    public function showImport()
    {
        return view('admin.student-modules.import');
    }

    /**
     * Handle CSV file import for student module inscriptions
     */
    public function import(Request $request)
    {
        // Increase execution limits for large imports
        set_time_limit(300); // 5 minutes
        ini_set('memory_limit', '512M'); // 512MB memory

        try {
            $request->validate([
                'csv_file' => [
                    'required',
                    'file',
                    'max:51200', // 50MB max
                    'mimes:csv,txt',
                ],
                'import_type' => 'required|in:current_session,historical',
                'session_type' => 'required_if:import_type,current_session|in:printemps,automne',
                'annee_scolaire' => 'required|string|max:20',
                'delimiter' => 'nullable|in:comma,semicolon,tab',
                'encoding' => 'nullable|in:utf8,latin1',
            ]);

            $file = $request->file('csv_file');
            $importType = $request->input('import_type');
            $sessionType = $request->input('session_type');
            $anneeScolaire = $request->input('annee_scolaire');

            // CSV options
            $delimiter = match($request->input('delimiter', 'comma')) {
                'semicolon' => ';',
                'tab' => "\t",
                default => ','
            };
            $encoding = $request->input('encoding', 'utf8');

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $lineNumber = 1;
            $batchSize = 1000;
            $batchData = [];

            // Get existing student codes once
            $existingStudentCodes = Student::pluck('apoL_a01_code')->flip()->toArray();

            // Open and read CSV file line by line
            if (($handle = fopen($file->getPathname(), 'r')) !== FALSE) {

                // Read header row
                $headers = fgetcsv($handle, 0, $delimiter);
                if (!$headers) {
                    throw new \Exception('Impossible de lire les en-têtes du fichier CSV.');
                }

                // Convert encoding if needed
                if ($encoding === 'latin1') {
                    $headers = array_map(function($header) {
                        return mb_convert_encoding($header, 'UTF-8', 'ISO-8859-1');
                    }, $headers);
                }

                // Map headers with flexible matching
                $expectedHeaders = [
                    'apoL_a01_code' => ['apol_a01_code', 'apoL_a01_code', 'cod_etu', 'code_etudiant', 'apogee'],
                    'code_module' => ['code_module', 'cod_module', 'module_code'],
                    'module' => ['module', 'nom_module', 'lib_module', 'module_name', 'libelle_module']
                ];

                $headerMap = [];
                foreach ($headers as $index => $header) {
                    $cleanHeader = strtolower(trim($header));

                    // Check each expected header and its variants
                    foreach ($expectedHeaders as $expectedKey => $variants) {
                        if (in_array($cleanHeader, $variants)) {
                            $headerMap[$expectedKey] = $index;
                            break;
                        }
                    }
                }

                // Validate required headers
                $requiredHeaders = ['apoL_a01_code', 'code_module', 'module'];
                foreach ($requiredHeaders as $requiredHeader) {
                    if (!isset($headerMap[$requiredHeader])) {
                        throw new \Exception("Colonne manquante: {$requiredHeader}");
                    }
                }

                $lineNumber = 2;

                // Read data line by line
                while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {

                    try {
                        // Convert encoding if needed
                        if ($encoding === 'latin1') {
                            $row = array_map(function($cell) {
                                return mb_convert_encoding($cell, 'UTF-8', 'ISO-8859-1');
                            }, $row);
                        }

                        // Skip empty rows
                        if (empty(array_filter($row))) {
                            $lineNumber++;
                            continue;
                        }

                        $moduleData = [
                            'apoL_a01_code' => isset($headerMap['apoL_a01_code']) ? trim($row[$headerMap['apoL_a01_code']]) : '',
                            'code_module' => isset($headerMap['code_module']) ? trim($row[$headerMap['code_module']]) : '',
                            'module' => isset($headerMap['module']) ? trim($row[$headerMap['module']]) : '',
                        ];

                        // Validation
                        if (empty($moduleData['apoL_a01_code'])) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'code' => 'N/A',
                                'message' => 'Code Apogée manquant',
                                'type' => 'validation'
                            ];
                            $lineNumber++;
                            continue;
                        }

                        if (empty($moduleData['code_module'])) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'code' => $moduleData['apoL_a01_code'],
                                'message' => 'Code module manquant',
                                'type' => 'validation'
                            ];
                            $lineNumber++;
                            continue;
                        }

                        if (empty($moduleData['module'])) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'code' => $moduleData['apoL_a01_code'],
                                'message' => 'Nom du module manquant',
                                'type' => 'validation'
                            ];
                            $lineNumber++;
                            continue;
                        }

                        if (!isset($existingStudentCodes[$moduleData['apoL_a01_code']])) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'code' => $moduleData['apoL_a01_code'],
                                'message' => 'Étudiant non trouvé',
                                'type' => 'foreign_key'
                            ];
                            $skipped++;
                            $lineNumber++;
                            continue;
                        }

                        // Prepare data for batch insert
                        $insertData = [
                            'apogee' => $moduleData['apoL_a01_code'],
                            'module_code' => $moduleData['code_module'],
                            'module_name' => $moduleData['module'],
                            'module_name_ar' => null,
                            'credits' => 0,
                            'coefficient' => 1.00,
                            'semester' => 'S1', // Default, can be updated later
                            'annee_scolaire' => $anneeScolaire,
                            'status' => $importType === 'current_session' ? 'active' : 'completed',
                            'professor' => null,
                            'schedule' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Add session type for current session
                        if ($importType === 'current_session') {
                            $insertData['session_type'] = $sessionType;
                        }

                        $batchData[] = $insertData;

                        // Process batch when it reaches the batch size
                        if (count($batchData) >= $batchSize) {
                            $result = $this->processBatch($batchData, $importType, $sessionType, $anneeScolaire);
                            $imported += $result['inserted'];
                            $skipped += $result['skipped'];
                            $errors = array_merge($errors, $result['errors']);

                            $batchData = [];

                            if (function_exists('gc_collect_cycles')) {
                                gc_collect_cycles();
                            }
                        }

                    } catch (\Exception $e) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => $moduleData['apoL_a01_code'] ?? 'N/A',
                            'message' => 'Erreur ligne: ' . $e->getMessage(),
                            'type' => 'processing'
                        ];
                    }

                    $lineNumber++;
                }

                // Process remaining batch
                if (!empty($batchData)) {
                    $result = $this->processBatch($batchData, $importType, $sessionType, $anneeScolaire);
                    $imported += $result['inserted'];
                    $skipped += $result['skipped'];
                    $errors = array_merge($errors, $result['errors']);
                }

                fclose($handle);

            } else {
                throw new \Exception('Impossible d\'ouvrir le fichier CSV.');
            }

            $totalProcessed = $lineNumber - 2;

            // Store results
            session([
                'import_stats' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'total' => $totalProcessed,
                    'success_rate' => $totalProcessed > 0 ? ($imported / $totalProcessed) * 100 : 0,
                    'import_type' => $importType,
                    'session_type' => $sessionType,
                    'annee_scolaire' => $anneeScolaire,
                ],
                'import_errors' => $errors
            ]);

            if ($imported > 0) {
                $message = "Import CSV réussi! {$imported} inscriptions importées";
                if ($skipped > 0) {
                    $message .= ", {$skipped} ignorées";
                }
                if (count($errors) > 0) {
                    $message .= ", " . count($errors) . " erreurs";
                }
                return response()->json(['message' => $message, 'redirect' => route('admin.student-modules.import.results')], 200);
            } else {
                return response()->json(['message' => 'Aucune inscription importée. Vérifiez les erreurs.', 'errors' => $errors], 400);
            }

        } catch (\Exception $e) {
            Log::error('CSV import error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process a batch of records
     */
    private function processBatch($batchData, $importType, $sessionType, $anneeScolaire)
    {
        $inserted = 0;
        $skipped = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            // Simple duplicate check - check existing records
            $newRecords = [];
            foreach ($batchData as $record) {
                $query = DB::table('peda_modules')
                    ->where('apogee', $record['apogee'])
                    ->where('module_code', $record['module_code'])
                    ->where('annee_scolaire', $anneeScolaire);

                if ($importType === 'current_session') {
                    $query->where('session_type', $sessionType);
                }

                if ($query->exists()) {
                    $skipped++;
                } else {
                    $newRecords[] = $record;
                }
            }

            // Insert new records
            if (!empty($newRecords)) {
                DB::table('peda_modules')->insert($newRecords);
                $inserted = count($newRecords);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            $errors[] = [
                'line' => 'Batch',
                'code' => 'N/A',
                'message' => 'Erreur batch: ' . $e->getMessage(),
                'type' => 'batch_error'
            ];
        }

        return [
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Show import results
     */
    public function importResults()
    {
        if (!session()->has('import_stats')) {
            return redirect()->route('admin.student-modules.import')
                           ->with('error', 'Aucun résultat d\'import trouvé.');
        }

        return view('admin.student-modules.import-results');
    }
}
