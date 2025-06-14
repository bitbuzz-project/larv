@extends('layouts.student')

@section('title', 'تفاصيل السنة الأكاديمية')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📅 تفاصيل السنة الأكاديمية {{ $year }}</h2>
        <a href="{{ route('student.situation-pedagogique.index') }}" class="btn btn-secondary">
            ⬅️ العودة للوضعية البيداغوجية
        </a>
    </div>

    <!-- Year Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2">{{ $administrative->formatted_filiere }}</h3>
                            <p class="mb-1">
                                <strong>الطالب:</strong> {{ $student->full_name }}
                            </p>
                            <p class="mb-1">
                                <strong>رقم أبوجي:</strong> {{ $student->apoL_a01_code }}
                            </p>
                            <p class="mb-0">
                                <strong>السنة الأكاديمية:</strong> {{ $year }}
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

    <!-- Year Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 15px;">
                    <i style="font-size: 2.5rem; margin-bottom: 10px;">📖</i>
                    <h3 class="mb-1">{{ $yearStats['total_modules'] }}</h3>
                    <p class="mb-0">إجمالي المواد</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 15px;">
                    <i style="font-size: 2.5rem; margin-bottom: 10px;">✅</i>
                    <h3 class="mb-1">{{ $yearStats['completed_modules'] }}</h3>
                    <p class="mb-0">مواد مكتملة</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 15px;">
                    <i style="font-size: 2.5rem; margin-bottom: 10px;">🔄</i>
                    <h3 class="mb-1">{{ $yearStats['active_modules'] }}</h3>
                    <p class="mb-0">مواد نشطة</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; border-radius: 15px;">
                    <i style="font-size: 2.5rem; margin-bottom: 10px;">🏆</i>
                    <h3 class="mb-1">{{ $yearStats['completed_credits'] }}/{{ $yearStats['total_credits'] }}</h3>
                    <p class="mb-0">الوحدات المكتملة</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules by Semester -->
    <div class="row">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📚 المواد حسب الفصول الدراسية</h5>
                </div>
                <div class="card-body">
                    @if($modules->isEmpty())
                        <div class="text-center py-5">
                            <i style="font-size: 3rem; color: #6c757d;">📚</i>
                            <h6 class="mt-3 text-muted">لا توجد مواد مسجلة</h6>
                            <p class="text-muted">لم يتم تسجيل أي مواد لهذه السنة الأكاديمية</p>
                        </div>
                    @else
                        @foreach($modulesBySemester as $semester => $semesterModules)
                        <div class="mb-4">
                            <h6 class="text-primary border-bottom pb-2">{{ $semester }}</h6>
                            <div class="row">
                                @foreach($semesterModules as $module)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-left-primary">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $module->full_module_name }}</h6>
                                            <p class="card-text">
                                                <small class="text-muted">كود المادة: {{ $module->module_code }}</small>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge bg-info">{{ $module->credits }} وحدة</span>
                                                    <span class="badge bg-secondary">معامل {{ $module->coefficient }}</span>
                                                </div>
                                                <span class="badge {{
                                                    $module->status == 'active' ? 'bg-primary' :
                                                    ($module->status == 'completed' ? 'bg-success' :
                                                    ($module->status == 'failed' ? 'bg-danger' : 'bg-warning'))
                                                }}">
                                                    {{
                                                        $module->status == 'active' ? 'نشط' :
                                                        ($module->status == 'completed' ? 'مكتمل' :
                                                        ($module->status == 'failed' ? 'راسب' : 'منسحب'))
                                                    }}
                                                </span>
                                            </div>
                                            @if($module->professor)
                                            <div class="mt-2">
                                                <small class="text-muted">👨‍🏫 الأستاذ: {{ $module->professor }}</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
