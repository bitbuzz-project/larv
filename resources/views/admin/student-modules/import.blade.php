@extends('layouts.admin')

@section('title', 'Importer les Inscriptions Pédagogiques')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📊</i> Importer les Inscriptions Pédagogiques</h2>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i>⬅️</i> Retour au tableau de bord
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <div class="card-body text-white">
                        <h3 id="current-modules">{{ \App\Models\PedaModule::count() }}</h3>
                        <p class="mb-0">Inscriptions Actuelles</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <div class="card-body text-white">
                        <h3 id="ready-to-import">0</h3>
                        <p class="mb-0">Prêts à Importer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                    <div class="card-body text-white">
                        <h3 id="skipped-count">0</h3>
                        <p class="mb-0">Ignorées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                    <div class="card-body text-white">
                        <h3 id="validation-status">En Attente</h3>
                        <p class="mb-0">Statut</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i>📁</i> Sélectionner le fichier CSV</h5>
                    </div>
                    <div class="card-body">
                        <form id="import-form">
                            @csrf

                            <!-- Import Type Selection -->
                            <div class="mb-4">
                                <label class="form-label">Type d'importation <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="import_type" id="historical" value="historical" required>
                                            <label class="form-check-label" for="historical">
                                                <strong>Historique</strong><br>
                                                <small class="text-muted">Inscriptions des années précédentes</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="import_type" id="current_session" value="current_session" required>
                                            <label class="form-check-label" for="current_session">
                                                <strong>Session actuelle</strong><br>
                                                <small class="text-muted">Inscriptions de la session en cours</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Année Scolaire -->
                            <div class="mb-3">
                                <label for="annee_scolaire" class="form-label">Année scolaire <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="annee_scolaire" name="annee_scolaire"
                                       value="2024-2025" placeholder="ex: 2024-2025" required>
                            </div>

                            <!-- Current Session Options -->
                            <div id="current_session_options" style="display: none;">
                                <div class="mb-3">
                                    <label for="session_type" class="form-label">Type de session <span class="text-danger">*</span></label>
                                    <select class="form-control" id="session_type" name="session_type">
                                        <option value="">-- Sélectionner --</option>
                                        <option value="printemps">Printemps</option>
                                        <option value="automne">Automne</option>
                                    </select>
                                </div>
                            </div>

                            <!-- File Selection -->
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Fichier CSV <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                <small class="form-text text-muted">Format CSV uniquement (max 50MB)</small>
                            </div>

                            <!-- CSV Options -->
                            <div class="mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Options CSV</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Séparateur:</label>
                                                <select name="delimiter" class="form-select">
                                                    <option value="comma">Virgule (,)</option>
                                                    <option value="semicolon">Point-virgule (;)</option>
                                                    <option value="tab">Tabulation</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Encodage:</label>
                                                <select name="encoding" class="form-select">
                                                    <option value="utf8">UTF-8</option>
                                                    <option value="latin1">Latin-1/ISO-8859-1</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Section -->
                            <div id="progress-section" class="mb-4" style="display: none;">
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm me-2" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                        <span>Importation en cours...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Messages -->
                            <div id="error-messages" class="mb-4" style="display: none;">
                                <div class="alert alert-danger">
                                    <h6>Erreurs d'importation:</h6>
                                    <ul id="error-list" class="mb-0"></ul>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i>❌</i> Annuler
                                </a>
                                <button type="submit" class="btn btn-danger" id="import-btn">
                                    <i>📊</i> Importer les Inscriptions
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i>ℹ️</i> Format CSV attendu</h6>
                    </div>
                    <div class="card-body">
                        <h6>Colonnes requises:</h6>
                        <ul class="list-unstyled">
                            <li><i style="color: green;">✓</i> <strong>apoL_a01_code</strong> - Code Apogée étudiant</li>
                            <li><i style="color: green;">✓</i> <strong>code_module</strong> - Code du module</li>
                            <li><i style="color: green;">✓</i> <strong>module</strong> - Nom du module</li>
                        </ul>

                        <h6 class="mt-3">Colonnes alternatives acceptées:</h6>
                        <ul class="list-unstyled small">
                            <li><i>○</i> <code>cod_etu</code>, <code>apogee</code> pour le code étudiant</li>
                            <li><i>○</i> <code>cod_module</code>, <code>module_code</code> pour le code module</li>
                            <li><i>○</i> <code>nom_module</code>, <code>lib_module</code> pour le nom</li>
                        </ul>

                        <div class="alert alert-warning mt-3">
                            <small>
                                <strong>⚠️ Important :</strong><br>
                                • Les étudiants doivent exister dans la base<br>
                                • Format CSV uniquement<br>
                                • Max 50MB par fichier<br>
                                • Encodage UTF-8 recommandé
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i>📄</i> Exemple CSV</h6>
                    </div>
                    <div class="card-body">
                        <p class="small">Exemple de structure :</p>
                        <pre class="small text-muted">apoL_a01_code,code_module,module
12345678,INFO101,Introduction Informatique
12345678,MATH201,Mathématiques 2
12345679,INFO101,Introduction Informatique</pre>
                        <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="downloadTemplate()">
                            <i>⬇️</i> Télécharger Modèle
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const importTypeRadios = document.querySelectorAll('input[name="import_type"]');
    const currentSessionOptions = document.getElementById('current_session_options');
    const sessionTypeSelect = document.getElementById('session_type');

    // Handle import type change
    importTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'current_session') {
                currentSessionOptions.style.display = 'block';
                sessionTypeSelect.required = true;
            } else {
                currentSessionOptions.style.display = 'none';
                sessionTypeSelect.required = false;
                sessionTypeSelect.value = '';
            }
        });
    });

    // Handle form submission
    document.getElementById('import-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const progressSection = document.getElementById('progress-section');
        const errorMessages = document.getElementById('error-messages');
        const importBtn = document.getElementById('import-btn');

        // Reset UI
        progressSection.style.display = 'block';
        errorMessages.style.display = 'none';
        importBtn.disabled = true;

        try {
            const response = await fetch('{{ route('admin.student-modules.import') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Import réussi!');
                }
            } else {
                throw new Error(data.message || 'Erreur lors de l\'importation');
            }

        } catch (error) {
            progressSection.style.display = 'none';
            errorMessages.style.display = 'block';
            const errorList = document.getElementById('error-list');
            errorList.innerHTML = `<li>${error.message}</li>`;
        } finally {
            importBtn.disabled = false;
        }
    });
});

// Download template function
function downloadTemplate() {
    const template = `apoL_a01_code,code_module,module
12345678,INFO101,Introduction à l'Informatique
12345678,MATH201,Mathématiques 2
12345678,FRAN101,Français 1
12345679,INFO101,Introduction à l'Informatique
12345679,MATH201,Mathématiques 2
12345680,DROIT301,Droit Civil`;

    const dataBlob = new Blob([template], {type: 'text/csv'});
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'template_inscriptions_pedagogiques.csv';
    link.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection
