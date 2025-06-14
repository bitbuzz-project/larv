@extends('layouts.student')

@section('title', 'تعديل الملف الشخصي')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>✏️ تعديل الملف الشخصي</h2>
            <a href="{{ route('student.profile.show') }}" class="btn btn-secondary">
                ⬅️ العودة للملف الشخصي
            </a>
        </div>

        <!-- Edit Form -->
        <div class="card stat-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📝 تعديل المعلومات الشخصية</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('student.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <!-- Read-only Information -->
                    <div class="alert alert-info">
                        <strong>ملاحظة:</strong> بعض المعلومات مثل رقم أبوجي لا يمكن تعديلها لأسباب أمنية.
                    </div>

                    <!-- Apogee Code (Read-only) -->
                    <div class="mb-3">
                        <label for="apoL_a01_code" class="form-label">رقم أبوجي</label>
                        <input type="text" class="form-control bg-light" id="apoL_a01_code"
                               value="{{ $student->apoL_a01_code }}" readonly>
                        <small class="form-text text-muted">لا يمكن تعديل رقم أبوجي</small>
                    </div>

                    <!-- First Name -->
                    <div class="mb-3">
                        <label for="apoL_a03_prenom" class="form-label">الاسم الشخصي <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a03_prenom') is-invalid @enderror"
                               id="apoL_a03_prenom" name="apoL_a03_prenom"
                               value="{{ old('apoL_a03_prenom', $student->apoL_a03_prenom) }}" required>
                        @error('apoL_a03_prenom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div class="mb-3">
                        <label for="apoL_a02_nom" class="form-label">اسم العائلة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a02_nom') is-invalid @enderror"
                               id="apoL_a02_nom" name="apoL_a02_nom"
                               value="{{ old('apoL_a02_nom', $student->apoL_a02_nom) }}" required>
                        @error('apoL_a02_nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Birth Date -->
                    <div class="mb-3">
                        <label for="apoL_a04_naissance" class="form-label">تاريخ الميلاد <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a04_naissance') is-invalid @enderror"
                               id="apoL_a04_naissance" name="apoL_a04_naissance"
                               value="{{ old('apoL_a04_naissance', $student->apoL_a04_naissance) }}"
                               placeholder="DD/MM/YYYY" required>
                        @error('apoL_a04_naissance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">التنسيق: DD/MM/YYYY (مثال: 15/03/2000)</small>
                    </div>

                    <!-- Optional Fields Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cin_ind" class="form-label">رقم البطاقة الوطنية</label>
                                <input type="text" class="form-control @error('cin_ind') is-invalid @enderror"
                                       id="cin_ind" name="cin_ind"
                                       value="{{ old('cin_ind', $student->cin_ind) }}">
                                @error('cin_ind')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cod_sex_etu" class="form-label">الجنس</label>
                                <select class="form-control @error('cod_sex_etu') is-invalid @enderror"
                                        id="cod_sex_etu" name="cod_sex_etu">
                                    <option value="">-- اختر --</option>
                                    <option value="M" {{ old('cod_sex_etu', $student->cod_sex_etu) == 'M' ? 'selected' : '' }}>ذكر</option>
                                    <option value="F" {{ old('cod_sex_etu', $student->cod_sex_etu) == 'F' ? 'selected' : '' }}>أنثى</option>
                                </select>
                                @error('cod_sex_etu')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Birth Place -->
                    <div class="mb-3">
                        <label for="lib_vil_nai_etu" class="form-label">مكان الميلاد</label>
                        <input type="text" class="form-control @error('lib_vil_nai_etu') is-invalid @enderror"
                               id="lib_vil_nai_etu" name="lib_vil_nai_etu"
                               value="{{ old('lib_vil_nai_etu', $student->lib_vil_nai_etu) }}">
                        @error('lib_vil_nai_etu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('student.profile.show') }}" class="btn btn-secondary">
                            ❌ إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            💾 حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-format date input
document.getElementById('apoL_a04_naissance').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0,2) + '/' + value.substring(2);
    }
    if (value.length >= 5) {
        value = value.substring(0,5) + '/' + value.substring(5,9);
    }
    e.target.value = value;
});
</script>
@endsection
