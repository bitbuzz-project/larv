@extends('layouts.admin')

@section('title', 'Analyse Détaillée')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i>🔍</i> Analyse Détaillée</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Rapports</a></li>
                <li class="breadcrumb-item active">Analyse Détaillée</li>
            </ol>
        </nav>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i>⬅️</i> Retour aux Rapports
        </a>
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i>🖨️</i> Imprimer
        </button>
    </div>
</div>

<!-- Coming Soon Message -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i style="font-size: 4rem; color: #6c757d;">🚧</i>
                <h3 class="mt-3 mb-3">Analyses Détaillées en Cours de Développement</h3>
                <p class="text-muted mb-4">
                    Les analyses détaillées par section sont actuellement en développement.
                    Elles seront bientôt disponibles pour fournir des insights approfondis
                    sur chaque aspect de la plateforme.
                </p>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <strong>Fonctionnalités à venir :</strong>
                            <ul class="list-unstyled mt-2 mb-0">
                                <li>📊 Analyses détaillées des étudiants par filière et année</li>
                                <li>📚 Statistiques avancées des modules et inscriptions</li>
                                <li>📈 Évolution des notes et performances académiques</li>
                                <li>⚠️ Suivi détaillé des réclamations et temps de réponse</li>
                                <li>📋 Rapports personnalisables avec filtres avancés</li>
                                <li>📄 Export automatique en PDF et Excel</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-primary">
                    <i>📊</i> Retour au Rapport Principal
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
