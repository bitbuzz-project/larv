

@extends('layouts.admin')

@section('title', 'Ajouter un Module')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>➕</i> Ajouter un Module</h2>
            <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">
                <i>⬅️</i> Retour à la liste
            </a>
        </div>

        <!-- Create Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i>📝</i> Informations du Module</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.modules.store') }}">
                    @csrf

                    <!-- Code Module -->
                    <div class="mb-3">
                        <label for="cod_elp" class="form-label">Code Module <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('cod_elp') is-invalid @enderror"
                               id="cod_elp" name="cod_elp" value="{{ old('cod_elp') }}" required>
                        @error('cod_elp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Code unique du module (ex: INFO101)</small>
                    </div>

                    <!-- Libellé Français -->
                    <div class="mb-3">
                        <label for="lib_elp" class="form-label">Libellé (Français) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('lib_elp') is-invalid @enderror"
                               id="lib_elp" name="lib_elp" value="{{ old('lib_elp') }}" required>
                        @error('lib_elp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Libellé Arabe -->
                    <div class="mb-3">
                        <label for="lib_elp_arb" class="form-label">Libellé (Arabe)</label>
                        <input type="text" class="form-control @error('lib_elp_arb') is-invalid @enderror"
                               id="lib_elp_arb" name="lib_elp_arb" value="{{ old('lib_elp_arb') }}">
                        @error('lib_elp_arb')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Optional Fields Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cod_cmp" class="form-label">Code Composante</label>
                                <input type="text" class="form-control" id="cod_cmp" name="cod_cmp" value="{{ old('cod_cmp') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nbr_pnt_ect_elp" class="form-label">Points ECTS</label>
                                <input type="number" class="form-control" id="nbr_pnt_ect_elp" name="nbr_pnt_ect_elp"
                                       value="{{ old('nbr_pnt_ect_elp') }}" min="0" step="0.1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="eta_elp" class="form-label">Statut</label>
                                <select class="form-control" id="eta_elp" name="eta_elp">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="A" {{ old('eta_elp') == 'A' ? 'selected' : '' }}>Actif</option>
                                    <option value="I" {{ old('eta_elp') == 'I' ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lib_nom_rsp_elp" class="form-label">Responsable</label>
                                <input type="text" class="form-control" id="lib_nom_rsp_elp" name="lib_nom_rsp_elp"
                                       value="{{ old('lib_nom_rsp_elp') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">
                            <i>❌</i> Annuler
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i>💾</i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
