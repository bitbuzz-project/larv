@extends('layouts.student')

@section('title', 'الملف الشخصي')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>👤 الملف الشخصي</h2>
            <div class="btn-group">
                <a href="{{ route('student.profile.edit') }}" class="btn btn-primary">
                    ✏️ تعديل المعلومات
                </a>
                <a href="{{ route('student.profile.change-password') }}" class="btn btn-outline-warning">
                    🔒 تغيير كلمة المرور
                </a>
            </div>
        </div>

        <!-- Profile Header Card -->
        <div class="card stat-card mb-4">
            <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center mx-auto"
                             style="width: 100px; height: 100px; font-size: 2.5rem;">
                            {{ $student->initials }}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h3 class="mb-2">{{ $student->full_name }}</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong>رقم أبوجي:</strong>
                                    <span class="badge bg-light text-dark fs-6">{{ $student->apoL_a01_code }}</span>
                                </p>
                                <p class="mb-1">
                                    <strong>تاريخ الميلاد:</strong> {{ $student->apoL_a04_naissance ?? 'غير محدد' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong>رقم الطالب:</strong> {{ $student->cod_etu ?? 'غير محدد' }}
                                </p>
                                <p class="mb-1">
                                    <strong>الجنس:</strong>
                                    {{ $student->cod_sex_etu == 'M' ? 'ذكر' : ($student->cod_sex_etu == 'F' ? 'أنثى' : 'غير محدد') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information Card -->
        <div class="card stat-card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">📋 المعلومات الشخصية</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>الاسم الشخصي:</strong>
                        <p class="text-muted">{{ $student->apoL_a03_prenom }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>اسم العائلة:</strong>
                        <p class="text-muted">{{ $student->apoL_a02_nom }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>رقم البطاقة الوطنية:</strong>
                        <p class="text-muted">{{ $student->cin_ind ?? 'غير محدد' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>مكان الميلاد:</strong>
                        <p class="text-muted">{{ $student->lib_vil_nai_etu ?? 'غير محدد' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Information Card -->
        @if($student->cod_etp || $student->lib_etp)
        <div class="card stat-card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">🎓 المعلومات الأكاديمية</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($student->cod_etp)
                    <div class="col-md-6 mb-3">
                        <strong>رمز التخصص:</strong>
                        <p class="text-muted">{{ $student->cod_etp }}</p>
                    </div>
                    @endif
                    @if($student->lib_etp)
                    <div class="col-md-6 mb-3">
                        <strong>التخصص:</strong>
                        <p class="text-muted">{{ $student->lib_etp }}</p>
                    </div>
                    @endif
                    @if($student->cod_anu)
                    <div class="col-md-6 mb-3">
                        <strong>السنة الأكاديمية:</strong>
                        <p class="text-muted">{{ $student->cod_anu }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Statistics Card -->
        <div class="card stat-card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">📊 إحصائيات الحساب</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-primary">{{ $profile_stats['total_reclamations'] }}</h4>
                            <small class="text-muted">إجمالي الشكاوى</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-warning">{{ $profile_stats['pending_reclamations'] }}</h4>
                            <small class="text-muted">شكاوى في الانتظار</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-success">{{ $profile_stats['resolved_reclamations'] }}</h4>
                            <small class="text-muted">شكاوى محلولة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information Card -->
        <div class="card stat-card">
            <div class="card-header bg-light">
                <h5 class="mb-0">ℹ️ معلومات النظام</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>تاريخ إنشاء الحساب:</strong>
                        <p class="text-muted">{{ $profile_stats['account_created']->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>آخر تحديث:</strong>
                        <p class="text-muted">{{ $profile_stats['last_updated']->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
