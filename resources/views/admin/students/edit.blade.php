@extends('layouts.admin')

@section('title', 'Modifier l\'Étudiant')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>✏️</i> Modifier l'Étudiant</h2>
            <div class="btn-group">
                <a href="{{ route('admin.students.show', $student->apoL_a01_code) }}" class="btn btn-secondary">
                    <i>👁️</i> Voir détails
                </a>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                    <i>⬅️</i> Retour à la liste
                </a>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i>📝</i> Modifier les Informations</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.students.update', $student->apoL_a01_code) }}">
                    @csrf
                    @method('PUT')

                    <!-- Code Apogée (Read-only) -->
                    <div class="mb-3">
                        <label for="apoL_a01_code" class="form-label">Code Apogée</label>
                        <input type="text" class="form-control bg-light" id="apoL_a01_code"
                               value="{{ $student->apoL_a01_code }}" readonly>
                        <small class="form-text text-muted">Le code Apogée ne peut pas être modifié</small>
                    </div>

                    <!-- Nom -->
                    <div class="mb-3">
                        <label for="apoL_a02_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a02_nom') is-invalid @enderror"
                               id="apoL_a02_nom" name="apoL_a02_nom"
                               value="{{ old('apoL_a02_nom', $student->apoL_a02_nom) }}" required>
                        @error('apoL_a02_nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Prénom -->
                    <div class="mb-3">
                        <label for="apoL_a03_prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a03_prenom') is-invalid @enderror"
                               id="apoL_a03_prenom" name="apoL_a03_prenom"
                               value="{{ old('apoL_a03_prenom', $student->apoL_a03_prenom) }}" required>
                        @error('apoL_a03_prenom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date de Naissance -->
                    <div class="mb-3">
                        <label for="apoL_a04_naissance" class="form-label">Date de Naissance <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('apoL_a04_naissance') is-invalid @enderror"
                               id="apoL_a04_naissance" name="apoL_a04_naissance"
                               value="{{ old('apoL_a04_naissance', $student->apoL_a04_naissance) }}"
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
                                <input type="text" class="form-control" id="cod_etu" name="cod_etu"
                                       value="{{ old('cod_etu', $student->cod_etu) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cin_ind" class="form-label">CIN</label>
                                <input type="text" class="form-control" id="cin_ind" name="cin_ind"
                                       value="{{ old('cin_ind', $student->cin_ind) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cod_sex_etu" class="form-label">Sexe</label>
                                <select class="form-control" id="cod_sex_etu" name="cod_sex_etu">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="M" {{ old('cod_sex_etu', $student->cod_sex_etu) == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('cod_sex_etu', $student->cod_sex_etu) == 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lib_vil_nai_etu" class="form-label">Ville de Naissance</label>
                                <input type="text" class="form-control" id="lib_vil_nai_etu" name="lib_vil_nai_etu"
                                       value="{{ old('lib_vil_nai_etu', $student->lib_vil_nai_etu) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Technical Fields -->
                    <hr>
                    <h6>Informations Techniques (Optionnel)</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cod_etp" class="form-label">Code ETP</label>
                                <input type="text" class="form-control" id="cod_etp" name="cod_etp"
                                       value="{{ old('cod_etp', $student->cod_etp) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cod_anu" class="form-label">Code Année</label>
                                <input type="text" class="form-control" id="cod_anu" name="cod_anu"
                                       value="{{ old('cod_anu', $student->cod_anu) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="lib_etp" class="form-label">Libellé ETP</label>
                        <input type="text" class="form-control" id="lib_etp" name="lib_etp"
                               value="{{ old('lib_etp', $student->lib_etp) }}">
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.students.show', $student->apoL_a01_code) }}" class="btn btn-secondary">
                            <i>❌</i> Annuler
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i>💾</i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Section -->
        <div class="card border-0 shadow-sm mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i>🗑️</i> Zone de Danger</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Supprimer cet étudiant supprimera définitivement toutes ses données.</p>
                <form method="POST" action="{{ route('admin.students.destroy', $student->apoL_a01_code) }}"
                      onsubmit="return confirm('Êtes-vous absolument sûr de vouloir supprimer cet étudiant? Cette action est irréversible!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i>🗑️</i> Supprimer l'étudiant
                    </button>
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
