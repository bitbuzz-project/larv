@extends('layouts.admin')

@section('title', 'Tableau de bord Admin')

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger border-0 shadow" role="alert">
            <h4 class="alert-heading">
                <i>👋</i> Bienvenue, {{ auth()->user()->apoL_a03_prenom }}!
            </h4>
            <p class="mb-0">Vous êtes connecté en tant qu'administrateur. Gérez votre plateforme étudiante depuis ce panneau de contrôle.</p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #17a2b8, #138496);">
            <div class="card-body text-white text-center">
                <i style="font-size: 2.5rem;">👥</i>
                <h2 class="mt-2 mb-1">{{ $stats['total_students'] }}</h2>
                <p class="card-text">Étudiants Total</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="card-body text-white text-center">
                <i style="font-size: 2.5rem;">🎓</i>
                <h2 class="mt-2 mb-1">0</h2>
                <p class="card-text">Filières</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
            <div class="card-body text-white text-center">
                <i style="font-size: 2.5rem;">📊</i>
                <h2 class="mt-2 mb-1">{{ $stats['total_reclamations'] }}</h2>
                <p class="card-text">Réclamations</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #6f42c1, #5a67d8);">
            <div class="card-body text-white text-center">
                <i style="font-size: 2.5rem;">📈</i>
                <h2 class="mt-2 mb-1">2024-25</h2>
                <p class="card-text">Session Actuelle</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i>⚡</i> Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.students.create') }}" class="btn btn-outline-primary btn-block w-100">
                            <i>➕</i> Ajouter Étudiant
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-warning btn-block w-100">
                            <i>👥</i> Voir Étudiants
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="#" class="btn btn-outline-success btn-block w-100">
                            <i>📋</i> Gérer Résultats
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="#" class="btn btn-outline-info btn-block w-100">
                            <i>📈</i> Voir Rapports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Students and System Info -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i>👥</i> Étudiants Récents</h5>
            </div>
            <div class="card-body">
                @if($recent_students->isEmpty())
                    <p class="text-muted">Aucun étudiant trouvé.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recent_students as $student)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        {{ $student->initials }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ $student->full_name }}</h6>
                                        <small class="text-muted">Code: {{ $student->apoL_a01_code }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-primary btn-sm">Voir tous les étudiants</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i>📊</i> Informations Système</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-success">{{ $stats['pending_reclamations'] }}</h4>
                            <small class="text-muted">Réclamations en attente</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-info">{{ $stats['resolved_reclamations'] }}</h4>
                            <small class="text-muted">Réclamations résolues</small>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <h6>Actions Système</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-secondary btn-sm">
                            <i>🔄</i> Actualiser les données
                        </button>
                        <button class="btn btn-outline-warning btn-sm">
                            <i>📥</i> Exporter les données
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
