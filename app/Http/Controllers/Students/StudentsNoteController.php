<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Models\ModuleArabic; // This model is not used in the updated logic, can be removed.
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Module; // Make sure this model is correctly defined to interact with the 'modules' table.

class StudentsNoteController extends Controller
{
    /**
     * Display student's notes
     */
    public function index(Request $request)
    {
        $student = Auth::user();
        $currentYear = '2024-2025'; // Ensure this matches your application's definition of current year

        // Get filter parameters
        $selectedYear = $request->input('annee_scolaire', $currentYear);
        $selectedSession = $request->input('session_type', 'all');
        $selectedResultType = $request->input('result_type', 'all');

        // Get available years for filter
        $availableYears = DB::table('notes')
            ->where('apoL_a01_code', $student->apoL_a01_code)
            ->select('annee_scolaire')
            ->distinct()
            ->union(
                DB::table('notes_actu')
                    ->where('apoL_a01_code', $student->apoL_a01_code)
                    ->select('annee_scolaire')
                    ->distinct()
            )
            ->orderBy('annee_scolaire', 'desc')
            ->pluck('annee_scolaire');

        // --- Fetch current session notes (notes_actu) ---
        $currentNotesQuery = DB::table('notes_actu')
            ->where('apoL_a01_code', $student->apoL_a01_code)
            ->where('annee_scolaire', $selectedYear)
            // Select all necessary columns, including session_type and result_type
            ->select(
                'id',
                'apoL_a01_code',
                'code_module',
                'nom_module',
                'note',
                'annee_scolaire',
                'session_type', // Keep for current sessions
                'result_type',  // Keep for current sessions
                DB::raw('1 as is_current') // Flag to easily distinguish current sessions
            );

        if ($selectedSession !== 'all') {
            $currentNotesQuery->where('session_type', $selectedSession);
        }

        if ($selectedResultType !== 'all') {
            $currentNotesQuery->where('result_type', $selectedResultType);
        }

        $currentNotes = $currentNotesQuery->orderBy('code_module')->get();

        // --- Fetch old session notes (notes table) ---
        $oldNotes = collect();
        // Only fetch old notes if the selected year is not the current year OR if 'all' sessions are requested for the current year
        if ($selectedYear !== $currentYear || $selectedSession === 'all') {
            $oldNotesQuery = DB::table('notes')
                ->where('apoL_a01_code', $student->apoL_a01_code)
                ->where('annee_scolaire', $selectedYear)
                // Select all necessary columns, including COD_SES and COD_TRE
                ->select(
                    'id',
                    'apoL_a01_code',
                    'code_module',
                    'nom_module',
                    'note',
                    'annee_scolaire',
                    'COD_SES', // New: Select COD_SES
                    'COD_TRE', // New: Select COD_TRE
                    DB::raw('0 as is_current') // Flag to easily distinguish old sessions
                );

            $oldNotes = $oldNotesQuery->orderBy('code_module')->get();
        }

        // --- Get all unique module codes from combined notes ---
        $allNotes = $currentNotes->concat($oldNotes);
        $moduleCodes = $allNotes->pluck('code_module')->unique();

        // --- Get module details (Arabic name and PEL code) from the modules table ---
        // Fetch 'cod_elp' (which is 'code_module'), 'lib_elp_arb_fixed' (Arabic name), and 'cod_pel' (for semester)
        $moduleDetailsMap = Module::whereIn('cod_elp', $moduleCodes)
            ->get(['cod_elp', 'lib_elp_arb_fixed', 'cod_pel'])
            ->keyBy('cod_elp'); // Key by cod_elp for easy lookup

        // --- Define mapping for COD_SES display names ---
        $codSesDisplayMap = [
            '1' => 'Session Normale',
            '2' => 'Session Rattrapage',
            // Add any other COD_SES values you might have
        ];

        // --- Add display names, semester, and session display names to all notes ---
        $allNotes = $allNotes->map(function ($note) use ($moduleDetailsMap, $codSesDisplayMap) {
            $moduleDetail = $moduleDetailsMap->get($note->code_module);

            // Assign Module Display Name (Arabic if available, otherwise original nom_module)
            $note->display_name = $moduleDetail ? $moduleDetail->lib_elp_arb_fixed : $note->nom_module;

            // Assign Semester based on COD_PEL from modules table
            $codPel = $moduleDetail ? $moduleDetail->cod_pel : null;
            $note->semester_name = $this->mapCodPelToSemester($codPel); // Call helper function

            // Assign Session Display Name based on note type (current vs. old)
            if ($note->is_current) {
                // For current sessions (notes_actu), use session_type and result_type
                $note->session_display_name = $this->mapCurrentSessionType($note->session_type, $note->result_type); // Call helper function
            } else {
                // For old sessions (notes), use COD_SES
                $note->session_display_name = $codSesDisplayMap[$note->COD_SES] ?? 'Session Inconnue';
            }

            return $note;
        });

        // --- Group notes: First by Semester, then by Session Display Name ---
        $notesBySemester = $allNotes->groupBy('semester_name')->map(function ($semesterNotes, $semesterName) {
            return [
                'semester_name' => $semesterName,
                'sessions' => $semesterNotes->groupBy('session_display_name')->map(function ($sessionNotes, $sessionDisplayName) {
                    // Get 'is_current' and 'result_type' from the first note in the session group
                    $firstNoteInSession = $sessionNotes->first();
                    $isCurrent = $firstNoteInSession->is_current;
                    $resultType = $isCurrent ? ($firstNoteInSession->result_type ?? '') : '';

                    return [
                        'session_type_display' => $sessionDisplayName, // The user-friendly session name
                        'is_current' => $isCurrent,
                        'result_type' => $resultType, // Only relevant for notes_actu
                        'notes' => $sessionNotes->sortBy('code_module')->values(), // Sort modules within each session
                    ];
                })->sortBy(function($session) {
                    // Custom sorting for sessions within a semester (e.g., Normale before Rattrapage, Printemps before Automne)
                    $sessionType = strtolower($session['session_type_display']);
                    if (str_contains($sessionType, 'normale')) return 1;
                    if (str_contains($sessionType, 'rattrapage')) return 2;
                    if (str_contains($sessionType, 'printemps')) return 3;
                    if (str_contains($sessionType, 'automne')) return 4;
                    return 99; // Fallback for unknown session types
                })->values() // Convert to a simple array for easy iteration in Blade
            ];
        })->sortBy(function($semester) {
            // Sort semesters numerically (Semestre 1, Semestre 2, etc.)
            preg_match('/\d+/', $semester['semester_name'], $matches);
            return $matches[0] ?? 99; // Extract number for sorting, default to 99 if not found
        })->values(); // Convert to a simple array for easy iteration in Blade


        // --- Calculate statistics ---
        // Ensure to use the mapped $allNotes for stats if display_name, semester_name, etc., are used in calculations
        $stats = [
            'total_modules' => $allNotes->count(),
            'passed_modules' => $allNotes->where('note', '>=', 10)->count(),
            'failed_modules' => $allNotes->where('note', '<', 10)->where('note', '!=', null)->count(),
            'average_grade' => $allNotes->where('note', '!=', null)->avg('note'),
            'highest_grade' => $allNotes->where('note', '!=', null)->max('note'),
            'lowest_grade' => $allNotes->where('note', '!=', null)->min('note'),
        ];

        return view('student.notes.index', compact(
            'student',
            'notesBySemester', // Pass the new grouped structure to the view
            'stats',
            'availableYears',
            'selectedYear',
            'selectedSession',
            'selectedResultType',
            'currentYear'
        ));
    }

