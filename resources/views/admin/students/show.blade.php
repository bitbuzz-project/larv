@extends('layouts.admin')

@section('title', 'Détails de l\'Étudiant')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>👤</i> Détails de l'Étudiant</h2>
            <div class="btn-group">
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                    <i>⬅️</i> Retour à la liste
                </a>
                <a href="{{ route('admin.students.edit', $student->apoL_a01_code) }}" class="btn btn-primary">
                    <i>✏️</i> Modifier
                </a>
            </div>
        </div>

        <!-- Student Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i>📋</i> Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ $student->initials }}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Code Apogée:</strong><br>
                                <span class="badge bg-primary fs-6">{{ $student->apoL_a01_code }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Nom Complet:</strong><br>
                                {{ $student->full_name }}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Date de Naissance:</strong><br>
                                {{ $student->apoL_a04_naissance ?? 'Non renseigné' }}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Code Étudiant:</strong><br>
                                {{ $student->cod_etu ?? 'Non renseigné' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                @if($student->cin_ind || $student->lib_vil_nai_etu || $student->cod_sex_etu)
                <hr>
                <div class="row">
                    @if($student->cin_ind)
                    <div class="col-md-4 mb-3">
                        <strong>CIN:</strong><br>
                        {{ $student->cin_ind }}
                    </div>
                    @endif
                    @if($student->cod_sex_etu)
                    <div class="col-md-4 mb-3">
                        <strong>Sexe:</strong><br>
                        {{ $student->cod_sex_etu == 'M' ? 'Masculin' : ($student->cod_sex_etu == 'F' ? 'Féminin' : $student->cod_sex_etu) }}
                    </div>
                    @endif
                    @if($student->lib_vil_nai_etu)
                    <div class="col-md-4 mb-3">
                        <strong>Ville de Naissance:</strong><br>
                        {{ $student->lib_vil_nai_etu }}
                    </div>
                    @endif
                </div>
                @endif

                <!-- Technical Info -->
                @if($student->cod_etp || $student->lib_etp)
                <hr>
                <div class="row">
                    @if($student->cod_etp)
                    <div class="col-md-6 mb-3">
                        <strong>Code ETP:</strong><br>
                        {{ $student->cod_etp }}
                    </div>
                    @endif
                    @if($student->lib_etp)
                    <div class="col-md-6 mb-3">
                        <strong>Libellé ETP:</strong><br>
                        {{ $student->lib_etp }}
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Statistics Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i>📊</i> Statistiques</h5>
            </div>
            <div class="card-body text-center">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-info">{{ $notes_count }}</h4>
                            <small class="text-muted">Notes</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-warning">{{ $reclamations_count }}</h4>
                            <small class="text-muted">Réclamations</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i>⚡</i> Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.students.edit', $student->apoL_a01_code) }}" class="btn btn-outline-primary">
                        <i>✏️</i> Modifier les informations
                    </a>
                    <button class="btn btn-outline-info" onclick="alert('Fonctionnalité à venir')">
                        <i>📊</i> Voir les notes
                    </button>
                    <button class="btn btn-outline-warning" onclick="alert('Fonctionnalité à venir')">
                        <i>⚠️</i> Voir les réclamations
                    </button>
                    <button class="btn btn-outline-success" onclick="window.print()">
                        <i>🖨️</i> Imprimer
                    </button>
                </div>
            </div>
        </div>

        <!-- System Info Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i>ℹ️</i> Informations Système</h5>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <strong>Créé le:</strong> {{ $student->created_at ? $student->created_at->format('d/m/Y H:i') : 'Non disponible' }}<br>
                    <strong>Modifié le:</strong> {{ $student->updated_at ? $student->updated_at->format('d/m/Y H:i') : 'Non disponible' }}
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
