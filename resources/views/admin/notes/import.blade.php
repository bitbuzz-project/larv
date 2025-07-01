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
                        <h5 class="mb-0"><i>📁</i> Sélectionner le fichier</h5>
                    </div>
                    <div class="card-body">
                        <!-- Formulaire avec method et enctype corrects -->
                        <form id="import-form" method="POST" enctype="multipart/form-data">
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

                            <!-- File Selection avec validation -->
                            <div class="mb-3">
                                <label for="file" class="form-label">Fichier Notes <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="file" name="file" accept=".ods,.csv" required>
                                <small class="form-text text-muted">Formats supportés: ODS, CSV (max 100MB)</small>
                                <div id="file-validation" class="mt-2" style="display: none;">
                                    <div class="alert alert-info">
                                        <strong>Fichier sélectionné:</strong> <span id="selected-file-name"></span><br>
                                        <strong>Taille:</strong> <span id="selected-file-size"></span><br>
                                        <strong>Type:</strong> <span id="selected-file-type"></span>
                                    </div>
                                </div>
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
                                    <option value="100" selected>100 enregistrements par chunk (pour gros fichiers)</option>
                                    <option value="200">200 enregistrements par chunk (défaut)</option>
                                    <option value="500">500 enregistrements par chunk (pour petits fichiers)</option>
                                    <option value="1000">1000 enregistrements par chunk (fichiers rapides)</option>
                                </select>
                            </div>

                            <!-- Progress Section -->
                            <div id="progress-section" class="mb-4" style="display: none;">
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm me-2" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                        <span id="progress-text">Importation en cours...</span>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" role="progressbar" style="width: 0%" id="progress-bar"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Messages -->
                            <div id="error-messages" class="mb-4" style="display: none;">
                                <div class="alert alert-danger">
                                    <h6>Erreurs d'importation:</h6>
                                    <div id="error-content"></div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i>❌</i> Annuler
                                </a>
                                <button type="submit" class="btn btn-danger" id="import-btn" disabled>
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
                        <h6 class="mb-0"><i>ℹ️</i> Format CSV attendu</h6>
                    </div>
                    <div class="card-body">
                        <h6>Colonnes acceptées pour votre CSV :</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Champ requis</th>
                                        <th>Variantes acceptées</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Code Étudiant</strong></td>
                                        <td><small><code>COD_ETU</code>, <code>apol_a01_code</code>, <code>apogee</code></small></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Code Module</strong></td>
                                        <td><small><code>COD_ELP</code>, <code>code_module</code>, <code>cod_module</code></small></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nom Module</strong></td>
                                        <td><small><code>LIB_ELP</code>, <code>nom_module</code>, <code>lib_module</code> (optionnel)</small></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Note</strong></td>
                                        <td><small><code>NOT_ELP</code>, <code>note</code>, <code>grade</code></small></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-success mt-3">
                            <small>
                                <strong>✅ Votre format détecté :</strong><br>
                                <code>COD_ETU</code> → Code Étudiant<br>
                                <code>COD_ELP</code> → Code Module<br>
                                <code>LIB_ELP</code> → Nom Module<br>
                                <code>NOT_ELP</code> → Note<br>
                                <strong>Compatible !</strong>
                            </small>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <small>
                                <strong>⚠️ Important :</strong><br>
                                • Notes entre 0 et 20<br>
                                • Étudiants doivent exister dans la base<br>
                                • Format CSV avec séparateur virgule<br>
                                • Encodage UTF-8 recommandé<br>
                                • Max 100MB par fichier
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i>📄</i> Exemple de votre format</h6>
                    </div>
                    <div class="card-body">
                        <p class="small">Exemple compatible avec votre CSV :</p>
                        <pre class="small text-muted bg-light p-2 rounded">COD_ETU,COD_ELP,LIB_ELP,NOT_ELP
