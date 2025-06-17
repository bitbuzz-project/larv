@extends('layouts.admin')

@section('title', 'Détails du Module')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📚</i> Détails du Module</h2>
            <div class="btn-group">
                <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">
                    <i>⬅️</i> Retour à la liste
                </a>
                <a href="{{ route('admin.modules.edit', $module) }}" class="btn btn-primary">
                    <i>✏️</i> Modifier
                </a>
            </div>
        </div>

        <!-- Module Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i>📋</i> Informations Principales</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Code Module:</strong><br>
                        <span class="badge bg-primary fs-6">{{ $module->cod_elp ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Statut:</strong><br>
                        @if($module->eta_elp == 'A')
                            <span class="badge bg-success">Actif</span>
                        @elseif($module->eta_elp == 'I')
                            <span class="badge bg-danger">Inactif</span>
                        @else
                            <span class="badge bg-secondary">{{ $module->eta_elp ?? 'N/A' }}</span>
                        @endif
                    </div>
                    <div class="col-md-12 mb-3">
                        <strong>Libellé (Français):</strong><br>
                        {{ $module->lib_elp ?? 'Non renseigné' }}
                    </div>
                    @if($module->lib_elp_arb)
                    <div class="col-md-12 mb-3">
                        <strong>Libellé (Arabe):</strong><br>
                        {{ $module->lib_elp_arb }}
                    </div>
                    @endif
                </div>

                <!-- Additional Info -->
                <hr>
                <div class="row">
                    @if($module->cod_cmp)
                    <div class="col-md-4 mb-3">
                        <strong>Composante:</strong><br>
                        <span class="badge bg-secondary">{{ $module->cod_cmp }}</span>
                    </div>
                    @endif
                    @if($module->nbr_pnt_ect_elp)
                    <div class="col-md-4 mb-3">
                        <strong>Points ECTS:</strong><br>
                        <span class="badge bg-info">{{ $module->nbr_pnt_ect_elp }}</span>
                    </div>
                    @endif
                    @if($module->lib_nom_rsp_elp)
                    <div class="col-md-4 mb-3">
                        <strong>Responsable:</strong><br>
                        {{ $module->lib_nom_rsp_elp }}
                    </div>
                    @endif
                </div>

                <!-- Technical Details -->
                @if($module->cod_nel || $module->cod_pel || $module->lic_elp)
                <hr>
                <h6>Informations Techniques</h6>
                <div class="row">
                    @if($module->cod_nel)
                    <div class="col-md-4 mb-3">
                        <strong>Code NEL:</strong><br>
                        {{ $module->cod_nel }}
                    </div>
                    @endif
                    @if($module->cod_pel)
                    <div class="col-md-4 mb-3">
                        <strong>Code PEL:</strong><br>
                        {{ $module->cod_pel }}
                    </div>
                    @endif
                    @if($module->lic_elp)
                    <div class="col-md-4 mb-3">
                        <strong>Licence ELP:</strong><br>
                        {{ $module->lic_elp }}
                    </div>
                    @endif
                </div>
                @endif

                <!-- Dates -->
                @if($module->dat_cre_elp || $module->dat_mod_elp)
                <hr>
                <h6>Dates</h6>
                <div class="row">
                    @if($module->dat_cre_elp)
                    <div class="col-md-6 mb-3">
                        <strong>Date de Création:</strong><br>
                        {{ $module->dat_cre_elp }}
                    </div>
                    @endif
                    @if($module->dat_mod_elp)
                    <div class="col-md-6 mb-3">
                        <strong>Date de Modification:</strong><br>
                        {{ $module->dat_mod_elp }}
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Quick Actions Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i>⚡</i> Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.modules.edit', $module) }}" class="btn btn-outline-primary">
                        <i>✏️</i> Modifier le module
                    </a>
                    <button class="btn btn-outline-info" onclick="window.print()">
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
                    <strong>Créé le:</strong> {{ $module->created_at ? $module->created_at->format('d/m/Y H:i') : 'Non disponible' }}<br>
                    <strong>Modifié le:</strong> {{ $module->updated_at ? $module->updated_at->format('d/m/Y H:i') : 'Non disponible' }}
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
