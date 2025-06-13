<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Reclamation;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_students' => Student::count(),
            'total_reclamations' => Reclamation::count(),
            'pending_reclamations' => Reclamation::where('status', 'pending')->count(),
            'resolved_reclamations' => Reclamation::where('status', 'resolved')->count(),
        ];

        $recent_students = Student::orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_students'));
    }
}
