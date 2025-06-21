<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Models\PedaModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Get modules for selected year
        $modules = PedaModule::forStudent($student->apoL_a01_code)
                           ->where('annee_scolaire', $selectedYear)
                           ->orderBy('semester')
                           ->orderBy('module_name')
                           ->get();

        // Group modules by semester
        $modulesBySemester = $modules->groupBy('semester');

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
        $sessionType = $request->get('session', 'automne'); // Default to current session

        // Get current session modules
        $modules = PedaModule::forStudent($student->apoL_a01_code)
                           ->currentYear($currentYear)
                           ->where('status', 'active')
                           ->when($sessionType, function($query, $sessionType) {
                               return $query->where('session_type', $sessionType);
                           })
                           ->orderBy('semester')
                           ->orderBy('module_name')
                           ->get();

        // Group modules by semester
        $modulesBySemester = $modules->groupBy('semester');

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

        $module = PedaModule::forStudent($student->apoL_a01_code)
                          ->findOrFail($moduleId);

        return view('student.modules.show', compact('student', 'module'));
    }

    /**
     * Export modules as PDF
     */
    public function exportPdf(Request $request)
    {
        $student = Auth::user();
        $selectedYear = $request->get('year', '2024-2025');

        $modules = PedaModule::forStudent($student->apoL_a01_code)
                           ->where('annee_scolaire', $selectedYear)
                           ->orderBy('semester')
                           ->orderBy('module_name')
                           ->get();

        $modulesBySemester = $modules->groupBy('semester');

        // For now, return a simple view - you can integrate with PDF library later
        return view('student.modules.pdf', compact(
            'student',
            'modules',
            'modulesBySemester',
            'selectedYear'
        ));
    }
}
