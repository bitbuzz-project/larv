<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Ods;

class NoteController extends Controller
{
    /**
     * Show the import form
     */
    public function showImport()
    {
        return view('admin.notes.import');
    }

    /**
     * Optimized CSV import method
     */
    public function importCsv(Request $request)
    {
        // More conservative memory settings for CSV
        ini_set('memory_limit', '512M');
        set_time_limit(600);

        try {
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'max:102400', // 100MB max
                    'mimes:csv,txt',
                ],
                'import_type' => 'required|in:old_session,current_session',
                'session_type' => 'required_if:import_type,current_session|in:printemps,automne',
                'result_type' => 'required_if:import_type,current_session|in:normale,rattrapage',
                'annee_scolaire' => 'required|string|max:20',
                'delimiter' => 'nullable|in:comma,semicolon,tab',
                'encoding' => 'nullable|in:utf8,latin1',
            ]);

            $file = $request->file('file');
            $importType = $request->input('import_type');
            $sessionType = $request->input('session_type');
            $resultType = $request->input('result_type');
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

            // Determine table name
            $tableName = $importType === 'current_session' ? 'notes_actu' : 'notes';

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

                // Map headers
                $expectedHeaders = ['apol_a01_code', 'code_module', 'nom_module', 'note'];
                $headerMap = [];

                foreach ($headers as $index => $header) {
                    $cleanHeader = strtolower(trim($header));
                    if (in_array($cleanHeader, $expectedHeaders)) {
                        $headerMap[$cleanHeader] = $index;
                    }
                }

                // Validate required headers
                foreach ($expectedHeaders as $requiredHeader) {
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

                        $noteData = [
                            'apoL_a01_code' => isset($headerMap['apol_a01_code']) ? trim($row[$headerMap['apol_a01_code']]) : '',
                            'code_module' => isset($headerMap['code_module']) ? trim($row[$headerMap['code_module']]) : '',
                            'nom_module' => isset($headerMap['nom_module']) ? trim($row[$headerMap['nom_module']]) : '',
                            'note' => isset($headerMap['note']) ? $row[$headerMap['note']] : null,
                        ];

                        // Validation
                        if (empty($noteData['apoL_a01_code'])) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'code' => 'N/A',
                                'message' => 'Code Apogée manquant',
                                'type' => 'validation'
                            ];
                            $lineNumber++;
                            continue;
                        }

                        if (empty($noteData['code_module'])) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'code' => $noteData['apoL_a01_code'],
                                'message' => 'Code module manquant',
                                'type' => 'validation'
                            ];
                            $lineNumber++;
                            continue;
                        }

                        if (!isset($existingStudentCodes[$noteData['apoL_a01_code']])) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'code' => $noteData['apoL_a01_code'],
                                'message' => 'Étudiant non trouvé',
                                'type' => 'foreign_key'
                            ];
                            $skipped++;
                            $lineNumber++;
                            continue;
                        }

                        // Prepare data for batch insert
                        $insertData = [
                            'apoL_a01_code' => $noteData['apoL_a01_code'],
                            'code_module' => $noteData['code_module'],
                            'nom_module' => $noteData['nom_module'],
                            'note' => is_numeric($noteData['note']) ? (float)$noteData['note'] : null,
                            'annee_scolaire' => $anneeScolaire,
                            'is_current_session' => $importType === 'current_session',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if ($importType === 'current_session') {
                            $insertData['session_type'] = $sessionType;
                            $insertData['result_type'] = $resultType;
                        }

                        $batchData[] = $insertData;

                        // Process batch when it reaches the batch size
                        if (count($batchData) >= $batchSize) {
                            $result = $this->processBatch($batchData, $tableName, $importType, $sessionType, $resultType, $anneeScolaire);
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
                            'code' => $noteData['apoL_a01_code'] ?? 'N/A',
                            'message' => 'Erreur ligne: ' . $e->getMessage(),
                            'type' => 'processing'
                        ];
                    }

                    $lineNumber++;
                }

                // Process remaining batch
                if (!empty($batchData)) {
                    $result = $this->processBatch($batchData, $tableName, $importType, $sessionType, $resultType, $anneeScolaire);
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
                    'result_type' => $resultType,
                    'annee_scolaire' => $anneeScolaire,
                ],
                'import_errors' => $errors
            ]);

            if ($imported > 0) {
                $message = "Import CSV réussi! {$imported} notes importées";
                if ($skipped > 0) {
                    $message .= ", {$skipped} ignorées";
                }
                if (count($errors) > 0) {
                    $message .= ", " . count($errors) . " erreurs";
                }
                return response()->json(['message' => $message, 'redirect' => route('admin.notes.import.results')], 200);
            } else {
                return response()->json(['message' => 'Aucune note importée. Vérifiez les erreurs.', 'errors' => $errors], 400);
            }

        } catch (\Exception $e) {
            Log::error('CSV import error: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process a batch of records
     */
    private function processBatch($batchData, $tableName, $importType, $sessionType, $resultType, $anneeScolaire)
    {
        $inserted = 0;
        $skipped = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            // Simple duplicate check - check existing records
            $newRecords = [];
            foreach ($batchData as $record) {
                $query = DB::table($tableName)
                    ->where('apoL_a01_code', $record['apoL_a01_code'])
                    ->where('code_module', $record['code_module'])
                    ->where('annee_scolaire', $anneeScolaire);

                if ($importType === 'current_session') {
                    $query->where('session_type', $sessionType)
                          ->where('result_type', $resultType);
                }

                if ($query->exists()) {
                    $skipped++;
                } else {
                    $newRecords[] = $record;
                }
            }

            // Insert new records
            if (!empty($newRecords)) {
                DB::table($tableName)->insert($newRecords);
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
     * Handle ODS file import with chunked processing
     */
    public function import(Request $request)
    {
        // Increase memory and time limits for large files
        ini_set('memory_limit', '1024M'); // Increase to 1GB
        set_time_limit(600); // 10 minutes

        try {
            $request->validate([
                'ods_file' => [
                    'required',
                    'file',
                    'max:102400', // Increase to 100MB max
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $extension = strtolower($value->getClientOriginalExtension());
                            $mimeType = $value->getClientMimeType();

                            if (!in_array($extension, ['ods']) && !in_array($mimeType, ['application/vnd.oasis.opendocument.spreadsheet'])) {
                                $fail('Le fichier doit être un fichier ODS valide.');
                                return;
                            }
                        }
                    },
                ],
                'import_type' => 'required|in:old_session,current_session',
                'session_type' => 'required_if:import_type,current_session|in:printemps,automne',
                'result_type' => 'required_if:import_type,current_session|in:normale,rattrapage',
                'annee_scolaire' => 'required|string|max:20',
                'chunk_size' => 'nullable|integer|min:50|max:1000'
            ]);

            $file = $request->file('file'); // Changed from 'ods_file' to 'file'
            $importType = $request->input('import_type');
            $sessionType = $request->input('session_type');
            $resultType = $request->input('result_type');
            $anneeScolaire = $request->input('annee_scolaire');
            $chunkSize = $request->input('chunk_size', 200); // Default chunk size

            // Load ODS file with memory optimization
            try {
                $reader = new Ods();
                $reader->setReadDataOnly(true); // Only read data, not formatting
                $reader->setReadEmptyCells(false); // Skip empty cells

                $spreadsheet = $reader->load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();

                // Get the highest row and column
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();

                Log::info("ODS file loaded: {$highestRow} rows, highest column: {$highestColumn}");

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Erreur lors de la lecture du fichier ODS: ' . $e->getMessage(),
                    'errors' => ['ods_file' => ['Le fichier ODS ne peut pas être lu.']]
                ], 400);
            }

            if ($highestRow <= 1) {
                return response()->json([
                    'message' => 'Le fichier ODS est vide ou ne contient que les en-têtes.',
                    'errors' => ['ods_file' => ['Le fichier ne contient aucune donnée.']]
                ], 400);
            }

            // Read headers first
            $headerRow = $worksheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false)[0];

            // Map headers to expected columns with flexible matching
            $expectedHeaders = [
                'apoL_a01_code' => ['apol_a01_code', 'apoL_a01_code', 'cod_etu', 'code_etudiant', 'apogee'],
                'code_module' => ['code_module', 'cod_module', 'module_code'],
                'nom_module' => ['nom_module', 'lib_module', 'module_name', 'libelle_module'],
                'note' => ['note', 'grade', 'resultat']
            ];

            $headerMap = [];

            foreach ($headerRow as $index => $header) {
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
            $requiredHeaders = ['apoL_a01_code', 'code_module', 'nom_module', 'note'];
            foreach ($requiredHeaders as $requiredHeader) {
                if (!isset($headerMap[$requiredHeader])) {
                    $availableHeaders = implode(', ', $headerRow);
                    return response()->json([
                        'message' => "Colonne manquante: {$requiredHeader}. Colonnes disponibles: {$availableHeaders}",
                        'errors' => ['file' => ["La colonne '{$requiredHeader}' est requise."]]
                    ], 400);
                }
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $totalRows = $highestRow - 1; // Exclude header row

            // Get existing student codes for validation (only once)
            $existingStudentCodes = Student::pluck('apoL_a01_code')->flip()->toArray();

            // Process data in chunks to avoid memory issues
            for ($startRow = 2; $startRow <= $highestRow; $startRow += $chunkSize) {
                $endRow = min($startRow + $chunkSize - 1, $highestRow);

                try {
                    // Read chunk of data
                    $range = 'A' . $startRow . ':' . $highestColumn . $endRow;
                    $chunkData = $worksheet->rangeToArray($range, null, true, false);

                    Log::info("Processing chunk: rows {$startRow} to {$endRow}");

                    DB::beginTransaction();

                    $insertBatch = [];

                    foreach ($chunkData as $rowIndex => $row) {
                        $lineNumber = $startRow + $rowIndex;

                        try {
                            // Skip empty rows
                            if (empty(array_filter($row))) {
                                continue;
                            }

                            $noteData = [
                                'apoL_a01_code' => isset($headerMap['apoL_a01_code']) ? trim($row[$headerMap['apoL_a01_code']]) : '',
                                'code_module' => isset($headerMap['code_module']) ? trim($row[$headerMap['code_module']]) : '',
                                'nom_module' => isset($headerMap['nom_module']) ? trim($row[$headerMap['nom_module']]) : '',
                                'note' => isset($headerMap['note']) ? $row[$headerMap['note']] : null,
                            ];

                            // Validation
                            if (empty($noteData['apoL_a01_code'])) {
                                $errors[] = [
                                    'line' => $lineNumber,
                                    'code' => 'N/A',
                                    'message' => 'Code Apogée manquant',
                                    'type' => 'validation'
                                ];
                                continue;
                            }

                            if (empty($noteData['code_module'])) {
                                $errors[] = [
                                    'line' => $lineNumber,
                                    'code' => $noteData['apoL_a01_code'],
                                    'message' => 'Code module manquant',
                                    'type' => 'validation'
                                ];
                                continue;
                            }

                            if (!isset($existingStudentCodes[$noteData['apoL_a01_code']])) {
                                $errors[] = [
                                    'line' => $lineNumber,
                                    'code' => $noteData['apoL_a01_code'],
                                    'message' => 'Étudiant non trouvé dans la base de données',
                                    'type' => 'foreign_key'
                                ];
                                $skipped++;
                                continue;
                            }

                            // Prepare note data for insertion
                            $noteInsertData = [
                                'apoL_a01_code' => $noteData['apoL_a01_code'],
                                'code_module' => $noteData['code_module'],
                                'nom_module' => $noteData['nom_module'],
                                'note' => is_numeric($noteData['note']) ? (float)$noteData['note'] : null,
                                'annee_scolaire' => $anneeScolaire,
                                'is_current_session' => $importType === 'current_session',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            // Add session and result type for current session
                            if ($importType === 'current_session') {
                                $noteInsertData['session_type'] = $sessionType;
                                $noteInsertData['result_type'] = $resultType;
                                $tableName = 'notes_actu';
                            } else {
                                $tableName = 'notes';
                            }

                            // Check for duplicates (only in current chunk processing)
                            $duplicateKey = $noteData['apoL_a01_code'] . '|' . $noteData['code_module'] . '|' . $anneeScolaire;
                            if ($importType === 'current_session') {
                                $duplicateKey .= '|' . $sessionType . '|' . $resultType;
                            }

                            // Quick duplicate check in database
                            $existingQuery = DB::table($tableName)
                                ->where('apoL_a01_code', $noteData['apoL_a01_code'])
                                ->where('code_module', $noteData['code_module'])
                                ->where('annee_scolaire', $anneeScolaire);

                            if ($importType === 'current_session') {
                                $existingQuery->where('session_type', $sessionType)
                                             ->where('result_type', $resultType);
                            }

                            if ($existingQuery->exists()) {
                                $skipped++;
                                continue;
                            }

                            $insertBatch[] = $noteInsertData;

                        } catch (\Exception $e) {
                            $errors[] = [
                                'line' => $lineNumber,
                                'code' => $noteData['apoL_a01_code'] ?? 'N/A',
                                'message' => 'Erreur lors du traitement: ' . $e->getMessage(),
                                'type' => 'processing'
                            ];
                        }
                    }

                    // Batch insert for better performance
                    if (!empty($insertBatch)) {
                        DB::table($tableName)->insert($insertBatch);
                        $imported += count($insertBatch);
                    }

                    DB::commit();

                    // Clear memory
                    unset($chunkData);
                    unset($insertBatch);

                    // Force garbage collection
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }

                } catch (\Exception $e) {
                    DB::rollback();
                    Log::error("Error processing chunk {$startRow}-{$endRow}: " . $e->getMessage());
                    $errors[] = [
                        'line' => "Chunk {$startRow}-{$endRow}",
                        'code' => 'N/A',
                        'message' => 'Erreur lors du traitement du chunk: ' . $e->getMessage(),
                        'type' => 'chunk_error'
                    ];
                }
            }

            // Clean up spreadsheet object
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            // Store import statistics in session
            session([
                'import_stats' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'total' => $totalRows,
                    'success_rate' => $totalRows > 0 ? ($imported / $totalRows) * 100 : 0,
                    'import_type' => $importType,
                    'session_type' => $sessionType,
                    'result_type' => $resultType,
                    'annee_scolaire' => $anneeScolaire,
                ],
                'import_errors' => $errors
            ]);

            if ($imported > 0) {
                $message = "Import réussi! {$imported} notes importées";
                if ($skipped > 0) {
                    $message .= ", {$skipped} ignorées (déjà existantes)";
                }
                if (count($errors) > 0) {
                    $message .= ", " . count($errors) . " erreurs";
                }
                return response()->json(['message' => $message, 'redirect' => route('admin.notes.import.results')], 200);
            } else {
                $errorMessage = 'Aucune note n\'a pu être importée. Vérifiez les erreurs.';
                return response()->json(['message' => $errorMessage, 'errors' => $errors], 400);
            }

        } catch (\Exception $e) {
            Log::error('Note import error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json(['message' => 'Erreur serveur lors de l\'importation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show import results
     */
    public function importResults()
    {
        if (!session()->has('import_stats')) {
            return redirect()->route('admin.notes.import')
                           ->with('error', 'Aucun résultat d\'import trouvé.');
        }

        return view('admin.notes.import-results');
    }
}
