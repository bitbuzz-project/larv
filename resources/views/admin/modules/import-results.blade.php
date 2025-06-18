@extends('layouts.admin')

@section('title', 'Résultats d\'Importation des Modules')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📊</i> Résultats d'Importation des Modules</h2>
            <a href="{{ route('admin.modules.import') }}" class="btn btn-primary">
                <i>⬆️</i> Nouvelle Importation
            </a>
        </div>

        @if(session('import_stats'))
            @php
                $stats = session('import_stats');
                $errors = session('import_errors', []);
            @endphp

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
                                <p class="mb-0">Importé avec succès</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning py-3">
                                <h4 class="mb-1">{{ $stats['skipped'] }}</h4>
                                <p class="mb-0">Ignorés (existants)</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-danger py-3">
                                <h4 class="mb-1">{{ $stats['errors'] }}</h4>
                                <p class="mb-0">Erreurs</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <p class="h5">Taux de succès: <span class="badge {{ $stats['success_rate'] == 100 ? 'bg-success' : ($stats['success_rate'] > 50 ? 'bg-warning text-dark' : 'bg-danger') }}">{{ number_format($stats['success_rate'], 2) }}%</span></p>
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
                                        <th>Code Module</th>
                                        <th>Type d'Erreur</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($errors as $error)
                                        <tr>
                                            <td>{{ $error['line'] ?? 'N/A' }}</td>
                                            <td>{{ $error['code'] ?? 'N/A' }}</td>
                                            <td>{{ $error['type'] ?? 'Inconnu' }}</td>
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
                    <i>🎉</i> Aucune erreur détaillée à afficher. Tous les modules valides ont été traités.
                </div>
            @endif

        @else
            <div class="alert alert-warning text-center">
                <i>⚠️</i> Aucun résultat d'importation n'a été trouvé. Veuillez lancer une importation d'abord.
                <br><br>
                <a href="{{ route('admin.modules.import') }}" class="btn btn-warning">
                    <i>⬆️</i> Aller à la page d'importation
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
