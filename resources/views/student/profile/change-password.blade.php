@extends('layouts.student')

@section('title', 'تغيير كلمة المرور')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🔒 تغيير كلمة المرور</h2>
            <a href="{{ route('student.profile.show') }}" class="btn btn-secondary">
                ⬅️ العودة للملف الشخصي
            </a>
        </div>

        <!-- Change Password Form -->
        <div class="card stat-card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">🔐 تحديث تاريخ الميلاد (كلمة المرور)</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>ملاحظة:</strong> تاريخ الميلاد يُستخدم كلمة مرور للدخول إلى النظام.
                </div>

                <form method="POST" action="{{ route('student.profile.update-password') }}">
                    @csrf

                    <!-- Current Birth Date -->
                    <div class="mb-3">
                        <label for="current_birthdate" class="form-label">تاريخ الميلاد الحالي <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('current_birthdate') is-invalid @enderror"
                               id="current_birthdate" name="current_birthdate" required>
                        @error('current_birthdate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Birth Date -->
                    <div class="mb-3">
                        <label for="new_birthdate" class="form-label">تاريخ الميلاد الجديد <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('new_birthdate') is-invalid @enderror"
                               id="new_birthdate" name="new_birthdate" required>
                        @error('new_birthdate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm New Birth Date -->
                    <div class="mb-3">
                        <label for="new_birthdate_confirmation" class="form-label">تأكيد تاريخ الميلاد الجديد <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('new_birthdate_confirmation') is-invalid @enderror"
                               id="new_birthdate_confirmation" name="new_birthdate_confirmation" required>
                        @error('new_birthdate_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Security Warning -->
                    <div class="alert alert-warning">
                        <strong>تحذير:</strong> بعد تغيير تاريخ الميلاد، ستحتاج لاستخدام التاريخ الجديد لتسجيل الدخول.
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('student.profile.show') }}" class="btn btn-secondary">
                            ❌ إلغاء
                        </a>
                        <button type="submit" class="btn btn-warning">
                            🔒 تحديث كلمة المرور
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
