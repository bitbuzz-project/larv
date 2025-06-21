@extends('layouts.student')

@section('title', 'المواد الحالية')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📚 المواد الحالية - {{ $currentYear }}</h2>
        <div class="btn-group">
            <a href="{{ route('student.modules.index') }}" class="btn btn-secondary">
                📋 جميع المواد
            </a>
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                ⬅️ لوحة التحكم
            </a>
        </div>
    </div>

    <!-- Current Session Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2">{{ $student->full_name }}</h3>
                            <p class="mb-1">
                                <strong>رقم أبوجي:</strong> {{ $student->apoL_a01_code }}
                            </p>
                            <p class="mb-1">
                                <strong>السنة الجامعية:</strong> {{ $currentYear }}
                            </p>
                            <p class="mb-0">
                                <strong>الدورة الحالية:</strong>
                                {{ $sessionType == 'printemps' ? 'الربيع' : 'الخريف' }}
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center"
                                 style="width: 80px; height: 80px; font-size: 2rem;">
                                📚
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Filter -->
    @if($availableSessions->isNotEmpty())
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <h6>اختر الدورة:</h6>
                    <div class="btn-group w-100" role="group">
                        @foreach($availableSessions as $session)
                        <a href="{{ route('student.modules.current-session', ['session' => $session]) }}"
                           class="btn {{ $sessionType == $session ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $session == 'printemps' ? 'دورة الربيع' : 'دورة الخريف' }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 15px;">
                    <div class="row">
                        <div class="col-6">
                            <h4>{{ $stats['total_modules'] }}</h4>
                            <small>مواد الدورة</small>
                        </div>
                        <div class="col-6">
                            <h4>{{ $stats['total_credits'] }}</h4>
                            <small>إجمالي الوحدات</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($modules->isEmpty())
        <!-- No Current Modules -->
        <div class="row">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-body text-center py-5">
                        <i style="font-size: 4rem; color: #6c757d;">📚</i>
                        <h4 class="mt-3 text-muted">لا توجد مواد في الدورة الحالية</h4>
                        <p class="text-muted">
                            لم يتم العثور على مواد نشطة في
                            {{ $sessionType == 'printemps' ? 'دورة الربيع' : 'دورة الخريف' }}
                        </p>
                        @if($availableSessions->count() > 1)
                        <p class="text-muted">جرب اختيار دورة أخرى من الأعلى</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Current Session Modules -->
        @foreach($modulesBySemester as $semester => $semesterModules)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">📖 {{ $semester }} - {{ $sessionType == 'printemps' ? 'دورة الربيع' : 'دورة الخريف' }}</h5>
                            <span class="badge bg-light text-dark">
                                {{ $semesterModules->count() }} مادة | {{ $semesterModules->sum('credits') }} وحدة
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($semesterModules as $module)
                            <div class="col-lg-6 mb-3">
                                <div class="card h-100 border-start border-success border-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ $module->module_name }}</h6>
                                            <span class="badge bg-success">نشط</span>
                                        </div>

                                        @if($module->module_name_ar)
                                        <p class="text-muted small mb-2">{{ $module->module_name_ar }}</p>
                                        @endif

                                        <p class="text-muted small mb-2">
                                            <strong>كود المادة:</strong> {{ $module->module_code }}
                                        </p>

                                        <div class="row text-center mb-2">
                                            <div class="col-6">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-primary">{{ $module->credits }}</h6>
                                                    <small class="text-muted">وحدة</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-info">{{ $module->coefficient }}</h6>
                                                    <small class="text-muted">معامل</small>
                                                </div>
                                            </div>
                                        </div>

                                        @if($module->professor)
                                        <div class="mb-2 p-2 bg-light rounded">
                                            <small>
                                                👨‍🏫 <strong>الأستاذ:</strong> {{ $module->professor }}
                                            </small>
                                        </div>
                                        @endif

                                        @if($module->schedule)
                                        <div class="mb-2 p-2 bg-light rounded">
                                            <small>
                                                🕐 <strong>التوقيت:</strong> {{ $module->schedule }}
                                            </small>
                                        </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">
                                                📅 {{ $sessionType == 'printemps' ? 'دورة الربيع' : 'دورة الخريف' }}
                                            </small>
                                            <a href="{{ route('student.modules.show', $module->id) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                عرض التفاصيل
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">⚡ إجراءات سريعة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('student.modules.export-pdf', ['year' => $currentYear, 'session' => $sessionType]) }}"
                                   class="btn btn-outline-danger w-100">
                                    📄 تصدير PDF
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('student.modules.index') }}" class="btn btn-outline-primary w-100">
                                    📋 جميع المواد
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('student.situation-pedagogique.index') }}" class="btn btn-outline-success w-100">
                                    📚 الوضعية البيداغوجية
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-outline-warning w-100" onclick="window.print()">
                                    🖨️ طباعة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
@media print {
    .btn, .card-header, .btn-group {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}
</style>
@endsection
