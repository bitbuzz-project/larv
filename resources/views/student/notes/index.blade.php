@extends('layouts.student')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4 text-primary">{{ __("نتائجي الأكاديمية") }}</h3>

    <form action="{{ route('student.notes.index') }}" method="GET" class="mb-4 p-3 bg-white rounded shadow-sm d-flex flex-wrap align-items-end gap-3">
        <div class="flex-grow-1">
            <label for="annee_scolaire" class="form-label">السنة الدراسية:</label>
            <select name="annee_scolaire" id="annee_scolaire" class="form-select">
                @foreach ($availableYears as $year)
                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1">
            <label for="session_type" class="form-label">نوع الدورة:</label>
            <select name="session_type" id="session_type" class="form-select">
                <option value="all" {{ $selectedSession == 'all' ? 'selected' : '' }}>الكل</option>
                <option value="printemps" {{ $selectedSession == 'printemps' ? 'selected' : '' }}>ربيعية</option>
                <option value="automne" {{ $selectedSession == 'automne' ? 'selected' : '' }}>خريفية</option>
            </select>
        </div>
        <div class="flex-grow-1">
            <label for="result_type" class="form-label">نوع النتيجة:</label>
            <select name="result_type" id="result_type" class="form-select">
                <option value="all" {{ $selectedResultType == 'all' ? 'selected' : '' }}>الكل</option>
                <option value="normale" {{ $selectedResultType == 'normale' ? 'selected' : '' }}>عادية</option>
                <option value="rattrapage" {{ $selectedResultType == 'rattrapage' ? 'selected' : '' }}>استدراكية</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
        </div>
    </form>

    @if($notesBySemester->isEmpty())
        <div class="alert alert-info text-center" role="alert">
            لا توجد نقاط متاحة للمعايير المحددة.
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-sm-6">
                <div class="card stat-card bg-info text-white text-center">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المواد</h5>
                        <p class="card-text fs-4">{{ $stats['total_modules'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card stat-card bg-success text-white text-center">
                    <div class="card-body">
                        <h5 class="card-title">المواد المكتسبة</h5>
                        <p class="card-text fs-4">{{ $stats['passed_modules'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card stat-card bg-danger text-white text-center">
                    <div class="card-body">
                        <h5 class="card-title">المواد غير المكتسبة</h5>
                        <p class="card-text fs-4">{{ $stats['failed_modules'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card stat-card bg-secondary text-white text-center">
                    <div class="card-body">
                        <h5 class="card-title">المعدل العام</h5>
                        <p class="card-text fs-4">{{ number_format($stats['average_grade'], 2) ?? 'غ/م' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card stat-card bg-warning text-white text-center">
                    <div class="card-body">
                        <h5 class="card-title">أعلى نقطة</h5>
                        <p class="card-text fs-4">{{ $stats['highest_grade'] ?? 'غ/م' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card stat-card bg-dark text-white text-center">
                    <div class="card-body">
                        <h5 class="card-title">أدنى نقطة</h5>
                        <p class="card-text fs-4">{{ $stats['lowest_grade'] ?? 'غ/م' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($notesBySemester as $semesterGroup)
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white fs-5 fw-bold">
                    {{ $semesterGroup['semester_name'] }}
                </div>
                <div class="card-body p-0">
                    @if ($semesterGroup['sessions']->isEmpty())
                        <div class="alert alert-secondary m-3 text-center" role="alert">
                            لا توجد دورات متاحة لهذا الفصل.
                        </div>
                    @else
                        @foreach ($semesterGroup['sessions'] as $sessionGroup)
                            <div class="p-3 border-bottom">
                                <h4 class="mb-3 text-secondary">
                                    {{ $sessionGroup['session_type_display'] }}
                                    @if($sessionGroup['is_current'] && $sessionGroup['result_type'])
                                        ({{ ucfirst($sessionGroup['result_type']) }})
                                    @endif
                                </h4>

                                @if ($sessionGroup['notes']->isEmpty())
                                    <div class="alert alert-light m-2 text-center" role="alert">
                                        لا توجد نقاط لهذه الدورة.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">رمز المادة</th>
                                                    <th scope="col">اسم المادة</th>
                                                    <th scope="col">النقطة</th>
                                                    <th scope="col">السنة الدراسية</th>
                                                    <th scope="col">معلومات الدورة</th>
                                                    <th scope="col">الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($sessionGroup['notes'] as $note)
                                                    <tr>
                                                        <td>{{ $note->code_module }}</td>
                                                        <td>{{ $note->display_name }}</td>
                                                        <td class="{{ ($note->note !== null && $note->note >= 10) ? 'text-success fw-bold' : (($note->note !== null && $note->note < 10) ? 'text-danger fw-bold' : 'text-muted') }}">
                                                            @if ($note->note == -1)
                                                                غ/م (ABS)
                                                            @elseif ($note->note !== null)
                                                                {{ number_format($note->note, 2) }}
                                                            @else
                                                                غ/م
                                                            @endif
                                                        </td>
                                                        <td>{{ $note->annee_scolaire }}</td>
                                                        <td>
                                                            @if (isset($note->COD_TRE))
                                                                {{ $note->COD_TRE }}
                                                            @else
                                                                غ/م
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('student.notes.show', ['noteId' => $note->id, 'table' => $note->is_current ? 'notes_actu' : 'notes']) }}" class="btn btn-sm btn-outline-primary">تفاصيل</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
