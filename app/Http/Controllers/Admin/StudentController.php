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
        'max:51200', // 50MB max
        function ($attribute, $value, $fail) {
            // Check if it's a JSON file by extension or mime type
            if ($value) {
                $extension = strtolower($value->getClientOriginalExtension());
                $mimeType = $value->getClientMimeType();

                if ($extension !== 'json' && $mimeType !== 'application/json') {
                    $fail('Le fichier doit être un fichier JSON valide.');
                    return;
                }

                // Rest of your existing validation logic...
                $content = file_get_contents($value->getPathname());
                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $fail('Le fichier JSON est invalide.');
                    return;
                }

                // Continue with existing validation logic...
            }
        },
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


$studentsData = [];

if (isset($jsonData['results']) && is_array($jsonData['results'])) {
    // Oracle export format
    foreach ($jsonData['results'] as $result) {
        if (isset($result['items']) && is_array($result['items'])) {
            // The items are already properly formatted objects, just add them directly
            foreach ($result['items'] as $item) {
                $studentsData[] = $item;
            }
        }
    }
} elseif (is_array($jsonData)) {
    $studentsData = $jsonData;
} else {
    return back()->with('error', 'Format JSON non reconnu.');
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
            $lineNumber = 1;  // Add this counter
            DB::beginTransaction();

            foreach ($studentsData as $studentData) {
                try {
                    // Map the JSON fields to our database columns
                    $mappedData = $this->mapJsonToDatabase($studentData);

                    // Validate required fields
                        if (!isset($mappedData['apoL_a01_code']) ||
                            !isset($mappedData['apoL_a02_nom']) ||
                            !isset($mappedData['apoL_a03_prenom'])) {
                        $errors[] = [
                            'line' => $lineNumber,
                            'code' => $mappedData['apoL_a01_code'] ?? 'N/A',
                            'message' => 'Champs requis manquants (Code étudiant, nom, prénom)',
                            'type' => 'validation'
                        ];
                        $lineNumber++;
                        continue;
                    }

                    // Check if student already exists
                    if (Student::where('apoL_a01_code', $mappedData['apoL_a01_code'])->exists()) {
                        $skipped++;
                        $lineNumber++;  // Increment counter
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
                        'line' => $lineNumber,
                        'code' => $mappedData['apoL_a01_code'] ?? 'N/A',
                        'message' => $e->getMessage(),
                        'type' => 'database'
                    ];
                }
                 $lineNumber++;
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
            // Add this debugging section:

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
    $mapped = [];

    // Map student code - your JSON uses 'cod_etu' directly
    if (isset($studentData['cod_etu'])) {
        $mapped['apoL_a01_code'] = (string) $studentData['cod_etu'];
    }

    // Map last name - your JSON uses 'lib_nom_pat_ind' directly
    if (isset($studentData['lib_nom_pat_ind'])) {
        $mapped['apoL_a02_nom'] = $studentData['lib_nom_pat_ind'];
    }

    // Map first name - your JSON uses 'lib_pr1_ind' directly
    if (isset($studentData['lib_pr1_ind'])) {
        $mapped['apoL_a03_prenom'] = $studentData['lib_pr1_ind'];
    }

    // Map birth date
    if (isset($studentData['date_nai_ind'])) {
        $mapped['apoL_a04_naissance'] = $this->formatDate($studentData['date_nai_ind']);
    }

    // Map other fields using the exact field names from your JSON
    $directMappings = [
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
        // First, try to handle the common formats
        $date = null;

        // Try DD/MM/YY format first (most common issue)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];

            // Convert 2-digit year to 4-digit year
            // Assume years 00-30 are 2000-2030, and 31-99 are 1931-1999
            if (intval($year) <= 30) {
                $year = '20' . $year;
            } else {
                $year = '19' . $year;
            }

            return $day . '/' . $month . '/' . $year;
        }

        // Try other date formats
        $formats = [
            'd/m/Y',    // DD/MM/YYYY
            'Y-m-d',    // YYYY-MM-DD
            'd/m/y',    // DD/MM/YY (fallback)
            'Y-m-d H:i:s' // YYYY-MM-DD HH:MM:SS
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                break;
            }
        }

        if ($date === false || $date === null) {
            // If all parsing fails, return the original string
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
