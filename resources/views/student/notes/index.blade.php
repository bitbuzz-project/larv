@extends('layouts.student')

@section('title', 'النتائج والنقط')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 النتائج والنقط</h2>
        <div class="text-muted">
            <small>{{ $student->full_name }} - {{ $student->apoL_a01_code }}</small>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card card text-center" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                <div class="card-body text-white">
                    <h4>{{ $stats['total_modules'] }}</h4>
                    <small>إجمالي المواد</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card text-center" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <div class="card-body text-white">
                    <h4>{{ $stats['passed_modules'] }}</h4>
                    <small>مواد ناجحة</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card text-center" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                <div class="card-body text-white">
                    <h4>{{ $stats['failed_modules'] }}</h4>
                    <small>مواد راسبة</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card text-center" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                <div class="card-body text-white">
                    <h4>{{ $stats['average_grade'] ? number_format($stats['average_grade'], 2) : '--' }}</h4>
                    <small>المعدل العام</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card text-center" style="background: linear-gradient(135deg, #6f42c1, #5a67d8);">
                <div class="card-body text-white">
                    <h4>{{ $stats['highest_grade'] ? number_format($stats['highest_grade'], 2) : '--' }}</h4>
                    <small>أعلى نقطة</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card text-center" style="background: linear-gradient(135deg, #fd7e14, #e55a4e);">
                <div class="card-body text-white">
                    <h4>{{ $stats['lowest_grade'] ? number_format($stats['lowest_grade'], 2) : '--' }}</h4>
                    <small>أقل نقطة</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🔍 تصفية النتائج</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">السنة الدراسية:</label>
                    <select name="annee_scolaire" class="form-select">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">نوع الدورة:</label>
                    <select name="session_type" class="form-select">
                        <option value="all" {{ $selectedSession == 'all' ? 'selected' : '' }}>كل الدورات</option>
                        <option value="printemps" {{ $selectedSession == 'printemps' ? 'selected' : '' }}>الربيع</option>
                        <option value="automne" {{ $selectedSession == 'automne' ? 'selected' : '' }}>الخريف</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">نوع النتيجة:</label>
                    <select name="result_type" class="form-select">
                        <option value="all" {{ $selectedResultType == 'all' ? 'selected' : '' }}>كل النتائج</option>
                        <option value="normale" {{ $selectedResultType == 'normale' ? 'selected' : '' }}>عادية</option>
                        <option value="rattrapage" {{ $selectedResultType == 'rattrapage' ? 'selected' : '' }}>استدراك</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">تطبيق التصفية</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notes by Session -->
    @if(empty($notesBySession))
        <div class="alert alert-info text-center">
            <h5>📝 لا توجد نتائج</h5>
            <p>لم يتم العثور على أي نتائج للمعايير المحددة.</p>
        </div>
    @else
        @foreach($notesBySession as $sessionKey => $sessionData)
            <div class="card mb-4">
                <div class="card-header {{ $sessionData['is_current'] ? 'bg-success' : 'bg-secondary' }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            @if($sessionData['is_current'])
                                🌟 {{ ucfirst($sessionData['session_type']) }} - {{ ucfirst($sessionData['result_type']) }}
                            @else
                                📚 {{ $sessionData['session_type'] }}
                            @endif
                        </h5>
                        <span class="badge bg-light text-dark">
                            {{ $sessionData['notes']->count() }} مادة
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($sessionData['notes']->isEmpty())
                        <div class="p-4 text-center text-muted">
                            لا توجد نتائج لهذه الدورة
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%">كود المادة</th>
                                        <th style="width: 45%">اسم المادة</th>
                                        <th style="width: 15%" class="text-center">النقطة</th>
                                        <th style="width: 15%" class="text-center">الحالة</th>
                                        <th style="width: 10%" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sessionData['notes'] as $note)
                                        <tr>
                                            <td>
                                                <code class="text-primary">{{ $note->code_module }}</code>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $note->display_name }}</strong>
                                                    @if($note->display_name !== $note->nom_module)
                                                        <br><small class="text-muted">{{ $note->nom_module }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($note->note !== null)
                                                    <span class="badge fs-6 {{ $note->note >= 10 ? 'bg-success' : 'bg-danger' }}">
                                                        {{ number_format($note->note, 2) }}/20
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">--</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($note->note !== null)
                                                    @if($note->note >= 10)
                                                        <span class="badge bg-success">ناجح ✓</span>
                                                    @else
                                                        <span class="badge bg-danger">راسب ✗</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning">في الانتظار</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($note->note !== null)
                                                    <button class="btn btn-sm btn-outline-info"
                                                            onclick="showNoteDetails({{ json_encode($note) }})"
                                                            title="تفاصيل النقطة">
                                                        👁️
                                                    </button>
                                                @else
                                                    <small class="text-muted">--</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Session Summary -->
                        <div class="card-footer bg-light">
                            @php
                                $sessionNotes = $sessionData['notes']->whereNotNull('note');
                                $sessionPassed = $sessionNotes->where('note', '>=', 10)->count();
                                $sessionFailed = $sessionNotes->where('note', '<', 10)->count();
                                $sessionAverage = $sessionNotes->avg('note');
                            @endphp
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <small class="text-muted">المواد الناجحة:</small>
                                    <div class="fw-bold text-success">{{ $sessionPassed }}</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">المواد الراسبة:</small>
                                    <div class="fw-bold text-danger">{{ $sessionFailed }}</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">معدل الدورة:</small>
                                    <div class="fw-bold">{{ $sessionAverage ? number_format($sessionAverage, 2) : '--' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">نسبة النجاح:</small>
                                    <div class="fw-bold">
                                        @if($sessionNotes->count() > 0)
                                            {{ number_format(($sessionPassed / $sessionNotes->count()) * 100, 1) }}%
                                        @else
                                            --
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Note Details Modal -->
<div class="modal fade" id="noteDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">تفاصيل النقطة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <strong>كود المادة:</strong>
                        <div id="modal-code-module" class="text-muted"></div>
                    </div>
                    <div class="col-6">
                        <strong>النقطة:</strong>
                        <div id="modal-note" class="text-muted"></div>
                    </div>
                </div>
                <div class="mt-3">
                    <strong>اسم المادة:</strong>
                    <div id="modal-nom-module" class="text-muted"></div>
                    <div id="modal-nom-module-original" class="text-muted small" style="display: none;"></div>
                </div>
                <div class="mt-3" id="modal-session-info" style="display: none;">
                    <div class="row">
                        <div class="col-6">
                            <strong>نوع الدورة:</strong>
                            <div id="modal-session-type" class="text-muted"></div>
                        </div>
                        <div class="col-6">
                            <strong>نوع النتيجة:</strong>
                            <div id="modal-result-type" class="text-muted"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <strong>السنة الدراسية:</strong>
                    <div class="text-muted">{{ $selectedYear }}</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<script>
function showNoteDetails(note) {
    document.getElementById('modal-code-module').textContent = note.code_module;
    document.getElementById('modal-nom-module').textContent = note.display_name;
    document.getElementById('modal-note').textContent = note.note ? note.note + '/20' : '--';

    // Show original name if different from display name
    const originalNameElement = document.getElementById('modal-nom-module-original');
    if (note.display_name !== note.nom_module) {
        originalNameElement.textContent = 'اسم أصلي: ' + note.nom_module;
        originalNameElement.style.display = 'block';
    } else {
        originalNameElement.style.display = 'none';
    }

    if (note.session_type) {
        document.getElementById('modal-session-type').textContent = note.session_type;
        document.getElementById('modal-result-type').textContent = note.result_type;
        document.getElementById('modal-session-info').style.display = 'block';
    } else {
        document.getElementById('modal-session-info').style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('noteDetailsModal')).show();
}
</script>
@endsection
