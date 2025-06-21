@extends('layouts.admin')

@section('title', 'Résultats d\'Importation des Notes')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📊</i> Résultats d'Importation des Notes</h2>
            <a href="{{ route('admin.notes.import') }}" class="btn btn-primary">
                <i>⬆️</i> Nouvelle Importation
            </a>
        </div>

        @if(session('import_stats'))
            @php
                $stats = session('import_stats');
                $errors = session('import_errors', []);
            @endphp

            <!-- Import Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i>📈</i> Résumé de l'Importation</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="alert alert-info py-3">
                                <h4 class="mb-1">{{ $stats['total'] }}</h4>
                                <p class="mb-0">Total lu</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success py-3">
                                <h4 class="mb-1">{{ $stats['imported'] }}</h4>
                                <p class="mb-0">Notes importées</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning py-3">
                                <h4 class="mb-1">{{ $stats['skipped'] }}</h4>
                                <p class="mb-0">Ignorées</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-danger py-3">
                                <h4 class="mb-1">{{ $stats['errors'] }}</h4>
                                <p class="mb-0">Erreurs</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6>Détails de l'importation:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Type:</strong> {{ $stats['import_type'] === 'current_session' ? 'Session actuelle' : 'Sessions anciennes' }}</li>
                            @if($stats['import_type'] === 'current_session')
                                <li><strong>Session:</strong> {{ ucfirst($stats['session_type']) }}</li>
                                <li><strong>Résultat:</strong> {{ ucfirst($stats['result_type']) }}</li>
                            @endif
                            <li><strong>Année scolaire:</strong> {{ $stats['annee_scolaire'] }}</li>
                            <li><strong>Taux de succès:</strong>
                                <span class="badge {{ $stats['success_rate'] == 100 ? 'bg-success' : ($stats['success_rate'] > 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ number_format($stats['success_rate'], 2) }}%
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            @if(count($errors) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i>❌</i> Détails des Erreurs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ligne</th>
                                        <th>Code Apogée</th>
                                        <th>Type d'Erreur</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($errors as $error)
                                        <tr>
                                            <td>{{ $error['line'] ?? 'N/A' }}</td>
                                            <td>{{ $error['code'] ?? 'N/A' }}</td>
                                            <td>
                                                @if($error['type'] === 'validation')
                                                    <span class="badge bg-warning">Validation</span>
                                                @elseif($error['type'] === 'foreign_key')
                                                    <span class="badge bg-info">Référence</span>
                                                @elseif($error['type'] === 'database')
                                                    <span class="badge bg-danger">Base de données</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $error['type'] }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $error['message'] ?? 'Erreur inconnue' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i>🎉</i> Aucune erreur détaillée à afficher. Toutes les notes valides ont été traitées.
                </div>
            @endif

        @else
            <div class="alert alert-warning text-center">
                <i>⚠️</i> Aucun résultat d'importation n'a été trouvé. Veuillez lancer une importation d'abord.
                <br><br>
                <a href="{{ route('admin.notes.import') }}" class="btn btn-warning">
                    <i>⬆️</i> Aller à la page d'importation
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
