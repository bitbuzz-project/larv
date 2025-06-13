<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Http\Requests\StudentImportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Display a listing of students
     */
    public function index(Request $request)
    {
        $query = Student::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('apoL_a01_code', 'like', "%{$search}%")
                  ->orWhere('apoL_a02_nom', 'like', "%{$search}%")
                  ->orWhere('apoL_a03_prenom', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'apoL_a01_code' => 'required|string|max:20|unique:students_base,apoL_a01_code',
            'apoL_a02_nom' => 'required|string|max:100',
            'apoL_a03_prenom' => 'required|string|max:100',
            'apoL_a04_naissance' => 'required|string|max:20',
            'cod_etu' => 'nullable|string|max:20',
            'cin_ind' => 'nullable|string|max:20',
            'cod_sex_etu' => 'nullable|string|max:5',
            'lib_vil_nai_etu' => 'nullable|string|max:100',
            'cod_etp' => 'nullable|string|max:20',
            'cod_anu' => 'nullable|string|max:10',
            'lib_etp' => 'nullable|string|max:200',
        ]);

        try {
            Student::create($validated);
            return redirect()->route('admin.students.index')
                           ->with('success', 'Étudiant créé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Erreur lors de la création de l\'étudiant.');
        }
    }

    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        $notes_count = 0; // Will implement when Note model is ready
        $reclamations_count = $student->reclamations()->count();

        return view('admin.students.show', compact('student', 'notes_count', 'reclamations_count'));
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'apoL_a02_nom' => 'required|string|max:100',
            'apoL_a03_prenom' => 'required|string|max:100',
            'apoL_a04_naissance' => 'required|string|max:20',
            'cod_etu' => 'nullable|string|max:20',
            'cin_ind' => 'nullable|string|max:20',
            'cod_sex_etu' => 'nullable|string|max:5',
            'lib_vil_nai_etu' => 'nullable|string|max:100',
            'cod_etp' => 'nullable|string|max:20',
            'cod_anu' => 'nullable|string|max:10',
            'lib_etp' => 'nullable|string|max:200',
        ]);

        try {
            $student->update($validated);
            return redirect()->route('admin.students.show', $student)
                           ->with('success', 'Étudiant mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Erreur lors de la mise à jour de l\'étudiant.');
        }
    }

    /**
     * Remove the specified student
     */
    public function destroy(Student $student)
    {
        try {
            $student->delete();
            return redirect()->route('admin.students.index')
                           ->with('success', 'Étudiant supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                           ->with('error', 'Erreur lors de la suppression de l\'étudiant.');
        }
    }

    /**
     * Show the import form
     */
    public function showImport()
    {
        return view('admin.students.import');
    }

    /**
     * Handle JSON file import
     */
    public function import(Request $request)
    {
        // Increase execution limits for large imports
        set_time_limit(300); // 5 minutes
        ini_set('memory_limit', '512M'); // 512MB memory

        // Validate the uploaded file
        $request->validate([
            'json_file' => [
                'required',
                'file',
                'mimes:json',
                'max:51200', // 50MB max
            ],
        ]);

        try {
            $file = $request->file('json_file');
            $jsonContent = file_get_contents($file->getPathname());
            $jsonData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Le fichier JSON est invalide.');
            }

            // Handle the JSON structure - expecting array of student objects
            $studentsData = [];

            if (is_array($jsonData)) {
                $studentsData = $jsonData;
            } else {
                return back()->with('error', 'Le fichier JSON doit contenir un tableau d\'étudiants.');
            }

            if (empty($studentsData)) {
                return back()->with('error', 'Aucune donnée d\'étudiant trouvée dans le fichier.');
            }

            if (count($studentsData) > 20000) {
                return back()->with('error', 'Le fichier ne peut pas contenir plus de 20000 étudiants à la fois.');
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($studentsData as $index => $studentData) {
                try {
                    // Map the JSON fields to our database columns
                    $mappedData = $this->mapJsonToDatabase($studentData);

                    // Validate required fields
                    if (!isset($mappedData['apoL_a01_code']) ||
                        !isset($mappedData['apoL_a02_nom']) ||
                        !isset($mappedData['apoL_a03_prenom'])) {
                        $errors[] = [
                            'line' => $index + 1,
                            'code' => $mappedData['apoL_a01_code'] ?? 'N/A',
                            'message' => 'Champs requis manquants (Code étudiant, nom, prénom)',
                            'type' => 'validation'
                        ];
                        continue;
                    }

                    // Check if student already exists
                    if (Student::where('apoL_a01_code', $mappedData['apoL_a01_code'])->exists()) {
                        $skipped++;
                        continue;
                    }

                    // Create student
                    Student::create([
                        'apoL_a01_code' => $mappedData['apoL_a01_code'],
                        'apoL_a02_nom' => $mappedData['apoL_a02_nom'],
                        'apoL_a03_prenom' => $mappedData['apoL_a03_prenom'],
                        'apoL_a04_naissance' => $mappedData['apoL_a04_naissance'] ?? null,
                        'cod_etu' => $mappedData['cod_etu'] ?? null,
                        'cod_sex_etu' => $mappedData['cod_sex_etu'] ?? null,
                        'lib_vil_nai_etu' => $mappedData['lib_vil_nai_etu'] ?? null,
                        'cin_ind' => $mappedData['cin_ind'] ?? null,
                        'cod_etp' => $mappedData['cod_etp'] ?? null,
                        'cod_anu' => $mappedData['cod_anu'] ?? null,
                        'cod_dip' => $mappedData['cod_dip'] ?? null,
                        'lib_etp' => $mappedData['lib_etp'] ?? null,
                        'lic_etp' => $mappedData['lic_etp'] ?? null,
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = [
                        'line' => $index + 1,
                        'code' => $mappedData['apoL_a01_code'] ?? 'N/A',
                        'message' => $e->getMessage(),
                        'type' => 'database'
                    ];
                }
            }

            DB::commit();

            // Store import statistics in session
            session([
                'import_stats' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => count($errors),
                    'total' => count($studentsData),
                    'success_rate' => count($studentsData) > 0 ? ($imported / count($studentsData)) * 100 : 0
                ],
                'import_errors' => $errors
            ]);

            if ($imported > 0) {
                $message = "Import réussi! {$imported} étudiants importés";
                if ($skipped > 0) {
                    $message .= ", {$skipped} ignorés (déjà existants)";
                }
                if (count($errors) > 0) {
                    $message .= ", " . count($errors) . " erreurs";
                }

                return redirect()->route('admin.students.import.results')->with('success', $message);
            } else {
                return back()->with('error', 'Aucun étudiant n\'a pu être importé. Vérifiez les erreurs ci-dessus.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Import error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }

    /**
     * Map JSON fields to database columns
     */
    private function mapJsonToDatabase($studentData)
    {
        // This function maps the actual JSON field names to our database columns
        $mapped = [];

        // Map student code - try different possible field names
        if (isset($studentData['cod_etu'])) {
            $mapped['apoL_a01_code'] = (string) $studentData['cod_etu'];
        } elseif (isset($studentData['cod_etu_1'])) {
            $mapped['apoL_a01_code'] = (string) $studentData['cod_etu_1'];
        } elseif (isset($studentData['COD_ETU'])) {
            $mapped['apoL_a01_code'] = (string) $studentData['COD_ETU'];
        } elseif (isset($studentData['COD_ETU_1'])) {
            $mapped['apoL_a01_code'] = (string) $studentData['COD_ETU_1'];
        }

        // Map last name
        if (isset($studentData['lib_nom_pat_ind'])) {
            $mapped['apoL_a02_nom'] = $studentData['lib_nom_pat_ind'];
        } elseif (isset($studentData['lib_nom_pat_ind_1'])) {
            $mapped['apoL_a02_nom'] = $studentData['lib_nom_pat_ind_1'];
        } elseif (isset($studentData['LIB_NOM_PAT_IND'])) {
            $mapped['apoL_a02_nom'] = $studentData['LIB_NOM_PAT_IND'];
        } elseif (isset($studentData['LIB_NOM_PAT_IND_1'])) {
            $mapped['apoL_a02_nom'] = $studentData['LIB_NOM_PAT_IND_1'];
        }

        // Map first name
        if (isset($studentData['lib_pr1_ind'])) {
            $mapped['apoL_a03_prenom'] = $studentData['lib_pr1_ind'];
        } elseif (isset($studentData['lib_pr1_ind_1'])) {
            $mapped['apoL_a03_prenom'] = $studentData['lib_pr1_ind_1'];
        } elseif (isset($studentData['LIB_PR1_IND'])) {
            $mapped['apoL_a03_prenom'] = $studentData['LIB_PR1_IND'];
        } elseif (isset($studentData['LIB_PR1_IND_1'])) {
            $mapped['apoL_a03_prenom'] = $studentData['LIB_PR1_IND_1'];
        }

        // Map birth date
        if (isset($studentData['date_nai_ind'])) {
            $mapped['apoL_a04_naissance'] = $this->formatDate($studentData['date_nai_ind']);
        } elseif (isset($studentData['DATE_NAI_IND'])) {
            $mapped['apoL_a04_naissance'] = $this->formatDate($studentData['DATE_NAI_IND']);
        }

        // Map other fields directly
        $directMappings = [
            // JSON field => Database field
            'cod_etu' => 'cod_etu',
            'cod_sex_etu' => 'cod_sex_etu',
            'lib_vil_nai_etu' => 'lib_vil_nai_etu',
            'cin_ind' => 'cin_ind',
            'cod_etp' => 'cod_etp',
            'cod_anu' => 'cod_anu',
            'cod_dip' => 'cod_dip',
            'lib_etp' => 'lib_etp',
            'lic_etp' => 'lic_etp',
        ];

        foreach ($directMappings as $jsonField => $dbField) {
            if (isset($studentData[$jsonField])) {
                $mapped[$dbField] = $studentData[$jsonField];
            }
            // Also try uppercase version
            $upperJsonField = strtoupper($jsonField);
            if (isset($studentData[$upperJsonField])) {
                $mapped[$dbField] = $studentData[$upperJsonField];
            }
        }

        return $mapped;
    }

    /**
     * Format date to consistent format
     */
    private function formatDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Try to parse various date formats
            $date = \DateTime::createFromFormat('d/m/Y', $dateString);
            if (!$date) {
                $date = \DateTime::createFromFormat('Y-m-d', $dateString);
            }
            if (!$date) {
                $date = \DateTime::createFromFormat('d/m/y', $dateString);
            }
            if (!$date) {
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
            }
            if (!$date) {
                // If parsing fails, return the original string
                return $dateString;
            }

            return $date->format('d/m/Y');
        } catch (\Exception $e) {
            return $dateString; // Return original if formatting fails
        }
    }

    /**
     * Show import results
     */
    public function importResults()
    {
        if (!session()->has('import_stats')) {
            return redirect()->route('admin.students.import')
                           ->with('error', 'Aucun résultat d\'import trouvé.');
        }

        return view('admin.students.import-results');
    }
}
