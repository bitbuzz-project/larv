@extends('layouts.student')

@section('title', 'مواد الدراسة')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📚 مواد الدراسة</h2>
        <div class="btn-group">
            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">
                ⬅️ العودة للوحة التحكم
            </a>
        </div>
    </div>

    <!-- Year Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.modules.index') }}">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label for="year_filter" class="form-label">اختر السنة الجامعية:</label>
                                <select name="year" id="year_filter" class="form-select">
                                    @foreach($availableYears as $year)
                                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">عرض</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card">
                <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4>{{ $stats['total_modules'] }}</h4>
                            <small>إجمالي المواد</small>
                        </div>
                        <div class="col-4">
                            <h4>{{ $stats['active_modules'] }}</h4>
                            <small>مواد نشطة</small>
                        </div>
                        <div class="col-4">
                            <h4>{{ $stats['total_credits'] }}</h4>
                            <small>الوحدات</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($modules->isEmpty())
        <!-- No Modules Message -->
        <div class="row">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-body text-center py-5">
                        <i style="font-size: 4rem; color: #6c757d;">📚</i>
                        <h4 class="mt-3 text-muted">لا توجد مواد مسجلة</h4>
                        <p class="text-muted">لم يتم العثور على مواد للسنة الجامعية {{ $selectedYear }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Modules by Semester -->
        @foreach($modulesBySemester as $semester => $semesterModules)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">📖 {{ $semester }}</h5>
                            <span class="badge bg-light text-dark">
                                {{ $semesterModules->count() }} مادة | {{ $semesterModules->sum('credits') }} وحدة
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($semesterModules as $module)
                            <div class="col-lg-6 mb-3">
                                <div class="card h-100 border-start border-primary border-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ $module->module_name }}</h6>
                                            <span class="badge {{
                                                $module->status == 'active' ? 'bg-success' :
                                                ($module->status == 'completed' ? 'bg-info' :
                                                ($module->status == 'failed' ? 'bg-danger' : 'bg-warning'))
                                            }}">
                                                {{ $module->status_label }}
                                            </span>
                                        </div>

                                        @if($module->module_name_ar)
                                        <p class="text-muted small mb-2">{{ $module->module_name_ar }}</p>
                                        @endif

                                        <p class="text-muted small mb-2">
                                            <strong>كود المادة:</strong> {{ $module->module_code }}
                                        </p>

                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-primary">{{ $module->credits }}</h6>
                                                    <small class="text-muted">وحدة</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-info">{{ $module->coefficient }}</h6>
                                                    <small class="text-muted">معامل</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-success">{{ $selectedYear }}</h6>
                                                    <small class="text-muted">العام</small>
                                                </div>
                                            </div>
                                        </div>

                                        @if($module->professor)
                                        <div class="mt-2 pt-2 border-top">
                                            <small class="text-muted">
                                                👨‍🏫 <strong>الأستاذ:</strong> {{ $module->professor }}
                                            </small>
                                        </div>
                                        @endif

                                        @if($module->session_type)
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                📅 <strong>الدورة:</strong> {{ $module->session_type == 'printemps' ? 'الربيع' : 'الخريف' }}
                                            </small>
                                        </div>
                                        @endif

                                        @if($module->schedule)
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                🕐 <strong>التوقيت:</strong> {{ $module->schedule }}
                                            </small>
                                        </div>
                                        @endif
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

        <!-- Summary Card -->
        <div class="row">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">📊 ملخص السنة الجامعية {{ $selectedYear }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-primary">{{ $stats['total_modules'] }}</h4>
                                    <p class="mb-0">إجمالي المواد</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-success">{{ $stats['active_modules'] }}</h4>
                                    <p class="mb-0">مواد نشطة</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-info">{{ $stats['completed_modules'] }}</h4>
                                    <p class="mb-0">مواد مكتملة</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-warning">{{ $stats['total_credits'] }}</h4>
                                    <p class="mb-0">إجمالي الوحدات</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
