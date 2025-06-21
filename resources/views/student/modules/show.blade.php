@extends('layouts.student')

@section('title', 'تفاصيل المادة')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📖 تفاصيل المادة</h2>
        <div class="btn-group">
            <a href="{{ route('student.modules.index') }}" class="btn btn-secondary">
                ⬅️ العودة للمواد
            </a>
            <a href="{{ route('student.modules.current-session') }}" class="btn btn-outline-primary">
                📚 المواد الحالية
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Module Details Card -->
            <div class="card stat-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📋 معلومات المادة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-3">{{ $module->module_name }}</h4>

                            @if($module->module_name_ar)
                            <h5 class="text-muted mb-3">{{ $module->module_name_ar }}</h5>
                            @endif

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>كود المادة:</strong>
                                    <p class="text-muted">{{ $module->module_code }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>الفصل الدراسي:</strong>
                                    <p class="text-muted">{{ $module->semester }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>السنة الجامعية:</strong>
                                    <p class="text-muted">{{ $module->annee_scolaire }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>حالة المادة:</strong>
                                    <span class="badge {{
                                        $module->status == 'active' ? 'bg-success' :
                                        ($module->status == 'completed' ? 'bg-info' :
                                        ($module->status == 'failed' ? 'bg-danger' : 'bg-warning'))
                                    }} fs-6">
                                        {{ $module->status_label }}
                                    </span>
                                </div>
                            </div>

                            @if($module->session_type)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>نوع الدورة:</strong>
                                    <p class="text-muted">{{ $module->session_type == 'printemps' ? 'دورة الربيع' : 'دورة الخريف' }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                                 style="width: 100px; height: 100px; font-size: 3rem;">
                                📖
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border rounded p-2 mb-2">
                                        <h5 class="mb-0 text-primary">{{ $module->credits }}</h5>
                                        <small class="text-muted">وحدة</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2 mb-2">
                                        <h5 class="mb-0 text-info">{{ $module->coefficient }}</h5>
                                        <small class="text-muted">معامل</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professor and Schedule Info -->
            @if($module->professor || $module->schedule)
            <div class="card stat-card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">👨‍🏫 معلومات التدريس</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($module->professor)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-3"
                                     style="width: 50px; height: 50px; font-size: 1.5rem;">
                                    👨‍🏫
                                </div>
                                <div>
                                    <h6 class="mb-0">الأستاذ المسؤول</h6>
                                    <p class="text-muted mb-0">{{ $module->professor }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($module->schedule)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center me-3"
                                     style="width: 50px; height: 50px; font-size: 1.5rem;">
                                    🕐
                                </div>
                                <div>
                                    <h6 class="mb-0">التوقيت</h6>
                                    <p class="text-muted mb-0">{{ $module->schedule }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card stat-card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">⚡ إجراءات سريعة</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            🖨️ طباعة تفاصيل المادة
                        </button>
                        <a href="{{ route('student.modules.current-session') }}" class="btn btn-outline-success">
                            📚 المواد الحالية
                        </a>
                        <a href="{{ route('student.modules.index') }}" class="btn btn-outline-info">
                            📋 جميع المواد
                        </a>
                        <button class="btn btn-outline-secondary" onclick="copyModuleInfo()">
                            📋 نسخ معلومات المادة
                        </button>
                    </div>
                </div>
            </div>

            <!-- Module Status Info -->
            <div class="card stat-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">📊 حالة المادة</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge {{
                            $module->status == 'active' ? 'bg-success' :
                            ($module->status == 'completed' ? 'bg-info' :
                            ($module->status == 'failed' ? 'bg-danger' : 'bg-warning'))
                        }} fs-5 p-3">
                            {{ $module->status_label }}
                        </span>
                    </div>

                    @if($module->status == 'active')
                    <p class="text-success">
                        <i style="font-size: 1.5rem;">✅</i><br>
                        مادة نشطة في الفصل الحالي
                    </p>
                    @elseif($module->status == 'completed')
                    <p class="text-info">
                        <i style="font-size: 1.5rem;">🏆</i><br>
                        تم إنجاز هذه المادة بنجاح
                    </p>
                    @elseif($module->status == 'failed')
                    <p class="text-danger">
                        <i style="font-size: 1.5rem;">❌</i><br>
                        يجب إعادة هذه المادة
                    </p>
                    @else
                    <p class="text-warning">
                        <i style="font-size: 1.5rem;">⚠️</i><br>
                        حالة غير محددة
                    </p>
                    @endif
                </div>
            </div>

            <!-- System Information -->
            <div class="card stat-card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">ℹ️ معلومات النظام</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>تاريخ التسجيل:</strong><br>
                        {{ $module->created_at ? $module->created_at->format('d/m/Y H:i') : 'غير متوفر' }}<br><br>

                        <strong>آخر تحديث:</strong><br>
                        {{ $module->updated_at ? $module->updated_at->format('d/m/Y H:i') : 'غير متوفر' }}<br><br>

                        <strong>رقم المادة في النظام:</strong><br>
                        #{{ $module->id }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyModuleInfo() {
    const moduleInfo = `
معلومات المادة:
اسم المادة: {{ $module->module_name }}
كود المادة: {{ $module->module_code }}
عدد الوحدات: {{ $module->credits }}
المعامل: {{ $module->coefficient }}
الفصل الدراسي: {{ $module->semester }}
السنة الجامعية: {{ $module->annee_scolaire }}
الحالة: {{ $module->status_label }}
@if($module->professor)الأستاذ: {{ $module->professor }}@endif
@if($module->schedule)التوقيت: {{ $module->schedule }}@endif
    `.trim();

    navigator.clipboard.writeText(moduleInfo).then(function() {
        alert('تم نسخ معلومات المادة إلى الحافظة');
    }, function(err) {
        console.error('خطأ في النسخ: ', err);
        alert('حدث خطأ أثناء النسخ');
    });
}
</script>

<style>
@media print {
    .btn, .card-header, .btn-group {
        display: none !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
    }
    .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
@endsection