12345678,INFO101,Informatique,15.5
12345679,MATH201,Mathématiques,14.0
12345680,PHYS101,Physique,16.0</pre>

                        <div class="mt-3">
                            <h6 class="small">Colonnes optionnelles détectées :</h6>
                            <ul class="small text-muted">
                                <li><code>COD_ANU</code> - Code Année (ignoré)</li>
                                <li><code>COD_SES</code> - Code Session (ignoré)</li>
                                <li><code>COD_NEL</code> - Code NEL (ignoré)</li>
                                <li><code>COD_TRE</code> - Code TRE (ignoré)</li>
                            </ul>
                            <small class="text-info">Les colonnes non mappées sont automatiquement ignorées.</small>
                        </div>
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
    const importBtn = document.getElementById('import-btn');
    const fileValidation = document.getElementById('file-validation');

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
            validateForm();
        });
    });

    // Handle file selection and validation
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        const form = document.getElementById('import-form');

        if (file) {
            // Show file info
            document.getElementById('selected-file-name').textContent = file.name;
            document.getElementById('selected-file-size').textContent = formatFileSize(file.size);
            document.getElementById('selected-file-type').textContent = file.type || 'Type inconnu';
            fileValidation.style.display = 'block';

            // Determine file type and set form action
            const extension = file.name.toLowerCase().split('.').pop();
            if (extension === 'csv') {
                csvOptions.style.display = 'block';
                form.action = '{{ route('admin.notes.import.csv') }}';
            } else {
                csvOptions.style.display = 'none';
                form.action = '{{ route('admin.notes.import') }}';
            }

            // Validate file size (100MB = 104857600 bytes)
            if (file.size > 104857600) {
                alert('Le fichier est trop volumineux (max 100MB)');
                this.value = '';
                fileValidation.style.display = 'none';
                validateForm();
                return;
            }

            // Validate file type
            if (!['csv', 'ods'].includes(extension)) {
                alert('Format de fichier non supporté. Utilisez CSV ou ODS.');
                this.value = '';
                fileValidation.style.display = 'none';
                validateForm();
                return;
            }
        } else {
            fileValidation.style.display = 'none';
        }

        validateForm();
    });

    // Form validation
    function validateForm() {
        const importType = document.querySelector('input[name="import_type"]:checked');
        const file = fileInput.files[0];
        const sessionType = sessionTypeSelect.value;
        const resultType = resultTypeSelect.value;
        const annee = document.getElementById('annee_scolaire').value;

        let isValid = true;

        // Check basic requirements
        if (!importType || !file || !annee) {
            isValid = false;
        }

        // Check current session requirements
        if (importType && importType.value === 'current_session') {
            if (!sessionType || !resultType) {
                isValid = false;
            }
        }

        importBtn.disabled = !isValid;
    }

    // Add change listeners for validation
    document.getElementById('annee_scolaire').addEventListener('input', validateForm);
    sessionTypeSelect.addEventListener('change', validateForm);
    resultTypeSelect.addEventListener('change', validateForm);

    // Handle form submission with proper file handling
    document.getElementById('import-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validate file is selected
        if (!fileInput.files[0]) {
            alert('Veuillez sélectionner un fichier avant de continuer.');
            return;
        }

        // Create FormData properly for file upload
        const formData = new FormData();

        // Add all form fields manually to ensure proper encoding
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('import_type', document.querySelector('input[name="import_type"]:checked').value);
        formData.append('annee_scolaire', document.getElementById('annee_scolaire').value);

        if (document.getElementById('session_type').value) {
            formData.append('session_type', document.getElementById('session_type').value);
        }
        if (document.getElementById('result_type').value) {
            formData.append('result_type', document.getElementById('result_type').value);
        }

        // Add file - this is critical!
        formData.append('file', fileInput.files[0]);

        // Add CSV options if visible
        if (csvOptions.style.display !== 'none') {
            formData.append('delimiter', document.querySelector('select[name="delimiter"]').value);
            formData.append('encoding', document.querySelector('select[name="encoding"]').value);
        }

        formData.append('chunk_size', document.getElementById('chunk_size').value);

        const progressSection = document.getElementById('progress-section');
        const errorMessages = document.getElementById('error-messages');
        const errorContent = document.getElementById('error-content');
        const progressText = document.getElementById('progress-text');
        const progressBar = document.getElementById('progress-bar');

        // Reset UI
        progressSection.style.display = 'block';
        errorMessages.style.display = 'none';
        importBtn.disabled = true;
        importBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importation...';
        progressText.textContent = 'Préparation de l\'importation...';
        progressBar.style.width = '10%';

        try {
            console.log('Sending file:', fileInput.files[0].name, 'Size:', fileInput.files[0].size);
            console.log('Form action:', this.action);

            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            progressText.textContent = 'Traitement de la réponse...';
            progressBar.style.width = '70%';

            console.log('Response status:', response.status);
            console.log('Response headers:', Object.fromEntries(response.headers));

            // Clone response for debugging
            const responseClone = response.clone();
            const responseText = await responseClone.text();
            console.log('Raw response preview:', responseText.substring(0, 300));

            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);

            let data;
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                throw new Error(`Réponse non-JSON reçue (Status: ${response.status})\n\nContenu: ${responseText.substring(0, 500)}...`);
            }

            progressBar.style.width = '90%';

            if (response.ok) {
                progressText.textContent = 'Import terminé avec succès!';
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar');
                progressBar.classList.add('progress-bar', 'bg-success');

                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '{{ route('admin.notes.import.results') }}';
                    }
                }, 1500);
            } else {
                throw new Error(data.message || 'Erreur lors de l\'importation');
            }

        } catch (error) {
            console.error('Import error:', error);
            progressSection.style.display = 'none';
            errorMessages.style.display = 'block';

            let errorHtml = '<p><strong>Erreur détaillée:</strong></p>';
            errorHtml += '<div class="alert alert-danger">' + error.message + '</div>';

            errorContent.innerHTML = errorHtml;
        } finally {
            importBtn.disabled = false;
            importBtn.innerHTML = '<i>📊</i> Importer les Notes';
        }
    });

    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Initial validation
    validateForm();
});
</script>
@endsection
