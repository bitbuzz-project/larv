<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Models\ModuleArabic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Module;

class StudentsNoteController extends Controller
{
    /**
     * Display student's notes
     */
    public function index(Request $request)
    {
        $student = Auth::user();
        $currentYear = '2024-2025';

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

        // Get current session notes (notes_actu)
        $currentNotesQuery = DB::table('notes_actu')
            ->where('apoL_a01_code', $student->apoL_a01_code)
            ->where('annee_scolaire', $selectedYear);

        if ($selectedSession !== 'all') {
            $currentNotesQuery->where('session_type', $selectedSession);
        }

        if ($selectedResultType !== 'all') {
            $currentNotesQuery->where('result_type', $selectedResultType);
        }

        $currentNotes = $currentNotesQuery->orderBy('code_module')->get();

        // Get old session notes (notes table)
        $oldNotes = collect();
        if ($selectedYear !== $currentYear || $selectedSession === 'all') {
            $oldNotesQuery = DB::table('notes')
                ->where('apoL_a01_code', $student->apoL_a01_code)
                ->where('annee_scolaire', $selectedYear);

            $oldNotes = $oldNotesQuery->orderBy('code_module')->get();
        }

        // Get all module codes to fetch Arabic names
        $allNotes = $currentNotes->concat($oldNotes);
        $moduleCodes = $allNotes->pluck('code_module')->unique();

        // Get Arabic module names from modules table
        $arabicModules = Module::whereIn('cod_elp', $moduleCodes)
            ->pluck('lib_elp_arb_fixed', 'cod_elp');

        // Add Arabic names to notes
        $currentNotes = $currentNotes->map(function ($note) use ($arabicModules) {
            $note->display_name = $arabicModules->get($note->code_module, $note->nom_module);
            return $note;
        });

        $oldNotes = $oldNotes->map(function ($note) use ($arabicModules) {
            $note->display_name = $arabicModules->get($note->code_module, $note->nom_module);
            return $note;
        });

        // Combine and organize notes
        $notesBySession = [];

        // Group current session notes
        foreach ($currentNotes as $note) {
            $sessionKey = $note->session_type . '_' . $note->result_type;
            if (!isset($notesBySession[$sessionKey])) {
                $notesBySession[$sessionKey] = [
                    'session_type' => $note->session_type,
                    'result_type' => $note->result_type,
                    'is_current' => true,
                    'notes' => collect()
                ];
            }
            $notesBySession[$sessionKey]['notes']->push($note);
        }

        // Add old session notes
        if ($oldNotes->isNotEmpty()) {
            $notesBySession['old_sessions'] = [
                'session_type' => 'Anciennes sessions',
                'result_type' => '',
                'is_current' => false,
                'notes' => $oldNotes
            ];
        }

        // Calculate statistics
        $allNotesWithNames = $currentNotes->concat($oldNotes);
        $stats = [
            'total_modules' => $allNotesWithNames->count(),
            'passed_modules' => $allNotesWithNames->where('note', '>=', 10)->count(),
            'failed_modules' => $allNotesWithNames->where('note', '<', 10)->where('note', '!=', null)->count(),
            'average_grade' => $allNotesWithNames->where('note', '!=', null)->avg('note'),
            'highest_grade' => $allNotesWithNames->where('note', '!=', null)->max('note'),
            'lowest_grade' => $allNotesWithNames->where('note', '!=', null)->min('note'),
        ];

        return view('student.notes.index', compact(
            'student',
            'notesBySession',
            'stats',
            'availableYears',
            'selectedYear',
            'selectedSession',
            'selectedResultType',
            'currentYear'
        ));
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

        // Get Arabic name for this module
        $arabicName = ModuleArabic::getArabicName($note->code_module);
        $note->display_name = $arabicName ?: $note->nom_module;

        return view('student.notes.show', compact('note', 'student'));
    }
}
