@extends('layouts.student')

@section('title', 'لوحة التحكم')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1">
                            👋 مرحباً، {{ $student->apoL_a03_prenom }}!
                        </h2>
                        <p class="mb-0 opacity-75">
                            لوحة التحكم الجامعية - العام الدراسي 2024/2025
                        </p>
                        <small class="opacity-50">
                            رقم أبوجي: {{ $student->apoL_a01_code }}
                        </small>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ $student->initials }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 15px;">
                <i style="font-size: 3rem; margin-bottom: 15px;">📊</i>
                <h3 class="mb-1">{{ $stats['total_notes'] }}</h3>
                <p class="mb-0">المجموع الكلي للنقط</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 15px;">
                <i style="font-size: 3rem; margin-bottom: 15px;">⚠️</i>
                <h3 class="mb-1">{{ $stats['total_reclamations'] }}</h3>
                <p class="mb-0">الشكاوى</p>
                @if($stats['pending_reclamations'] > 0)
                    <small class="opacity-75">{{ $stats['pending_reclamations'] }} في الانتظار</small>
                @else
                    <small class="opacity-75">تم معالجة الكل</small>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 15px;">
                <i style="font-size: 3rem; margin-bottom: 15px;">✅</i>
                <h3 class="mb-1">{{ $stats['resolved_reclamations'] }}</h3>
                <p class="mb-0">شكاوى محلولة</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; border-radius: 15px;">
                <i style="font-size: 3rem; margin-bottom: 15px;">📅</i>
                <h3 class="mb-1">2024-25</h3>
                <p class="mb-0">العام الحالي</p>
                <small class="text-muted">السنة الجامعية</small>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Recent Notes Section -->
    <div class="col-lg-8">
        <div class="card stat-card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    📈 النقط الأخيرة
                </h5>
            </div>
            <div class="card-body">
                @if(empty($recent_notes))
                    <div class="text-center py-5">
                        <i style="font-size: 3rem; color: #6c757d;">📊</i>
                        <h6 class="mt-3 text-muted">لا توجد نقط متاحة</h6>
                        <p class="text-muted">ستظهر النتائج هنا بعد نشرها</p>
                    </div>
                @else
                    <!-- Notes will be displayed here when implemented -->
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Section -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card stat-card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    ⚡ إجراءات سريعة
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                <a href="{{ route('student.profile.show') }}" class="btn btn-outline-primary">
                    👤 ملفي الشخصي
                </a>
                <a href="#" class="btn btn-outline-success">
                    📋 تسجيلاتي
                </a>
                <a href="#" class="btn btn-outline-info">
                    📚 الوضعية البيداغوجية
                </a>
            </div>
            </div>
        </div>

        <!-- Recent Reclamations -->
        @if($recent_reclamations->isNotEmpty())
        <div class="card stat-card mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    ⚠️ الشكاوى الأخيرة
                </h5>
            </div>
            <div class="card-body">
                @foreach($recent_reclamations as $reclamation)
                    <div class="border-bottom pb-2 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $reclamation->default_name ?? 'شكوى' }}</h6>
                                @if($reclamation->prof)
                                    <small class="text-muted">
                                        الأستاذ: {{ $reclamation->prof }}
                                    </small>
                                @endif
                            </div>
                            <span class="badge {{ $reclamation->status === 'resolved' ? 'bg-success' : 'bg-warning' }}">
                                {{ $reclamation->status_label }}
                            </span>
                        </div>
                        <small class="text-muted">
                            {{ $reclamation->created_at->format('d/m/Y') }}
                        </small>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Help & Support -->
        <div class="card stat-card">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">
                    💬 المساعدة والدعم
                </h5>
            </div>
            <div class="card-body text-center">
                <i style="font-size: 2rem; color: #28a745;">📧</i>
                <h6 class="mt-2">تحتاج مساعدة؟</h6>
                <p class="text-muted small">
                    اتصل بالدعم الفني لأي استفسار
                </p>
                <a href="mailto:support@fsjs.ac.ma" class="btn btn-outline-success btn-sm">
                    اتصل بالدعم
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
