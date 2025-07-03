<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Models\PedaModule;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModulesController extends Controller
{
    /**
     * Display student's modules
     */
    public function index(Request $request)
    {
        $student = Auth::user();
        $selectedYear = $request->get('year', '2024-2025');

        // Get all available years for this student
        $availableYears = PedaModule::forStudent($student->apoL_a01_code)
                                   ->distinct()
                                   ->pluck('annee_scolaire')
                                   ->sort()
                                   ->values();

        // If no years found, provide default
        if ($availableYears->isEmpty()) {
            $availableYears = collect(['2024-2025']);
        }

        // Get modules for selected year with dynamic semester determination
        $modules = $this->getModulesWithCorrectSemesters($student->apoL_a01_code, $selectedYear);

        // Group modules by semester and sort
        $modulesBySemester = $modules->groupBy('display_semester')->sortKeys();

        // Calculate statistics
        $stats = [
            'total_modules' => $modules->count(),
            'active_modules' => $modules->where('status', 'active')->count(),
            'completed_modules' => $modules->where('status', 'completed')->count(),
            'failed_modules' => $modules->where('status', 'failed')->count(),
            'total_credits' => $modules->sum('credits'),
            'completed_credits' => $modules->where('status', 'completed')->sum('credits'),
        ];

        return view('student.modules.index', compact(
            'student',
            'modules',
            'modulesBySemester',
            'stats',
            'selectedYear',
            'availableYears'
        ));
    }

    /**
     * Show current session modules
     */
    public function currentSession(Request $request)
    {
        $student = Auth::user();
        $currentYear = '2024-2025';
        $sessionType = $request->get('session', 'automne');

        // Get current session modules with dynamic semester determination
        $modules = $this->getModulesWithCorrectSemesters(
            $student->apoL_a01_code,
            $currentYear,
            ['status' => 'active', 'session_type' => $sessionType]
        );

        // Group modules by semester and sort
        $modulesBySemester = $modules->groupBy('display_semester')->sortKeys();

        // Get available session types
        $availableSessions = PedaModule::forStudent($student->apoL_a01_code)
                                     ->currentYear($currentYear)
                                     ->where('status', 'active')
                                     ->whereNotNull('session_type')
                                     ->distinct()
                                     ->pluck('session_type');

        // Calculate statistics
        $stats = [
            'total_modules' => $modules->count(),
            'total_credits' => $modules->sum('credits'),
            'sessions_count' => $availableSessions->count(),
            'current_session' => $sessionType,
        ];

        return view('student.modules.current-session', compact(
            'student',
            'modules',
            'modulesBySemester',
            'stats',
            'sessionType',
            'availableSessions',
            'currentYear'
        ));
    }

    /**
     * Show module details
     */
    public function show($moduleId)
    {
        $student = Auth::user();

        $module = PedaModule::forStudent($student->apoL_a01_code)->findOrFail($moduleId);

        // Add display semester
        $module->display_semester = $this->determineSemesterFromModuleCode($module->module_code);

        return view('student.modules.show', compact('student', 'module'));
    }

    /**
     * Export modules as PDF
     */
    public function exportPdf(Request $request)
    {
        $student = Auth::user();
        $selectedYear = $request->get('year', '2024-2025');

        $modules = $this->getModulesWithCorrectSemesters($student->apoL_a01_code, $selectedYear);
        $modulesBySemester = $modules->groupBy('display_semester')->sortKeys();

        return view('student.modules.pdf', compact(
            'student',
            'modules',
            'modulesBySemester',
            'selectedYear'
        ));
    }

    /**
     * Get modules with correct semester determination (MAIN LOGIC)
     */
    private function getModulesWithCorrectSemesters($apogee, $year, $filters = [])
    {
        // Build the query
        $query = DB::table('peda_modules')
            ->leftJoin('modules', 'peda_modules.module_code', '=', 'modules.cod_elp')
            ->where('peda_modules.apogee', $apogee)
            ->where('peda_modules.annee_scolaire', $year)
            ->select(
                'peda_modules.*',
                'modules.cod_pel',
                'modules.lib_elp_arb_fixed as module_arabic_name'
            );

        // Apply additional filters
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where('peda_modules.' . $field, $value);
            }
        }

        $modules = $query->get();

        // Add display_semester to each module
        return $modules->map(function ($module) {
            $module->display_semester = $this->determineSemesterFromModuleCode(
                $module->module_code,
                $module->cod_pel
            );
            return $module;
        });
    }

    /**
     * Determine semester from module code and cod_pel
     */
    private function determineSemesterFromModuleCode($moduleCode, $codPel = null)
    {
        // If we don't have cod_pel, get it from modules table
        if (!$codPel) {
            $moduleRecord = DB::table('modules')
                ->where('cod_elp', $moduleCode)
                ->first();
            $codPel = $moduleRecord ? $moduleRecord->cod_pel : null;
        }

        if ($codPel) {
            return $this->extractSemesterFromCodPel($codPel);
        }

        // Fallback: try to determine from module code itself
        return $this->extractSemesterFromCode($moduleCode);
    }

    /**
     * Extract semester from cod_pel
     */
    private function extractSemesterFromCodPel($codPel)
    {
        if (!$codPel) return 'S1';

        $codPel = strtoupper(trim($codPel));

        // Pattern 1: Direct semester notation (S1, S2, etc.)
        if (preg_match('/S([1-6])/', $codPel, $matches)) {
            return 'S' . $matches[1];
        }

        // Pattern 2: Look for standalone numbers 1-6
        if (preg_match('/\b([1-6])\b/', $codPel, $matches)) {
            return 'S' . $matches[1];
        }

        // Pattern 3: Semester in words (SEM1, SEMESTER1, etc.)
        if (preg_match('/SEM(?:ESTER)?[^\d]*([1-6])/i', $codPel, $matches)) {
            return 'S' . $matches[1];
        }

        // Pattern 4: Numbers at the end
        if (preg_match('/([1-6])$/', $codPel, $matches)) {
            return 'S' . $matches[1];
        }

        // Pattern 5: Extract first number found
        if (preg_match('/([1-6])/', $codPel, $matches)) {
            return 'S' . $matches[1];
        }

        return 'S1'; // Default fallback
    }

    /**
     * Extract semester from module code as fallback
     */
    private function extractSemesterFromCode($moduleCode)
    {
        if (!$moduleCode) return 'S1';

        $moduleCode = strtoupper(trim($moduleCode));

        // Look for patterns in module code
        if (preg_match('/S([1-6])/', $moduleCode, $matches)) {
            return 'S' . $matches[1];
        }

        if (preg_match('/([1-6])/', $moduleCode, $matches)) {
            return 'S' . $matches[1];
        }

        return 'S1'; // Default fallback
    }
}
