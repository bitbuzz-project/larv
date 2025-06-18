{{-- Path: resources/views/admin/student-modules/import-results.blade.php --}}
@extends('layouts.admin')

@section('title', 'Résultats d\'importation des modules étudiants')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Résultats d'importation des modules étudiants</h1>

    @if(session('import_stats'))
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">Statistiques d'importation</h6>
            </div>
            <div class="card-body">
                <p><strong>Total de lignes traitées:</strong> {{ session('import_stats.total') }}</p>
                <p><strong>Modules importés avec succès:</strong> <span class="text-success">{{ session('import_stats.imported') }}</span></p>
                <p><strong>Modules ignorés (déjà existants):</strong> <span class="text-warning">{{ session('import_stats.skipped') }}</span></p>
                <p><strong>Modules avec erreurs:</strong> <span class="text-danger">{{ session('import_stats.errors') }}</span></p>
                <p><strong>Taux de succès:</strong> {{ round(session('import_stats.success_rate'), 2) }}%</p>
            </div>
        </div>

        @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Détails des erreurs</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach(session('import_errors') as $error)
                                <li>
                                    <strong>Ligne {{ $error['line'] ?? 'N/A' }}</strong> (Code: {{ $error['code'] ?? 'N/A' }}):
                                    {{ $error['message'] ?? 'Erreur inconnue' }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="alert alert-info">
            Aucun résultat d'importation récent n'a été trouvé. Veuillez importer un fichier JSON.
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('admin.student-modules.import') }}" class="btn btn-primary">Retourner à l'importation des modules étudiants</a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Retour au Tableau de bord</a>
    </div>
</div>
@endsection
