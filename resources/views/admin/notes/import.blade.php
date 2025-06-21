@extends('layouts.admin')

@section('title', 'Importer des Notes')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📊</i> Importer des Notes</h2>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i>⬅️</i> Retour au tableau de bord
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i>📁</i> Sélectionner le fichier ODS</h5>
                    </div>
                    <div class="card-body">
                        <form id="import-form" enctype="multipart/form-data">
                            @csrf

                            <!-- Import Type Selection -->
                            <div class="mb-4">
                                <label class="form-label">Type d'importation <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="import_type" id="old_session" value="old_session" required>
                                            <label class="form-check-label" for="old_session">
                                                <strong>Sessions anciennes</strong><br>
                                                <small class="text-muted">Import direct dans la table 'notes'</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="import_type" id="current_session" value="current_session" required>
                                            <label class="form-check-label" for="current_session">
                                                <strong>Session actuelle</strong><br>
                                                <small class="text-muted">Import dans la table 'notes_actu'</small>
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="session_type" class="form-label">Type de session <span class="text-danger">*</span></label>
                                            <select class="form-control" id="session_type" name="session_type">
                                                <option value="">-- Sélectionner --</option>
                                                <option value="printemps">Printemps</option>
                                                <option value="automne">Automne</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="result_type" class="form-label">Type de résultat <span class="text-danger">*</span></label>
                                            <select class="form-control" id="result_type" name="result_type">
                                                <option value="">-- Sélectionner --</option>
                                                <option value="normale">Normale</option>
                                                <option value="rattrapage">Rattrapage</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- File Selection -->
                            <div class="mb-3">
                                <label for="file" class="form-label">Fichier Notes <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="file" name="file" accept=".ods,.csv" required>
                                <small class="form-text text-muted">Formats supportés: ODS, CSV (max 100MB) - CSV recommandé pour gros fichiers</small>
                            </div>

                            <!-- CSV Options (hidden by default) -->
                            <div id="csv-options" class="mb-3" style="display: none;">
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

                            <!-- Chunk Size Option -->
                            <div class="mb-4">
                                <label for="chunk_size" class="form-label">Taille des chunks (optionnel)</label>
                                <select class="form-control" id="chunk_size" name="chunk_size">
                                    <option value="100">100 enregistrements par chunk (pour gros fichiers)</option>
                                    <option value="200" selected>200 enregistrements par chunk (défaut)</option>
                                    <option value="500">500 enregistrements par chunk (pour petits fichiers)</option>
                                    <option value="1000">1000 enregistrements par chunk (fichiers rapides)</option>
                                </select>
                                <small class="form-text text-muted">Pour les très gros fichiers (>50000 lignes), utilisez une taille plus petite (100) pour éviter les erreurs de mémoire.</small>
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
                                    <i>📊</i> Importer les Notes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i>ℹ️</i> Format attendu</h6>
                    </div>
                    <div class="card-body">
                        <h6>Colonnes acceptées:</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Code Étudiant:</strong> <code>apoL_a01_code</code>, <code>apol_a01_code</code>, <code>cod_etu</code>, <code>apogee</code></li>
                            <li><strong>Code Module:</strong> <code>code_module</code>, <code>cod_module</code></li>
                            <li><strong>Nom Module:</strong> <code>nom_module</code>, <code>lib_module</code></li>
                            <li><strong>Note:</strong> <code>note</code>, <code>grade</code>, <code>resultat</code></li>
                        </ul>

                        <div class="alert alert-warning mt-3">
                            <small>
                                <strong>⚠️ Important :</strong><br>
                                • Sessions anciennes : Table 'notes'<br>
                                • Session actuelle : Table 'notes_actu'<br>
                                • Format ODS uniquement<br>
                                • Max 100MB par fichier<br>
                                • Pour fichiers >50k lignes : chunk 100<br>
                                • Pour fichiers <10k lignes : chunk 500-1000
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i>📄</i> Différences</h6>
                    </div>
                    <div class="card-body">
                        <p class="small"><strong>Sessions anciennes :</strong> Import direct sans options supplémentaires</p>
                        <p class="small"><strong>Session actuelle :</strong> Nécessite la sélection du type de session et de résultat</p>
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
    const resultTypeSelect = document.getElementById('result_type');
    const fileInput = document.getElementById('file');
    const csvOptions = document.getElementById('csv-options');

    // Handle import type change
    importTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'current_session') {
                currentSessionOptions.style.display = 'block';
                sessionTypeSelect.required = true;
                resultTypeSelect.required = true;
            } else {
                currentSessionOptions.style.display = 'none';
                sessionTypeSelect.required = false;
                resultTypeSelect.required = false;
                sessionTypeSelect.value = '';
                resultTypeSelect.value = '';
            }
        });
    });

    // Handle file type change
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        const form = document.getElementById('import-form');

        if (file) {
            const extension = file.name.toLowerCase().split('.').pop();

            if (extension === 'csv') {
                csvOptions.style.display = 'block';
                form.action = '{{ route('admin.notes.import.csv') }}';
            } else {
                csvOptions.style.display = 'none';
                form.action = '{{ route('admin.notes.import') }}';
            }
        }
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
            const response = await fetch(this.action, {
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
</script>
@endsection
