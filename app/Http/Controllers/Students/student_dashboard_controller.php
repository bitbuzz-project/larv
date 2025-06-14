<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Get basic statistics
        $stats = [
            'total_notes' => 0, // Will implement when Note model is ready
            'total_reclamations' => $student->reclamations()->count(),
            'pending_reclamations' => $student->reclamations()->where('status', 'pending')->count(),
            'total_filieres' => 0, // Will implement when needed
        ];

        // Get recent notes (placeholder for now)
        $recent_notes = [];

        // Get recent reclamations
        $recent_reclamations = $student->reclamations()
                                      ->orderBy('created_at', 'desc')
                                      ->limit(3)
                                      ->get();

        return view('student.dashboard', compact('student', 'stats', 'recent_notes', 'recent_reclamations'));
    }
}
