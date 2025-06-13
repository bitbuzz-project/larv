@extends('layouts.admin')

@section('title', 'Résultats de l\'Import')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📊</i> Résultats de l'Import</h2>
            <div class="btn-group">
                <a href="{{ route('admin.students.import') }}" class="btn btn-warning">
                    <i>📥</i> Nouvel Import
                </a>
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                    <i>👥</i> Voir Étudiants
                </a>
            </div>
        </div>

        <!-- Import Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <div class="card-body text-white">
                        <h3>{{ session('import_stats.imported', 0) }}</h3>
                        <p class="mb-0">Importés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                    <div class="card-body text-white">
                        <h3>{{ session('import_stats.skipped', 0) }}</h3>
                        <p class="mb-0">Ignorés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                    <div class="card-body text-white">
                        <h3>{{ session('import_stats.errors', 0) }}</h3>
                        <p class="mb-0">Erreurs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <div class="card-body text-white">
                        <h3>{{ session('import_stats.total', 0) }}</h3>
                        <p class="mb-0">Total Traités</p>
                    </div>
                </div>
            </div>
        </div>

        @if(session('import_stats.success_rate'))
        <!-- Success Rate -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i>📈</i> Taux de Réussite</h5>
            </div>
            <div class="card-body">
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: {{ session('import_stats.success_rate') }}%">
                        {{ number_format(session('import_stats.success_rate'), 1) }}%
                    </div>
                </div>
                <p class="text-muted">
                    {{ session('import_stats.imported') }} étudiants importés avec succès sur {{ session('import_stats.total') }} traités.
                </p>
            </div>
        </div>
        @endif

        @if(session('import_errors') && count(session('import_errors')) > 0)
        <!-- Error Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i>⚠️</i> Détails des Erreurs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ligne</th>
                                <th>Code Apogée</th>
                                <th>Erreur</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('import_errors') as $error)
                            <tr>
                                <td>{{ $error['line'] ?? 'N/A' }}</td>
                                <td>
                                    @if(isset($error['code']))
                                        <span class="badge bg-secondary">{{ $error['code'] }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $error['message'] }}</td>
                                <td>
                                    @if($error['type'] === 'duplicate')
                                        <span class="badge bg-warning">Doublon</span>
                                    @elseif($error['type'] === 'validation')
                                        <span class="badge bg-danger">Validation</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $error['type'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if(session('import_warnings') && count(session('import_warnings')) > 0)
        <!-- Warnings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i>⚠️</i> Avertissements</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach(session('import_warnings') as $warning)
                    <li class="list-group-item">
                        <i class="text-warning">⚠️</i> {{ $warning }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Import Log -->
        @if(session('import_log'))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i>📋</i> Journal d'Import</h5>
            </div>
            <div class="card-body">
                <div class="bg-dark text-light p-3 rounded" style="font-family: monospace; max-height: 300px; overflow-y: auto;">
                    @foreach(session('import_log') as $logEntry)
                    <div>{{ $logEntry['timestamp'] }} - {{ $logEntry['message'] }}</div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Next Steps -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i>🎯</i> Prochaines Étapes</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Actions Recommandées :</h6>
                        <ul>
                            @if(session('import_stats.imported', 0) > 0)
                            <li>✅ Vérifier les nouveaux étudiants dans la liste</li>
                            @endif
                            @if(session('import_stats.errors', 0) > 0)
                            <li>🔧 Corriger les erreurs et relancer l'import</li>
                            @endif
                            @if(session('import_stats.skipped', 0) > 0)
                            <li>📝 Vérifier les doublons ignorés</li>
                            @endif
                            <li>📊 Examiner les statistiques globales</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Actions Rapides :</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-primary">
                                <i>👥</i> Voir Tous les Étudiants
                            </a>
                            @if(session('import_stats.errors', 0) > 0)
                            <a href="{{ route('admin.students.import') }}" class="btn btn-outline-warning">
                                <i>🔄</i> Réessayer l'Import
                            </a>
                            @endif
                            <button class="btn btn-outline-success" onclick="downloadReport()">
                                <i>📄</i> Télécharger Rapport
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function downloadReport() {
    const report = {
        import_date: new Date().toISOString(),
        summary: {
            imported: {{ session('import_stats.imported', 0) }},
            skipped: {{ session('import_stats.skipped', 0) }},
            errors: {{ session('import_stats.errors', 0) }},
            total: {{ session('import_stats.total', 0) }},
            success_rate: {{ session('import_stats.success_rate', 0) }}
        },
        errors: @json(session('import_errors', [])),
        warnings: @json(session('import_warnings', [])),
        log: @json(session('import_log', []))
    };

    const dataStr = JSON.stringify(report, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `import_report_${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
