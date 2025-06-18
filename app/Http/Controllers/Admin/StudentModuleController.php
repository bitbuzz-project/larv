<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedaModule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * Handle JSON file import for student modules
     */
    public function import(Request $request)
    {
        // Increase execution limits for large imports
        set_time_limit(1800); // 30 minutes
        ini_set('memory_limit', '1024M'); // 1GB memory

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

                            $content = file_get_contents($value->getPathname());
                            $data = json_decode($content, true);

                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $fail('Le fichier JSON est invalide.');
                            }
                        }
                    },
                ],
            ]);

            $file = $request->file('json_file');
            $jsonContent = file_get_contents($file->getPathname());
            $jsonData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['message' => 'Le fichier JSON est invalide.', 'errors' => ['json_file' => ['Le fichier JSON est invalide.']]], 400);
            }

            $studentModulesData = [];

            if (isset($jsonData['results']) && is_array($jsonData['results'])) {
                // Oracle export format or similar
                foreach ($jsonData['results'] as $result) {
                    if (isset($result['items']) && is_array($result['items'])) {
                        // Check if items are already associative arrays (key-value pairs)
                        if (!empty($result['items']) && is_string(array_key_first($result['items'][0]))) {
                            // If items are already associative arrays, use them directly and convert keys to lowercase
                            foreach ($result['items'] as $item) {
                                $processedItem = [];
                                foreach ($item as $key => $value) {
                                    $processedItem[strtolower($key)] = $value; // Ensure keys are lowercase
                                }
                                $studentModulesData[] = $processedItem;
                            }
                        } elseif (isset($result['columns']) && is_array($result['columns'])) {
                            // FALLBACK LOGIC: If items are indexed arrays, map using columns as before
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
                // If JSON is a direct array of objects (like [{key: value}, ...])
                foreach ($jsonData as $item) {
                    $processedItem = [];
                    foreach ($item as $key => $value) {
                        $processedItem[strtolower($key)] = $value; // Ensure keys are lowercase
                    }
                    $studentModulesData[] = $processedItem;
                }
            } else {
                return response()->json(['message' => 'Format JSON non reconnu.', 'errors' => ['json_file' => ['Format JSON non reconnu.']]], 400);
            }

            if (count($studentModulesData) > 500000) {
                return response()->json(['message' => 'Le fichier ne peut pas contenir plus de 50000 modules étudiants à la fois.', 'errors' => ['json_file' => ['Limite de 50000 modules dépassée.']]], 400);
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $lineNumber = 1;

            // OPTIMIZATION: Fetch all existing student APOGEE codes once
            // This replaces thousands of 'exists()' queries with a single query and in-memory lookups.
            $existingStudentApogees = Student::pluck('apoL_a01_code')->flip()->toArray();

            DB::beginTransaction(); // Start a database transaction

            foreach ($studentModulesData as $moduleData) {
                try {
                    $mappedData = $this->mapJsonToDatabase($moduleData);

                    // Basic validation for required fields
                    if (!isset($mappedData['apogee']) || empty($mappedData['apogee'])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => 'N/A',
                            'message' => 'Code Apogée manquant (APOGEE) au serveur. Vérifiez votre fichier source.',
                            'type' => 'validation'
                        ];
                        $lineNumber++;
                        continue;
                    }
                    if (!isset($mappedData['module_code']) || empty($mappedData['module_code'])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => $mappedData['apogee'] ?? 'N/A',
                            'message' => 'Code module manquant (COD_ELP) au serveur. Vérifiez votre fichier source.',
                            'type' => 'validation'
                        ];
                        $lineNumber++;
                        continue;
                    }
                     if (!isset($mappedData['module_name']) || empty($mappedData['module_name'])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => $mappedData['apogee'] ?? 'N/A',
                            'message' => 'Nom du module manquant (MODULE) au serveur. Vérifiez votre fichier source.',
                            'type' => 'validation'
                        ];
                        $lineNumber++;
                        continue;
                    }

                    // Optimized student existence check using the in-memory cache
                    if (!isset($existingStudentApogees[$mappedData['apogee']])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => $mappedData['apogee'],
                            'message' => 'Étudiant non trouvé dans la base de données. Module ignoré.',
                            'type' => 'foreign_key_check'
                        ];
                        $skipped++;
                        $lineNumber++;
                        continue; // Skip to the next module
                    }

                    // Check if module already exists for this student for the academic year and semester
                    if (PedaModule::where('apogee', $mappedData['apogee'])
                                  ->where('module_code', $mappedData['module_code'])
                                  ->where('annee_scolaire', $mappedData['annee_scolaire'])
                                  ->where('semester', $mappedData['semester'])
                                  ->exists()) {
                        $skipped++;
                        $lineNumber++;
                        continue;
                    }

                    PedaModule::create($mappedData);
                    $imported++;

                } catch (\Exception $e) {
                    // This catch handles errors during individual record insertion (e.g., data type mismatch)
                    // We DO NOT rollback the transaction here, as we want to continue processing other records.
                    Log::error('Student module import error for record on line ' . $lineNumber . ' (Apogee: ' . ($mappedData['apogee'] ?? 'N/A') . '): ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
                    $errors[] = [
                        'line' => $lineNumber,
                        'code' => $mappedData['apogee'] ?? 'N/A',
                        'message' => 'Erreur à l\'insertion en base de données pour ce module: ' . $e->getMessage(),
                        'type' => 'database_record_error'
                    ];
                    // We continue the loop here to try and process subsequent records
                }
                $lineNumber++;
            }

            DB::commit(); // Commit the transaction if all records were processed or skipped without critical errors

            session([
                'import_stats' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'total' => count($studentModulesData),
                    'success_rate' => count($studentModulesData) > 0 ? ($imported / count($studentModulesData)) * 100 : 0
                ],
                'import_errors' => $errors
            ]);

            // Adjust message based on overall outcome
            if ($imported > 0 || $skipped > 0 || count($errors) > 0) {
                $message = "Import terminé! {$imported} modules étudiants importés";
                if ($skipped > 0) {
                    $message .= ", {$skipped} ignorés";
                }
                if (count($errors) > 0) {
                    $message .= ", " . count($errors) . " erreurs";
                }
                return response()->json(['message' => $message, 'redirect' => route('admin.student-modules.import.results')], 200);
            } else {
                // This case handles when the file was processed, but no data was actually imported, skipped, or had errors (e.g., empty file)
                $errorMessage = 'Aucune donnée de module étudiant n\'a pu être traitée ou le fichier était vide.';
                return response()->json(['message' => $errorMessage, 'errors' => ['general' => [$errorMessage]]], 400);
            }

        } catch (ValidationException $e) {
            // This catches validation errors defined by $request->validate()
            throw $e; // Laravel's default handler for AJAX will return JSON 422 automatically
        } catch (\Exception $e) {
            DB::rollback(); // Rollback the entire transaction if a critical error occurs outside the record loop
            Log::error('Global Student module import server error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json(['message' => 'Erreur serveur critique lors de l\'importation: ' . $e->getMessage(), 'errors' => ['general' => [$e->getMessage()]]], 500);
        }
    }

    /**
     * Map JSON fields to database columns for PedaModule
     * @param array $moduleData The raw module data from JSON
     * @return array The mapped data for the PedaModule model
     */
    private function mapJsonToDatabase($moduleData)
    {
        $mapped = [];

        // Define a mapping array from potential JSON keys (lowercase) to PedaModule column names
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

        // Populate mapped data based on the JSON and the map
        foreach ($jsonToDbMap as $jsonKey => $dbColumn) {
            // Check if the key exists directly in the moduleData
            if (isset($moduleData[$jsonKey])) {
                $mapped[$dbColumn] = $moduleData[$jsonKey];
            } else {
                $mapped[$dbColumn] = null; // Default to null if not found
            }
        }

        // Apply default values if not provided in JSON or mapped to null
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
