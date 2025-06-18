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
     * Handle JSON file import for student modules with chunked processing
     */
    public function import(Request $request)
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

                            // Quick JSON validation without loading entire file
                            $handle = fopen($value->getPathname(), 'r');
                            $firstChunk = fread($handle, 1024);
                            fclose($handle);

                            if (!str_contains($firstChunk, '{') && !str_contains($firstChunk, '[')) {
                                $fail('Le fichier JSON est invalide.');
                            }
                        }
                    },
                ],
                'chunk_size' => 'nullable|integer|min:10|max:1000'
            ]);

            $file = $request->file('json_file');
            $chunkSize = $request->input('chunk_size', 100); // Default chunk size

            // Store file temporarily and create import session
            $importId = uniqid('import_', true);
            $tempPath = storage_path('app/temp/' . $importId . '.json');

            // Ensure temp directory exists
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            $file->storeAs('temp', $importId . '.json');

            // Parse JSON and validate structure
            $jsonContent = file_get_contents($tempPath);
            $jsonData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                unlink($tempPath);
                return response()->json([
                    'message' => 'Le fichier JSON est invalide.',
                    'errors' => ['json_file' => ['Le fichier JSON est invalide.']]
                ], 400);
            }

            // Extract student modules data
            $studentModulesData = $this->extractStudentModulesData($jsonData);

            if (empty($studentModulesData)) {
                unlink($tempPath);
                return response()->json([
                    'message' => 'Aucune donnée de module étudiant trouvée dans le fichier.',
                    'errors' => ['json_file' => ['Aucune donnée valide trouvée.']]
                ], 400);
            }

            if (count($studentModulesData) > 500000) {
                unlink($tempPath);
                return response()->json([
                    'message' => 'Le fichier ne peut pas contenir plus de 500000 modules étudiants à la fois.',
                    'errors' => ['json_file' => ['Limite de 500000 modules dépassée.']]
                ], 400);
            }

            // Initialize import session
            $importSession = [
                'id' => $importId,
                'total_records' => count($studentModulesData),
                'processed' => 0,
                'imported' => 0,
                'skipped' => 0,
                'errors' => [],
                'chunk_size' => $chunkSize,
                'status' => 'pending',
                'started_at' => now(),
                'temp_file' => $tempPath
            ];

            // Cache the session data
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
    public function processChunk($importId)
    {
        try {
            $importSession = Cache::get('import_session_' . $importId);

            if (!$importSession) {
                return response()->json([
                    'message' => 'Session d\'importation expirée ou introuvable.',
                    'status' => 'error'
                ], 404);
            }

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

            // Load data from temp file
            if (!file_exists($importSession['temp_file'])) {
                return response()->json([
                    'message' => 'Fichier temporaire introuvable.',
                    'status' => 'error'
                ], 404);
            }

            $jsonContent = file_get_contents($importSession['temp_file']);
            $jsonData = json_decode($jsonContent, true);
            $studentModulesData = $this->extractStudentModulesData($jsonData);

            // Get chunk to process
            $startIndex = $importSession['processed'];
            $chunkData = array_slice($studentModulesData, $startIndex, $importSession['chunk_size']);

            if (empty($chunkData)) {
                // No more data to process, mark as completed
                $importSession['status'] = 'completed';
                $importSession['completed_at'] = now();

                // Clean up temp file
                if (file_exists($importSession['temp_file'])) {
                    unlink($importSession['temp_file']);
                }

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
                    'redirect' => route('admin.student-modules.import.results')
                ]);
            }

            // Process current chunk
            $chunkResults = $this->processChunkData($chunkData, $startIndex);

            // Update session with results
            $importSession['processed'] += count($chunkData);
            $importSession['imported'] += $chunkResults['imported'];
            $importSession['skipped'] += $chunkResults['skipped'];
            $importSession['errors'] = array_merge($importSession['errors'], $chunkResults['errors']);

            // Update cache
            Cache::put('import_session_' . $importId, $importSession, now()->addHours(2));

            // Calculate progress
            $progress = ($importSession['processed'] / $importSession['total_records']) * 100;

            return response()->json([
                'status' => 'processing',
                'progress' => round($progress, 2),
                'processed' => $importSession['processed'],
                'total' => $importSession['total_records'],
                'imported' => $importSession['imported'],
                'skipped' => $importSession['skipped'],
                'errors' => count($importSession['errors']),
                'continue_url' => route('admin.student-modules.process-chunk', $importId)
            ]);

        } catch (\Exception $e) {
            Log::error('Chunk processing error for import ' . $importId . ': ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors du traitement du chunk: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
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
        $existingStudentApogees = Student::pluck('apol_a01_code')->flip()->toArray();

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