    /**
     * Helper to map COD_PEL to Semester Name (e.g., 'PEL01' -> 'Semestre 1')
     */
      private function mapCodPelToSemester($codPel)
    {
        if (empty($codPel)) {
            return 'Semestre Inconnu';
        }

        $codPel = trim(strtoupper($codPel)); // Normalize to uppercase and trim

        // Try to match 'S' followed by one or more digits (e.g., S1, S2)
        if (preg_match('/^S(\d+)$/', $codPel, $matches)) {
            return 'Semestre ' . (int)$matches[1];
        }

        // Fallback for formats like 'PEL01' or just a number, if they exist
        if (preg_match('/(\d+)/', $codPel, $matches)) {
            return 'Semestre ' . (int)$matches[1];
        }

        // If no number can be extracted, return 'Semestre Inconnu' or the original value
        return 'Semestre Inconnu'; // Or return $codPel if you want to see the unmapped value
    }

    /**
     * Helper to map current session types to a display name
     */
    private function mapCurrentSessionType($sessionType, $resultType)
    {
        $sessionMap = [
            'printemps' => 'Session Printemps',
            'automne' => 'Session Automne',
        ];

        $resultMap = [
            'normale' => 'Normale',
            'rattrapage' => 'Rattrapage',
        ];

        $sessionDisplay = $sessionMap[$sessionType] ?? 'Session Inconnue';
        $resultDisplay = $resultMap[$resultType] ?? 'Résultat Inconnu';

        return "{$sessionDisplay} - {$resultDisplay}";
    }

    /**
     * Show detailed view of a specific note
     */
    public function show($noteId, $table = 'notes_actu')
    {
        $student = Auth::user();

        $note = DB::table($table)
            ->where('id', $noteId)
            ->where('apoL_a01_code', $student->apoL_a01_code)
            ->first();

        if (!$note) {
            return redirect()->route('student.notes.index')
                           ->with('error', 'Note non trouvée.');
        }

        // Get Arabic name for this module and its PEL code
        $moduleDetail = Module::where('cod_elp', $note->code_module)
            ->first(['lib_elp_arb_fixed', 'cod_pel']);

        $note->display_name = $moduleDetail ? $moduleDetail->lib_elp_arb_fixed : $note->nom_module;
        $note->semester_name = $this->mapCodPelToSemester($moduleDetail ? $moduleDetail->cod_pel : null);

        // Map session display for the single note view
        if (isset($note->COD_SES)) {
            $codSesDisplayMap = [
                '1' => 'Session Normale',
                '2' => 'Session Rattrapage',
            ];
            $note->session_display_name = $codSesDisplayMap[$note->COD_SES] ?? 'Session Inconnue';
        } elseif (isset($note->session_type) && isset($note->result_type)) {
            $note->session_display_name = $this->mapCurrentSessionType($note->session_type, $note->result_type);
        } else {
             $note->session_display_name = 'Session Inconnue';
        }


        return view('student.notes.show', compact('note', 'student'));
    }
}
