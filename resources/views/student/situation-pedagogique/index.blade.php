@extends('layouts.student')

@section('title', 'الوضعية البيداغوجية')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📚 الوضعية البيداغوجية</h2>
        <div class="btn-group">
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
                ⬅️ العودة للوحة التحكم
            </a>
        </div>
    </div>

    <!-- Current Year Overview -->
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
                                <strong>التخصص الحالي:</strong> {{ $stats['current_filiere'] }}
                            </p>
                            <p class="mb-0">
                                <strong>السنة الأكاديمية:</strong> {{ $stats['current_year'] }}
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 15px;">
                    <i style="font-size: 2.5rem; margin-bottom: 10px;">📊</i>
                    <h3 class="mb-1">{{ $stats['total_years'] }}</h3>
                    <p class="mb-0">سنوات دراسية</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 15px;">
                    <i style="font-size: 2.5rem; margin-bottom: 10px;">📖</i>
                    <h3 class="mb-1">{{ $stats['total_modules'] }}</h3>
                    <p class="mb-0">إجمالي المواد</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 15px;">
                    <i style="font-size: 2.5rem; margin-bottom: 10px;">✅</i>
                    <h3 class="mb-1">{{ $stats['active_modules'] }}</h3>
                    <p class="mb-0">مواد نشطة</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card h-100">
                <div class="card-body text-center" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; border-radius: 15px;">
                    <i style="font-size: 2.5rem; margin-bottom: 10px;">🏆</i>
                    <h3 class="mb-1">{{ $stats['total_credits'] }}</h3>
                    <p class="mb-0">مجموع الوحدات</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Administrative History -->
        <div class="col-lg-6 mb-4">
            <div class="card stat-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📋 تاريخ التسجيلات الأكاديمية</h5>
                </div>
                <div class="card-body">
                    @if($administratives->isEmpty())
                        <div class="text-center py-4">
                            <i style="font-size: 3rem; color: #6c757d;">📚</i>
                            <h6 class="mt-3 text-muted">لا توجد بيانات أكاديمية</h6>
                            <p class="text-muted">لم يتم العثور على تسجيلات أكاديمية</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>السنة الأكاديمية</th>
                                        <th>التخصص</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($administratives as $admin)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">{{ $admin->annee_scolaire }}</span>
                                        </td>
                                        <td>{{ $admin->formatted_filiere }}</td>
                                        <td>
                                            <a href="{{ route('student.situation-pedagogique.year', $admin->annee_scolaire) }}"
                                               class="btn btn-sm btn-outline-info">
                                                👁️ التفاصيل
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Current Year Modules -->
        <div class="col-lg-6 mb-4">
            <div class="card stat-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📖 مواد السنة الحالية</h5>
                </div>
                <div class="card-body">
                    @if($modules->isEmpty())
                        <div class="text-center py-4">
                            <i style="font-size: 3rem; color: #6c757d;">📚</i>
                            <h6 class="mt-3 text-muted">لا توجد مواد مسجلة</h6>
                            <p class="text-muted">سيتم إضافة المواد لاحقاً</p>
                        </div>
                    @else
                        @foreach($modulesBySemester as $semester => $semesterModules)
                        <div class="mb-3">
                            <h6 class="text-primary">{{ $semester }}</h6>
                            <div class="list-group">
                                @foreach($semesterModules as $module)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $module->full_module_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $module->module_code }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-secondary">{{ $module->credits }} وحدة</span>
                                        <br>
                                        <span class="badge {{ $module->status == 'active' ? 'bg-success' : 'bg-warning' }}">
                                            {{ $module->status == 'active' ? 'نشط' : 'مكتمل' }}
                                        </span>
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

    <!-- Current Administrative Details -->
    @if($currentAdministrative)
    <div class="row">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">📊 تفاصيل التسجيل الحالي</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>رقم أبوجي:</strong>
                            <p class="text-muted">{{ $currentAdministrative->apogee }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>التخصص:</strong>
                            <p class="text-muted">{{ $currentAdministrative->formatted_filiere }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>السنة الأكاديمية:</strong>
                            <p class="text-muted">{{ $currentAdministrative->annee_scolaire }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>تاريخ التسجيل:</strong>
                            <p class="text-muted">{{ $currentAdministrative->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>آخر تحديث:</strong>
                            <p class="text-muted">{{ $currentAdministrative->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
