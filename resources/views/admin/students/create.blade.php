@extends('layouts.admin')

@section('title', 'Ajouter un Étudiant')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>➕</i> Ajouter un Étudiant</h2>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                <i>⬅️</i> Retour à la liste
            </a>
        </div>

        <!-- Create Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i>📝</i> Informations de l'Étudiant</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.students.store') }}">
                    @csrf

                    <!-- Code Apogée -->
                    <div class="mb-3">
                        <label for="apoL_a01_code" class="form-label">Code Apogée <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a01_code') is-invalid @enderror"
                               id="apoL_a01_code" name="apoL_a01_code" value="{{ old('apoL_a01_code') }}" required>
                        @error('apoL_a01_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Code unique de l'étudiant (ex: 12345678)</small>
                    </div>

                    <!-- Nom -->
                    <div class="mb-3">
                        <label for="apoL_a02_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a02_nom') is-invalid @enderror"
                               id="apoL_a02_nom" name="apoL_a02_nom" value="{{ old('apoL_a02_nom') }}" required>
                        @error('apoL_a02_nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Prénom -->
                    <div class="mb-3">
                        <label for="apoL_a03_prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a03_prenom') is-invalid @enderror"
                               id="apoL_a03_prenom" name="apoL_a03_prenom" value="{{ old('apoL_a03_prenom') }}" required>
                        @error('apoL_a03_prenom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date de Naissance -->
                    <div class="mb-3">
                        <label for="apoL_a04_naissance" class="form-label">Date de Naissance <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a04_naissance') is-invalid @enderror"
                               id="apoL_a04_naissance" name="apoL_a04_naissance" value="{{ old('apoL_a04_naissance') }}"
                               placeholder="DD/MM/YYYY" required>
                        @error('apoL_a04_naissance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Format: DD/MM/YYYY (ex: 15/03/2000)</small>
                    </div>

                    <!-- Optional Fields Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cod_etu" class="form-label">Code Étudiant</label>
                                <input type="text" class="form-control" id="cod_etu" name="cod_etu" value="{{ old('cod_etu') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cin_ind" class="form-label">CIN</label>
                                <input type="text" class="form-control" id="cin_ind" name="cin_ind" value="{{ old('cin_ind') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cod_sex_etu" class="form-label">Sexe</label>
                                <select class="form-control" id="cod_sex_etu" name="cod_sex_etu">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="M" {{ old('cod_sex_etu') == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('cod_sex_etu') == 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lib_vil_nai_etu" class="form-label">Ville de Naissance</label>
                                <input type="text" class="form-control" id="lib_vil_nai_etu" name="lib_vil_nai_etu" value="{{ old('lib_vil_nai_etu') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
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

<script>
// Auto-format date input
document.getElementById('apoL_a04_naissance').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0,2) + '/' + value.substring(2);
    }
    if (value.length >= 5) {
        value = value.substring(0,5) + '/' + value.substring(5,9);
    }
    e.target.value = value;
});
</script>
@endsection
