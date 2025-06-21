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
     * Handle both initial import and chunk processing
     */
    public function import(Request $request)
    {
        // Check if this is a chunk processing request
        if ($request->has('action') && $request->input('action') === 'process_chunk') {
            return $this->processChunk($request->input('import_id'));
        }

        // This is an initial file import
        return $this->handleFileImport($request);
    }

    /**
     * Handle initial file upload and processing
     */
    private function handleFileImport(Request $request)
    {
        try {
            $request->validate([
                'json_file' => [
                    'required',
                    'file',
                    'max:102400', // 100MB max
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $extension = strtolower($value->getClientOriginalExtension());
                            $mimeType = $value->getClientMimeType();

                            if ($extension !== 'json' && $mimeType !== 'application/json') {
                                $fail('Le fichier doit être un fichier JSON valide.');
                                return;
                            }
                        }
                    },
                ],
                'chunk_size' => 'nullable|integer|min:10|max:1000'
            ]);

            $file = $request->file('json_file');
            $chunkSize = $request->input('chunk_size', 50);

            // Read and parse JSON directly
            $jsonContent = file_get_contents($file->getPathname());
            $jsonData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'message' => 'Le fichier JSON est invalide.',
                    'errors' => ['json_file' => ['Le fichier JSON est invalide.']]
                ], 400);
            }

            // Extract student modules data
            $studentModulesData = $this->extractStudentModulesData($jsonData);

            if (empty($studentModulesData)) {
                return response()->json([
                    'message' => 'Aucune donnée de module étudiant trouvée dans le fichier.',
                    'errors' => ['json_file' => ['Aucune donnée valide trouvée.']]
                ], 400);
            }

            if (count($studentModulesData) > 500000) {
                return response()->json([
                    'message' => 'Le fichier ne peut pas contenir plus de 500000 modules étudiants à la fois.',
                    'errors' => ['json_file' => ['Limite de 500000 modules dépassée.']]
                ], 400);
            }

            // Create import session
            $importId = uniqid('import_', true);

            // Store data in database chunks
            $this->storeImportDataInDatabase($importId, $studentModulesData);

            $importSession = [
                'id' => $importId,
                'total_records' => count($studentModulesData),
                'processed' => 0,
                'imported' => 0,
                'skipped' => 0,
                'errors' => [],
                'chunk_size' => $chunkSize,
                'status' => 'pending',
                'started_at' => now()
            ];

            // Store session metadata in cache
            Cache::put('import_session_' . $importId, $importSession, now()->addHours(2));

            // Start processing first chunk
            return $this->processChunk($importId);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Student module import initialization error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur serveur lors de l\'initialisation: ' . $e->getMessage(),
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Process a chunk of the import
     */
    private function processChunk($importId)
    {
        Log::info('Processing chunk for import ID: ' . $importId);

        try {
            $importSession = Cache::get('import_session_' . $importId);

            if (!$importSession) {
                Log::error('Import session not found for ID: ' . $importId);
                return response()->json([
                    'message' => 'Session d\'importation expirée ou introuvable.',
                    'status' => 'error'
                ], 404);
            }

            Log::info('Import session found', ['status' => $importSession['status'], 'processed' => $importSession['processed']]);

            // Check if import is already completed
            if ($importSession['status'] === 'completed') {
                return response()->json([
                    'message' => 'Import déjà terminé.',
                    'status' => 'completed',
                    'stats' => [
                        'total' => $importSession['total_records'],
                        'imported' => $importSession['imported'],
                        'skipped' => $importSession['skipped'],
                        'errors' => count($importSession['errors'])
                    ]
                ]);
            }

            // Get chunk to process from database
            $startIndex = $importSession['processed'];
            $chunkData = $this->getImportDataFromDatabase($importId, $startIndex, $importSession['chunk_size']);

            Log::info('Retrieved chunk data', ['start' => $startIndex, 'count' => count($chunkData)]);

            if (empty($chunkData)) {
                // No more data to process, mark as completed
                Log::info('No more data to process, completing import for ID: ' . $importId);
                $importSession['status'] = 'completed';
                $importSession['completed_at'] = now();

                // Clean up temporary data
                $this->cleanupImportData($importId);

                // Store final results in session for results page
                session([
                    'import_stats' => [
                        'imported' => $importSession['imported'],
                        'skipped' => $importSession['skipped'],
                        'errors' => count($importSession['errors']),
                        'total' => $importSession['total_records'],
                        'success_rate' => $importSession['total_records'] > 0 ?
                            ($importSession['imported'] / $importSession['total_records']) * 100 : 0
                    ],
                    'import_errors' => $importSession['errors']
                ]);

                Cache::put('import_session_' . $importId, $importSession, now()->addHours(2));

                return response()->json([
                    'status' => 'completed',
                    'message' => 'Import terminé avec succès!',
                    'stats' => [
                        'total' => $importSession['total_records'],
                        'imported' => $importSession['imported'],
                        'skipped' => $importSession['skipped'],
                        'errors' => count($importSession['errors'])
                    ],
                    'redirect' => url('/admin/student-modules-import/results')
                ]);
            }

            // Process current chunk
            Log::info('Processing chunk data for import ID: ' . $importId);
            $chunkResults = $this->processChunkData($chunkData, $startIndex);

            // Update session with results
            $importSession['processed'] += count($chunkData);
            $importSession['imported'] += $chunkResults['imported'];
            $importSession['skipped'] += $chunkResults['skipped'];
            $importSession['errors'] = array_merge($importSession['errors'], $chunkResults['errors']);

            // Limit errors array to prevent memory issues
            if (count($importSession['errors']) > 1000) {
                $importSession['errors'] = array_slice($importSession['errors'], -1000);
            }

            // Update cache
            Cache::put('import_session_' . $importId, $importSession, now()->addHours(2));

            // Calculate progress
            $progress = ($importSession['processed'] / $importSession['total_records']) * 100;

            Log::info('Chunk processed successfully', [
                'progress' => $progress,
                'imported' => $chunkResults['imported'],
                'skipped' => $chunkResults['skipped'],
                'errors' => count($chunkResults['errors'])
            ]);

            // Use the same endpoint for continuation - no routing issues!
            $continueUrl = request()->getSchemeAndHttpHost() . request()->getRequestUri() . '?action=process_chunk&import_id=' . $importId;

            return response()->json([
                'status' => 'processing',
                'progress' => round($progress, 2),
                'processed' => $importSession['processed'],
                'total' => $importSession['total_records'],
                'imported' => $importSession['imported'],
                'skipped' => $importSession['skipped'],
                'errors' => count($importSession['errors']),
                'continue_url' => $continueUrl,
                'import_id' => $importId
            ]);

        } catch (\Exception $e) {
            Log::error('Chunk processing error for import ' . $importId . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erreur lors du traitement du chunk: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Store import data in database for chunked processing
     */
    private function storeImportDataInDatabase($importId, $data)
    {
        // Create temporary table if it doesn't exist
        DB::statement("
            CREATE TABLE IF NOT EXISTS temp_import_data (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                import_id VARCHAR(255) NOT NULL,
                chunk_index INT NOT NULL,
                data JSON NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_import_id (import_id),
                INDEX idx_chunk (import_id, chunk_index)
            ) ENGINE=InnoDB
        ");

        // Split data into chunks and store each chunk
        $chunkSize = 1000; // Store 1000 records per database row
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $index => $chunk) {
            DB::table('temp_import_data')->insert([
                'import_id' => $importId,
                'chunk_index' => $index,
                'data' => json_encode($chunk)
            ]);
        }
    }

    /**
     * Get import data from database
     */
    private function getImportDataFromDatabase($importId, $startIndex, $limit)
    {
        $chunkSize = 1000; // Same as storage chunk size
        $startChunk = intval($startIndex / $chunkSize);
        $endChunk = intval(($startIndex + $limit - 1) / $chunkSize);

        $allData = [];

        // Get all relevant chunks
        $chunks = DB::table('temp_import_data')
            ->where('import_id', $importId)
            ->where('chunk_index', '>=', $startChunk)
            ->where('chunk_index', '<=', $endChunk)
            ->orderBy('chunk_index')
            ->get();

        foreach ($chunks as $chunk) {
            $data = json_decode($chunk->data, true);
            $allData = array_merge($allData, $data);
        }

        // Extract the exact slice we need
        $offsetInData = $startIndex % $chunkSize;
        if ($startChunk < $endChunk) {
            // Data spans multiple chunks, calculate correct offset
            $offsetInData = $startIndex - ($startChunk * $chunkSize);
        }

        return array_slice($allData, $offsetInData, $limit);
    }

    /**
     * Clean up temporary import data
     */
    private function cleanupImportData($importId)
    {
        try {
            DB::table('temp_import_data')->where('import_id', $importId)->delete();
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup import data for ' . $importId . ': ' . $e->getMessage());
        }
    }

    /**
     * Process a chunk of data
     */
    private function processChunkData($chunkData, $startIndex)
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Optimize: Get existing student codes once per chunk
        $existingStudentApogees = Student::pluck('apoL_a01_code')->flip()->toArray();

        DB::beginTransaction();

        try {
            foreach ($chunkData as $index => $moduleData) {
                $lineNumber = $startIndex + $index + 1;

                try {
                    $mappedData = $this->mapJsonToDatabase($moduleData);

                    // Validation
                    if (!isset($mappedData['apogee']) || empty($mappedData['apogee'])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => 'N/A',
                            'message' => 'Code Apogée manquant',
                            'type' => 'validation'
                        ];
                        continue;
                    }

                    if (!isset($mappedData['module_code']) || empty($mappedData['module_code'])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => $mappedData['apogee'],
                            'message' => 'Code module manquant',
                            'type' => 'validation'
                        ];
                        continue;
                    }

                    if (!isset($mappedData['module_name']) || empty($mappedData['module_name'])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => $mappedData['apogee'],
                            'message' => 'Nom du module manquant',
                            'type' => 'validation'
                        ];
                        continue;
                    }

                    // Check student exists
                    if (!isset($existingStudentApogees[$mappedData['apogee']])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => $mappedData['apogee'],
                            'message' => 'Étudiant non trouvé dans la base de données',
                            'type' => 'foreign_key_check'
                        ];
                        $skipped++;
                        continue;
                    }

                    // Check if module already exists
                    if (PedaModule::where('apogee', $mappedData['apogee'])
                                  ->where('module_code', $mappedData['module_code'])
                                  ->where('annee_scolaire', $mappedData['annee_scolaire'])
                                  ->where('semester', $mappedData['semester'])
                                  ->exists()) {
                        $skipped++;
                        continue;
                    }

                    PedaModule::create($mappedData);
                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'code' => $mappedData['apogee'] ?? 'N/A',
                        'message' => 'Erreur à l\'insertion: ' . $e->getMessage(),
                        'type' => 'database_record_error'
                    ];
                }
            }

            DB::commit();

            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Extract student modules data from JSON structure
     */
    private function extractStudentModulesData($jsonData)
    {
        $studentModulesData = [];

        if (isset($jsonData['results']) && is_array($jsonData['results'])) {
            // Oracle export format
            foreach ($jsonData['results'] as $result) {
                if (isset($result['items']) && is_array($result['items'])) {
                    if (!empty($result['items']) && is_string(array_key_first($result['items'][0]))) {
                        // Items are associative arrays
                        foreach ($result['items'] as $item) {
                            $processedItem = [];
                            foreach ($item as $key => $value) {
                                $processedItem[strtolower($key)] = $value;
                            }
                            $studentModulesData[] = $processedItem;
                        }
                    } elseif (isset($result['columns']) && is_array($result['columns'])) {
                        // Items are indexed arrays with column mapping
                        $columns = $result['columns'];
                        $columnNames = array_map(function($col) {
                            return $col['name'] ?? $col;
                        }, $columns);

                        foreach ($result['items'] as $item) {
                            if (is_array($item)) {
                                $moduleRow = [];
                                foreach ($item as $index => $value) {
                                    if (isset($columnNames[$index])) {
                                        $moduleRow[strtolower($columnNames[$index])] = $value;
                                    }
                                }
                                $studentModulesData[] = $moduleRow;
                            }
                        }
                    }
                }
            }
        } elseif (is_array($jsonData)) {
            // Direct array of objects
            foreach ($jsonData as $item) {
                $processedItem = [];
                foreach ($item as $key => $value) {
                    $processedItem[strtolower($key)] = $value;
                }
                $studentModulesData[] = $processedItem;
            }
        }

        return $studentModulesData;
    }

    /**
     * Map JSON fields to database columns for PedaModule
     */
    private function mapJsonToDatabase($moduleData)
    {
        $jsonToDbMap = [
            'apogee' => 'apogee',
            'cod_elp' => 'module_code',
            'module' => 'module_name',
            'ia' => 'professor',
            'module_name_ar' => 'module_name_ar',
            'credits' => 'credits',
            'coefficient' => 'coefficient',
            'semester' => 'semester',
            'annee_scolaire' => 'annee_scolaire',
            'status' => 'status',
            'schedule' => 'schedule',
        ];

        $mapped = [];
        foreach ($jsonToDbMap as $jsonKey => $dbColumn) {
            $mapped[$dbColumn] = $moduleData[$jsonKey] ?? null;
        }

        // Apply defaults
        $mapped['credits'] = isset($mapped['credits']) ? (int)$mapped['credits'] : 0;
        $mapped['coefficient'] = isset($mapped['coefficient']) ? (float)$mapped['coefficient'] : 1.00;
        $mapped['semester'] = $mapped['semester'] ?? 'S1';
        $mapped['annee_scolaire'] = $mapped['annee_scolaire'] ?? '2024-2025';
        $mapped['status'] = $mapped['status'] ?? 'active';
        $mapped['schedule'] = $mapped['schedule'] ?? null;

        return $mapped;
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
