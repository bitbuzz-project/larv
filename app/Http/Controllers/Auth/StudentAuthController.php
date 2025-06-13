<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'apogee' => 'required|string',
            'birthdate' => 'required|date'
        ]);

        $apogee = $request->apogee;
        $birthdate = date('d/m/Y', strtotime($request->birthdate));

        // Special handling for admin user
        if ($apogee === '16005333') {
            $admin_birthdate = '06/04/1987';

            if ($birthdate === $admin_birthdate) {
                // Find or create admin user
                $admin = Student::firstOrCreate(
                    ['apoL_a01_code' => '16005333'],
                    [
                        'apoL_a02_nom' => 'Admin',
                        'apoL_a03_prenom' => 'System',
                        'apoL_a04_naissance' => '06/04/1987'
                    ]
                );

                Auth::login($admin);
                return redirect()->route('admin.dashboard');
            } else {
                return back()->withErrors(['error' => 'Invalid Admin credentials.']);
            }
        }

        // Regular student login
        $student = Student::where('apoL_a01_code', $apogee)
                         ->where('apoL_a04_naissance', $birthdate)
                         ->first();

        if ($student) {
            Auth::login($student);
            return redirect()->route('student.dashboard');
        }

        return back()->withErrors(['error' => 'Invalid Apogee or Birthdate.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
