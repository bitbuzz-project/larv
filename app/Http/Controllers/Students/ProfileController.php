<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the student's profile
     */
    public function show()
    {
        $student = Auth::user();

        // Get additional stats for profile
        $profile_stats = [
            'total_reclamations' => $student->reclamations()->count(),
            'pending_reclamations' => $student->reclamations()->where('status', 'pending')->count(),
            'resolved_reclamations' => $student->reclamations()->where('status', 'resolved')->count(),
            'account_created' => $student->created_at,
            'last_updated' => $student->updated_at,
        ];

        return view('student.profile.show', compact('student', 'profile_stats'));
    }

    /**
     * Show the form for editing the student's profile
     */
    public function edit()
    {
        $student = Auth::user();
        return view('student.profile.edit', compact('student'));
    }

    /**
     * Update the student's profile information
     */
    public function update(Request $request)
    {
        $student = Auth::user();

        // Validation rules
        $validated = $request->validate([
            'apoL_a02_nom' => 'required|string|max:100',
            'apoL_a03_prenom' => 'required|string|max:100',
            'apoL_a04_naissance' => 'required|string|max:20',
            'cin_ind' => 'nullable|string|max:20',
            'lib_vil_nai_etu' => 'nullable|string|max:100',
            'cod_sex_etu' => 'nullable|in:M,F',
        ], [
            'apoL_a02_nom.required' => 'اسم العائلة مطلوب',
            'apoL_a02_nom.max' => 'اسم العائلة يجب أن يكون أقل من 100 حرف',
            'apoL_a03_prenom.required' => 'الاسم الشخصي مطلوب',
            'apoL_a03_prenom.max' => 'الاسم الشخصي يجب أن يكون أقل من 100 حرف',
            'apoL_a04_naissance.required' => 'تاريخ الميلاد مطلوب',
            'cin_ind.max' => 'رقم البطاقة الوطنية يجب أن يكون أقل من 20 حرف',
            'lib_vil_nai_etu.max' => 'مكان الميلاد يجب أن يكون أقل من 100 حرف',
            'cod_sex_etu.in' => 'الجنس يجب أن يكون ذكر أو أنثى',
        ]);

        try {
            $student->update($validated);

            return redirect()->route('student.profile.show')
                           ->with('success', 'تم تحديث المعلومات الشخصية بنجاح');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث المعلومات');
        }
    }

    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        return view('student.profile.change-password');
    }

    /**
     * Update password (birth date in this case)
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_birthdate' => 'required|date',
            'new_birthdate' => 'required|date|different:current_birthdate',
            'new_birthdate_confirmation' => 'required|same:new_birthdate',
        ], [
            'current_birthdate.required' => 'تاريخ الميلاد الحالي مطلوب',
            'new_birthdate.required' => 'تاريخ الميلاد الجديد مطلوب',
            'new_birthdate.different' => 'تاريخ الميلاد الجديد يجب أن يكون مختلف عن الحالي',
            'new_birthdate_confirmation.same' => 'تأكيد تاريخ الميلاد غير متطابق',
        ]);

        $student = Auth::user();
        $currentBirthdate = date('d/m/Y', strtotime($request->current_birthdate));

        if ($student->apoL_a04_naissance !== $currentBirthdate) {
            return back()->withErrors(['current_birthdate' => 'تاريخ الميلاد الحالي غير صحيح']);
        }

        $newBirthdate = date('d/m/Y', strtotime($request->new_birthdate));

        try {
            $student->update(['apoL_a04_naissance' => $newBirthdate]);

            return redirect()->route('student.profile.show')
                           ->with('success', 'تم تحديث تاريخ الميلاد بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تحديث تاريخ الميلاد');
        }
    }
}
