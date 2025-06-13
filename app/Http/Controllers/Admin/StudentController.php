<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('apoL_a01_code', 'like', "%{$search}%")
                  ->orWhere('apoL_a02_nom', 'like', "%{$search}%")
                  ->orWhere('apoL_a03_prenom', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('apoL_a02_nom')
                         ->orderBy('apoL_a03_prenom')
                         ->paginate(15);

        return view('admin.students.index', compact('students'));
    }

    public function show($id)
    {
        $student = Student::findOrFail($id);

        // Get additional data
        $notes_count = 0; // Will implement when Note model is ready
        $reclamations_count = $student->reclamations()->count();

        return view('admin.students.show', compact('student', 'notes_count', 'reclamations_count'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'apoL_a01_code' => 'required|string|unique:students_base',
            'apoL_a02_nom' => 'required|string|max:100',
            'apoL_a03_prenom' => 'required|string|max:100',
            'apoL_a04_naissance' => 'required|string',
        ]);

        Student::create($request->all());

        return redirect()->route('admin.students.index')
                        ->with('success', 'Étudiant créé avec succès.');
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'apoL_a02_nom' => 'required|string|max:100',
            'apoL_a03_prenom' => 'required|string|max:100',
            'apoL_a04_naissance' => 'required|string',
        ]);

        $student->update($request->all());

        return redirect()->route('admin.students.index')
                        ->with('success', 'Étudiant mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('admin.students.index')
                        ->with('success', 'Étudiant supprimé avec succès.');
    }
}
