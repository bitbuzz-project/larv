<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Models\Administrative;
use App\Models\PedaModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SituationPedagogiqueController extends Controller
{
    /**
     * Display the student's pedagogical situation
     */
    public function index()
    {
        $student = Auth::user();
        $currentYear = '2024-2025';

        // Get administrative data
        $administratives = Administrative::forStudent($student->apoL_a01_code)
                                       ->orderBy('annee_scolaire', 'desc')
                                       ->get();

        // Get current year administrative data
        $currentAdministrative = Administrative::forStudent($student->apoL_a01_code)
                                             ->currentYear($currentYear)
                                             ->first();

        // Get modules data (will be empty initially but ready for when you add modules)
        $modules = PedaModule::forStudent($student->apoL_a01_code)
                           ->currentYear($currentYear)
                           ->orderBy('semester')
                           ->orderBy('module_name')
                           ->get();

        // Group modules by semester
        $modulesBySemester = $modules->groupBy('semester');

        // Calculate statistics
        $stats = [
            'total_years' => $administratives->count(),
            'current_filiere' => $currentAdministrative->filliere ?? 'غير محدد',
            'current_year' => $currentYear,
            'total_modules' => $modules->count(),
            'active_modules' => $modules->where('status', 'active')->count(),
            'completed_modules' => $modules->where('status', 'completed')->count(),
            'total_credits' => $modules->sum('credits'),
        ];

        return view('student.situation-pedagogique.index', compact(
            'student',
            'administratives',
            'currentAdministrative',
            'modules',
            'modulesBySemester',
            'stats'
        ));
    }

    /**
     * Show details for a specific academic year
     */
    public function showYear($year)
    {
        $student = Auth::user();

        // Get administrative data for specific year
        $administrative = Administrative::forStudent($student->apoL_a01_code)
                                      ->where('annee_scolaire', $year)
                                      ->first();

        if (!$administrative) {
            return redirect()->route('student.situation-pedagogique.index')
                           ->with('error', 'لم يتم العثور على بيانات للسنة المحددة');
        }

        // Get modules for specific year
        $modules = PedaModule::forStudent($student->apoL_a01_code)
                           ->where('annee_scolaire', $year)
                           ->orderBy('semester')
                           ->orderBy('module_name')
                           ->get();

        // Group modules by semester
        $modulesBySemester = $modules->groupBy('semester');

        // Calculate year statistics
        $yearStats = [
            'total_modules' => $modules->count(),
            'active_modules' => $modules->where('status', 'active')->count(),
            'completed_modules' => $modules->where('status', 'completed')->count(),
            'failed_modules' => $modules->where('status', 'failed')->count(),
            'total_credits' => $modules->sum('credits'),
            'completed_credits' => $modules->where('status', 'completed')->sum('credits'),
        ];

        return view('student.situation-pedagogique.year-detail', compact(
            'student',
            'administrative',
            'modules',
            'modulesBySemester',
            'yearStats',
            'year'
        ));
    }
}
