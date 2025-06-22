<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Module;
use App\Models\PedaModule;
use App\Models\Administrative;
use App\Models\Reclamation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display comprehensive platform statistics
     */
    public function index()
    {
        // Get current year for filtering
        $currentYear = '2024-2025';

        // Basic Statistics
        $basicStats = [
            'total_students' => Student::count(),
            'total_modules' => Module::count(),
            'total_reclamations' => Reclamation::count(),
            'active_modules' => Module::where('eta_elp', 'A')->count(),
            'pending_reclamations' => Reclamation::where('status', 'pending')->count(),
            'resolved_reclamations' => Reclamation::where('status', 'resolved')->count(),
        ];

        // Student Statistics by Academic Year
        $studentsByYear = $this->getStudentsByAcademicYear();

        // Module Statistics
        $moduleStats = $this->getModuleStatistics();

        // Note Statistics
        $noteStats = $this->getNoteStatistics($currentYear);

        // Student Distribution by cod_dip (Diploma Code)
        $studentStats = $this->getStudentStatistics();

        // Reclamation Analytics
        $reclamationStats = $this->getReclamationAnalytics();

        // Performance Metrics
        $performanceMetrics = $this->getPerformanceMetrics($currentYear);

        // Recent Activity
        $recentActivity = $this->getRecentActivity();

        // Trend Analysis (last 12 months)
        $trendAnalysis = $this->getTrendAnalysis();

        return view('admin.reports.index', compact(
            'basicStats',
            'studentsByYear',
            'moduleStats',
            'noteStats',
            'studentStats',
            'reclamationStats',
            'performanceMetrics',
            'recentActivity',
            'trendAnalysis',
            'currentYear'
        ));
    }

    /**
     * Get students statistics by academic year
     */
    private function getStudentsByAcademicYear()
    {
        return Administrative::select('annee_scolaire', DB::raw('count(*) as count'))
            ->groupBy('annee_scolaire')
            ->orderBy('annee_scolaire', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get student statistics by diploma code (cod_dip)
     */
    private function getStudentStatistics()
    {
        $byDiploma = Student::select('cod_dip', DB::raw('count(*) as count'))
            ->whereNotNull('cod_dip')
            ->where('cod_dip', '!=', '')
            ->groupBy('cod_dip')
            ->orderBy('count', 'desc')
            ->get();

        $withoutDiploma = Student::whereNull('cod_dip')
            ->orWhere('cod_dip', '')
            ->count();

        $totalStudents = Student::count();

        return [
            'by_diploma' => $byDiploma,
            'without_diploma' => $withoutDiploma,
            'total_students' => $totalStudents,
            'with_diploma' => $totalStudents - $withoutDiploma,
        ];
    }

    /**
     * Get module statistics
     */
    private function getModuleStatistics()
    {
        return [
            'by_status' => Module::select('eta_elp', DB::raw('count(*) as count'))
                ->groupBy('eta_elp')
                ->get(),
            'by_component' => Module::select('cod_cmp', DB::raw('count(*) as count'))
                ->whereNotNull('cod_cmp')
                ->groupBy('cod_cmp')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'with_ects' => Module::whereNotNull('nbr_pnt_ect_elp')->count(),
            'without_ects' => Module::whereNull('nbr_pnt_ect_elp')->count(),
            'average_ects' => Module::whereNotNull('nbr_pnt_ect_elp')->avg('nbr_pnt_ect_elp'),
        ];
    }

    /**
     * Get note statistics
     */
    private function getNoteStatistics($currentYear)
    {
        // Current session notes
        $currentNotes = DB::table('notes_actu')
            ->where('annee_scolaire', $currentYear);

        // Historical notes
        $historicalNotes = DB::table('notes')
            ->where('annee_scolaire', $currentYear);

        return [
            'current_session' => [
                'total' => $currentNotes->count(),
                'by_session' => $currentNotes->select('session_type', DB::raw('count(*) as count'))
                    ->groupBy('session_type')
                    ->get(),
                'by_result_type' => $currentNotes->select('result_type', DB::raw('count(*) as count'))
                    ->groupBy('result_type')
                    ->get(),
                'average_grade' => $currentNotes->whereNotNull('note')->avg('note'),
                'passed_percentage' => $this->calculatePassPercentage($currentNotes),
            ],
            'historical' => [
                'total' => $historicalNotes->count(),
                'average_grade' => $historicalNotes->whereNotNull('note')->avg('note'),
                'passed_percentage' => $this->calculatePassPercentage($historicalNotes),
            ],
        ];
    }

    /**
     * Calculate pass percentage
     */
    private function calculatePassPercentage($query)
    {
        $total = $query->whereNotNull('note')->count();
        if ($total == 0) return 0;

        $passed = $query->where('note', '>=', 10)->count();
        return round(($passed / $total) * 100, 2);
    }

    /**
     * Get reclamation analytics
     */
    private function getReclamationAnalytics()
    {
        return [
            'by_status' => Reclamation::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'by_type' => Reclamation::select('reclamation_type', DB::raw('count(*) as count'))
                ->groupBy('reclamation_type')
                ->get(),
            'by_priority' => Reclamation::select('priority', DB::raw('count(*) as count'))
                ->groupBy('priority')
                ->get(),
            'recent_count' => Reclamation::where('created_at', '>=', now()->subDays(30))->count(),
            'response_time' => $this->calculateAverageResponseTime(),
        ];
    }

    /**
     * Calculate average response time for reclamations
     */
    private function calculateAverageResponseTime()
    {
        $resolved = Reclamation::where('status', 'resolved')
            ->whereNotNull('updated_at')
            ->get();

        if ($resolved->isEmpty()) return 0;

        $totalDays = 0;
        foreach ($resolved as $reclamation) {
            $totalDays += $reclamation->created_at->diffInDays($reclamation->updated_at);
        }

        return round($totalDays / $resolved->count(), 1);
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics($currentYear)
    {
        // Student inscriptions in modules
        $studentModules = PedaModule::where('annee_scolaire', $currentYear);

        return [
            'total_inscriptions' => $studentModules->count(),
            'active_inscriptions' => $studentModules->where('status', 'active')->count(),
            'completed_inscriptions' => $studentModules->where('status', 'completed')->count(),
            'average_modules_per_student' => $this->getAverageModulesPerStudent($currentYear),
            'most_popular_modules' => $this->getMostPopularModules($currentYear),
        ];
    }

    /**
     * Get average modules per student
     */
    private function getAverageModulesPerStudent($currentYear)
    {
        $totalInscriptions = PedaModule::where('annee_scolaire', $currentYear)->count();
        $uniqueStudents = PedaModule::where('annee_scolaire', $currentYear)
            ->distinct('apogee')
            ->count();

        return $uniqueStudents > 0 ? round($totalInscriptions / $uniqueStudents, 1) : 0;
    }

    /**
     * Get most popular modules
     */
    private function getMostPopularModules($currentYear)
    {
        return PedaModule::select('module_code', 'module_name', DB::raw('count(*) as enrollment_count'))
            ->where('annee_scolaire', $currentYear)
            ->groupBy('module_code', 'module_name')
            ->orderBy('enrollment_count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity()
    {
        return [
            'recent_students' => Student::orderBy('created_at', 'desc')->limit(5)->get(),
            'recent_reclamations' => Reclamation::with('student')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_modules' => Module::orderBy('created_at', 'desc')->limit(5)->get(),
        ];
    }

    /**
     * Get trend analysis for the last 12 months
     */
    private function getTrendAnalysis()
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->format('M Y'),
                'students_added' => Student::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'reclamations_created' => Reclamation::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'modules_added' => Module::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        return $months;
    }

    /**
     * Export report as PDF
     */
    public function exportPdf()
    {
        // This would integrate with a PDF library like dompdf or mpdf
        // For now, return a simple view that can be printed
        return $this->index();
    }

    /**
     * Get detailed analytics for a specific area
     */
    public function detailAnalytics($area)
    {
        // For now, return a generic detailed view
        // In the future, this will be expanded for each specific area
        return view('admin.reports.detail', compact('area'));
    }

    /**
     * Get detailed student analytics
     */
    private function getDetailedStudentAnalytics()
    {
        // Future implementation for detailed student analytics
        $studentData = [
            'by_year' => Student::selectRaw('YEAR(created_at) as year, COUNT(*) as count')
                ->groupBy('year')
                ->orderBy('year', 'desc')
                ->get(),
            'by_gender' => Student::select('cod_sex_etu', DB::raw('count(*) as count'))
                ->whereNotNull('cod_sex_etu')
                ->groupBy('cod_sex_etu')
                ->get(),
            'recent_registrations' => Student::orderBy('created_at', 'desc')->limit(10)->get(),
        ];

        return view('admin.reports.students-detail', compact('studentData'));
    }

    /**
     * Get detailed module analytics
     */
    private function getDetailedModuleAnalytics()
    {
        // Future implementation for detailed module analytics
        $moduleData = [
            'total_modules' => Module::count(),
            'active_modules' => Module::where('eta_elp', 'A')->count(),
            'modules_with_ects' => Module::whereNotNull('nbr_pnt_ect_elp')->count(),
            'by_component' => Module::select('cod_cmp', DB::raw('count(*) as count'))
                ->whereNotNull('cod_cmp')
                ->groupBy('cod_cmp')
                ->orderBy('count', 'desc')
                ->get(),
        ];

        return view('admin.reports.modules-detail', compact('moduleData'));
    }

    /**
     * Get detailed note analytics
     */
    private function getDetailedNoteAnalytics()
    {
        // Future implementation for detailed note analytics
        $noteData = [
            'total_notes' => DB::table('notes')->count() + DB::table('notes_actu')->count(),
            'average_grade' => DB::table('notes_actu')->avg('note'),
            'pass_rate' => $this->calculateOverallPassRate(),
            'grade_distribution' => $this->getGradeDistribution(),
        ];

        return view('admin.reports.notes-detail', compact('noteData'));
    }

    /**
     * Get detailed reclamation analytics
     */
    private function getDetailedReclamationAnalytics()
    {
        // Future implementation for detailed reclamation analytics
        $reclamationData = [
            'total_reclamations' => Reclamation::count(),
            'by_status' => Reclamation::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'by_type' => Reclamation::select('reclamation_type', DB::raw('count(*) as count'))
                ->groupBy('reclamation_type')
                ->get(),
            'recent_activity' => Reclamation::with('student')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return view('admin.reports.reclamations-detail', compact('reclamationData'));
    }

    /**
     * Calculate overall pass rate
     */
    private function calculateOverallPassRate()
    {
        $totalNotes = DB::table('notes_actu')->whereNotNull('note')->count();
        if ($totalNotes == 0) return 0;

        $passedNotes = DB::table('notes_actu')->where('note', '>=', 10)->count();
        return round(($passedNotes / $totalNotes) * 100, 2);
    }

    /**
     * Get grade distribution
     */
    private function getGradeDistribution()
    {
        return [
            'excellent' => DB::table('notes_actu')->whereBetween('note', [16, 20])->count(),
            'good' => DB::table('notes_actu')->whereBetween('note', [14, 15.99])->count(),
            'average' => DB::table('notes_actu')->whereBetween('note', [12, 13.99])->count(),
            'pass' => DB::table('notes_actu')->whereBetween('note', [10, 11.99])->count(),
            'fail' => DB::table('notes_actu')->where('note', '<', 10)->count(),
        ];
    }
}
