@extends('layouts.admin')

@section('title', 'Importer des Modules')

@section('content')
<style>
/* Custom styles for the import page */
.file-drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-drop-zone:hover {
    border-color: #ffc107;
    background-color: #fff3cd;
}

.file-drop-zone.dragover {
    border-color: #dc3545;
    background-color: #f8d7da;
}

.json-preview {
    background-color: #2d3748;
    color: #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    max-height: 300px;
    overflow-y: auto;
}

.progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #dc3545;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📥</i> Importer des Modules</h2>
            <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">
                <i>⬅️</i> Retour à la liste
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <div class="card-body text-white">
                        <h3 id="current-modules">{{ \App\Models\Module::count() }}</h3>
                        <p class="mb-0">Modules Actuels</p>
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
                        <p class="mb-0">Ignorés (Client)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                    <div class="card-body text-white">
                        <h3 id="validation-status">En Attente</h3>
                        <p class="mb-0">Statut Client</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i>📁</i> Sélectionner le Fichier JSON</h5>
                    </div>
                    <div class="card-body">
                        {{-- Removed enctype="multipart/form-data" as we'll handle submission with JS fetch --}}
                        <form id="import-form">
                            @csrf

                            <div class="file-drop-zone mb-4" id="drop-zone">
                                <div class="file-upload-icon">📁</div>
                                <h5>Glissez-déposez votre fichier JSON ici</h5>
                                <p class="text-muted">ou cliquez pour sélectionner un fichier</p>
                                <input type="file"
                                       class="d-none"
                                       id="json_file"
                                       name="json_file"
                                       accept=".json,application/json">
                            </div>

                            <div id="file-info" class="mb-4" style="display: none;">
                                <div class="alert alert-info">
                                    <strong>Fichier sélectionné:</strong> <span id="file-name"></span><br>
                                    <strong>Taille:</strong> <span id="file-size"></span><br>
                                    <strong>Modules dans le fichier:</strong> <span id="total-modules-in-file"></span>
                                </div>
                            </div>

                            <div id="validation-results" class="mb-4" style="display: none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Résultats de la Validation Client</h6>
                                    </div>
                                    <div class="card-body" id="validation-content">
                                        </div>
                                </div>
                            </div>

                            <div id="preview-section" class="mb-4" style="display: none;">
                                <h6>Aperçu des modules valides (premiers 3):</h6>
                                <div class="json-preview" id="file-preview"></div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">
                                    <i>❌</i> Annuler
                                </a>
                                {{-- Changed type to button and added onclick handler --}}
                                <button type="button" class="btn btn-danger" id="import-btn" disabled onclick="submitImportForm()">
                                    <i>📥</i> Importer les Modules
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i>ℹ️</i> Instructions</h6>
                    </div>
                    <div class="card-body">
                        <h6>Format JSON Attendu:</h6>
                        <p class="small">Le fichier doit contenir un tableau d'objets modules (ou format Oracle).</p>

                        <h6>Champs Requis (Non vides):</h6>
                        <ul class="list-unstyled">
                            <li><i style="color: green;">✓</i> <code>cod_elp</code> (Code Module)</li>
                            <li><i style="color: green;">✓</i> <code>lib_elp</code> (Libellé Français)</li>
                        </ul>

                        <h6 class="mt-3">Champs Optionnels:</h6>
                        <ul class="list-unstyled small">
                            <li><i>○</i> <code>lib_elp_arb</code> (Libellé Arabe)</li>
                            <li><i>○</i> <code>cod_cmp</code> (Code Composante)</li>
                            <li><i>○</i> <code>nbr_pnt_ect_elp</code> (Points ECTS)</li>
                            <li><i>○</i> <code>eta_elp</code> (Statut: A/I)</li>
                            <li><i>○</i> <code>lib_nom_rsp_elp</code> (Responsable)</li>
                            </ul>

                        <div class="alert alert-warning mt-3">
                            <small>
                                <strong>⚠️ Limites :</strong><br>
                                • Max 10000 modules par fichier<br>
                                • Taille max : 50MB<br>
                                • Format : JSON uniquement<br>
                                • Les modules sans 'cod_elp' ou 'lib_elp' seront ignorés.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i>📄</i> Modèle</h6>
                    </div>
                    <div class="card-body text-center">
                        <p class="small">Téléchargez un exemple :</p>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="downloadTemplate()">
                            <i>⬇️</i> Télécharger Modèle
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="progress-overlay" id="progress-overlay">
    <div class="bg-white p-4 rounded shadow">
        <div class="spinner"></div>
        <h5>Importation en cours...</h5>
        <p class="text-muted">Veuillez patienter pendant le traitement du fichier.</p>
    </div>
</div>

<script>
let selectedFile = null;
let validationPassed = false;
let filteredModulesData = []; // Global variable to store the filtered data

// File drop zone functionality
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('json_file');
const importBtn = document.getElementById('import-btn');

dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        handleFileSelection(files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFileSelection(e.target.files[0]);
    }
});

function handleFileSelection(file) {
    selectedFile = file;

    // Reset stats
    document.getElementById('ready-to-import').textContent = '0';
    document.getElementById('skipped-count').textContent = '0';
    document.getElementById('validation-status').textContent = 'En Attente';
    importBtn.disabled = true;
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('validation-results').style.display = 'none';
    document.getElementById('preview-section').style.display = 'none';
    document.getElementById('validation-content').innerHTML = '';


    // Show file info
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    document.getElementById('file-info').style.display = 'block';

    // Validate file
    if (file.type === 'application/json' || file.name.endsWith('.json')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const jsonData = JSON.parse(e.target.result);
                validateJsonData(jsonData);
            } catch (error) {
                showValidationResults(false, 'Fichier JSON invalide: ' + error.message);
                updateStats(0, false, 0); // Reset stats if JSON is invalid
            }
        };
        reader.readAsText(file);
    } else {
        showValidationResults(false, 'Veuillez sélectionner un fichier JSON valide.');
        updateStats(0, false, 0); // Reset stats if file type is invalid
    }
}

function validateJsonData(data) {
    const results = {
        isValid: true, // Renamed from 'valid' to avoid conflict with `validationPassed`
        errors: [],
        warnings: [],
        totalModulesInFile: 0, // Total modules before filtering
        importedCount: 0,      // Modules ready to be imported (after filtering)
        skippedCount: 0        // Modules skipped due to client-side validation (empty cod_elp/lib_elp)
    };

    let rawModulesData = [];

    // --- Parsing logic (same as before, extracts data into rawModulesData) ---
    if (data.results && Array.isArray(data.results)) {
        data.results.forEach(result => {
            if (result.items && Array.isArray(result.items) &&
                result.columns && Array.isArray(result.columns)) {
                const columns = result.columns;
                const columnNames = columns.map(col => col.name || col);
                result.items.forEach(item => {
                    if (Array.isArray(item)) {
                        const moduleRow = {};
                        item.forEach((value, index) => {
                            if (columnNames[index]) {
                                moduleRow[columnNames[index].toLowerCase()] = value;
                            }
                        });
                        rawModulesData.push(moduleRow);
                    } else if (typeof item === 'object' && item !== null) {
                        const moduleRow = {};
                        for (const key in item) {
                            if (Object.hasOwnProperty.call(item, key)) {
                                moduleRow[key.toLowerCase()] = item[key];
                            }
                        }
                        rawModulesData.push(moduleRow);
                    }
                });
            }
        });
    } else if (Array.isArray(data)) {
        rawModulesData = data.map(item => {
            if (typeof item === 'object' && item !== null) {
                const moduleRow = {};
                for (const key in item) {
                    if (Object.hasOwnProperty.call(item, key)) {
                        moduleRow[key.toLowerCase()] = item[key];
                    }
                }
                return moduleRow;
            }
            return item;
        });
    } else if (data.data && Array.isArray(data.data)) {
        rawModulesData = data.data.map(item => {
            if (typeof item === 'object' && item !== null) {
                const moduleRow = {};
                for (const key in item) {
                    if (Object.hasOwnProperty.call(item, key)) {
                        moduleRow[key.toLowerCase()] = item[key];
                    }
                }
                return moduleRow;
            }
            return item;
        });
    } else if (data.items && Array.isArray(data.items)) {
        rawModulesData = data.items.map(item => {
            if (typeof item === 'object' && item !== null) {
                const moduleRow = {};
                for (const key in item) {
                    if (Object.hasOwnProperty.call(item, key)) {
                        moduleRow[key.toLowerCase()] = item[key];
                    }
                }
                return moduleRow;
            }
            return item;
        });
    } else {
        const keys = Object.keys(data);
        let foundArray = false;
        for (const key of keys) {
            if (Array.isArray(data[key]) && data[key].length > 0) {
                rawModulesData = data[key].map(item => {
                    if (typeof item === 'object' && item !== null) {
                        const moduleRow = {};
                        for (const k in item) {
                            if (Object.hasOwnProperty.call(item, k)) {
                                moduleRow[k.toLowerCase()] = item[k];
                            }
                        }
                        return moduleRow;
                    }
                    return item;
                });
                foundArray = true;
                results.warnings.push(`Données trouvées dans la propriété: "${key}".`);
                break;
            }
        }
        if (!foundArray) {
            results.isValid = false;
            results.errors.push(`Structure JSON non reconnue. Propriétés disponibles: ${keys.join(', ')}`);
        }
    }
    // --- End of Parsing logic ---

    results.totalModulesInFile = rawModulesData.length;
    document.getElementById('total-modules-in-file').textContent = results.totalModulesInFile;

    if (results.totalModulesInFile === 0 && results.errors.length === 0) {
        results.isValid = false;
        results.errors.push('Aucune donnée de module trouvée dans le fichier.');
    } else if (results.totalModulesInFile > 10000) {
        results.isValid = false;
        results.errors.push('Maximum 10000 modules autorisés par fichier (avant filtrage).');
    } else if (results.isValid) { // Only proceed with filtering if no structural errors
        const tempFilteredModules = [];
        rawModulesData.forEach((module, index) => {
            if (!module || typeof module !== 'object') {
                results.warnings.push(`Ligne ${index + 1} ignorée: L'entrée n'est pas un objet valide.`);
                results.skippedCount++;
                return;
            }

            // Check for required cod_elp and lib_elp fields
            const codElp = module['cod_elp'] ? String(module['cod_elp']).trim() : '';
            const libElp = module['lib_elp'] ? String(module['lib_elp']).trim() : '';

            if (codElp === '') {
                results.warnings.push(`Ligne ${index + 1} ignorée: Champ 'cod_elp' manquant ou vide.`);
                results.skippedCount++;
            } else if (libElp === '') {
                results.warnings.push(`Ligne ${index + 1} ignorée (code: ${codElp}): Champ 'lib_elp' manquant ou vide.`);
                results.skippedCount++;
            } else {
                tempFilteredModules.push(module);
            }
        });

        filteredModulesData = tempFilteredModules; // Store filtered data globally
        results.importedCount = filteredModulesData.length;

        if (results.importedCount === 0 && results.totalModulesInFile > 0 && results.errors.length === 0) {
            results.isValid = false;
            results.errors.push('Aucun module valide trouvé après filtrage.');
        }

        if (results.importedCount > 0) {
            showPreview(filteredModulesData.slice(0, 3));
        } else {
            document.getElementById('preview-section').style.display = 'none';
        }
    }

    validationPassed = results.isValid && results.importedCount > 0;
    showValidationResults(validationPassed, null, results);
    updateStats(results.importedCount, validationPassed, results.skippedCount);
}

function showValidationResults(isValid, errorMessage, results = null) {
    const validationResults = document.getElementById('validation-results');
    const validationContent = document.getElementById('validation-content');

    importBtn.disabled = !isValid;
    validationResults.style.display = 'block';

    let content = '';

    if (isValid) {
        content = `
            <div class="alert alert-success">
                <i style="color: green;">✓</i> <strong>Validation client réussie!</strong><br>
                ${results.importedCount} modules prêts à être importés.
                ${results.skippedCount > 0 ? `<br>(${results.skippedCount} modules ignorés pour champs manquants/vides)` : ''}
                ${results.warnings.length > 0 ? '<br><small>Certaines entrées ont des avertissements.</small>' : ''}
            </div>
        `;
    } else {
        content = `<div class="alert alert-danger">
            <i style="color: red;">✗</i> <strong>Erreurs de validation client:</strong><br>`;

        if (errorMessage) {
            content += `• ${errorMessage}<br>`;
        }

        if (results && results.errors.length > 0) {
            results.errors.forEach(error => {
                content += `• ${error}<br>`;
            });
        }
        content += `</div>`;
    }

    if (results && results.warnings.length > 0) {
        content += `<div class="alert alert-warning mt-3">
            <strong>Avertissements:</strong><br>`;
        results.warnings.forEach(warning => {
            content += `• ${warning}<br>`;
        });
        content += `</div>`;
    }
    validationContent.innerHTML = content;
}

function showPreview(modules) {
    const previewSection = document.getElementById('preview-section');
    const filePreview = document.getElementById('file-preview');
    previewSection.style.display = 'block';
    filePreview.textContent = JSON.stringify(modules, null, 2);
}

function updateStats(importedCount, isValid, skippedCount) {
    document.getElementById('ready-to-import').textContent = importedCount;
    document.getElementById('skipped-count').textContent = skippedCount;
    document.getElementById('validation-status').textContent = isValid ? 'Validé' : 'Erreur';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Download template function (unchanged)
function downloadTemplate() {
    const template = [
        {
            "cod_elp": "INFO101",
            "lib_elp": "Introduction à l'Informatique",
            "lib_elp_arb": "مقدمة في المعلوماتية",
            "cod_cmp": "FSJS",
            "nbr_pnt_ect_elp": 6,
            "eta_elp": "A"
        },
        {
            "cod_elp": "MATH202",
            "lib_elp": "Algèbre Linéaire",
            "lib_elp_arb": "الجبر الخطي",
            "cod_cmp": "FSJS",
            "nbr_pnt_ect_elp": 5,
            "eta_elp": "A"
        },
        {
            "cod_elp": "DROIT301",
            "lib_elp": "Droit Civil",
            "lib_elp_arb": "القانون المدني",
            "cod_cmp": "FSJS",
            "nbr_pnt_ect_elp": 4,
            "eta_elp": "I"
        }
    ];

    const dataStr = JSON.stringify(template, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'template_modules.json';
    link.click();
    URL.revokeObjectURL(url);
}

// Custom form submission using Fetch API
// Inside your <script> block in resources/views/admin/modules/import.blade.php

async function submitImportForm() {
    if (!validationPassed) {
        alert('Veuillez d\'abord corriger les erreurs de validation ou sélectionner des modules valides.');
        return;
    }

    const progressOverlay = document.getElementById('progress-overlay');
    const importBtn = document.getElementById('import-btn');

    progressOverlay.style.display = 'flex';
    importBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Importation...';
    importBtn.disabled = true;

    try {
        const tokenInput = document.querySelector('#import-form input[name="_token"]');
        const token = tokenInput ? tokenInput.value : '';

        const formData = new FormData();
        const jsonBlob = new Blob([JSON.stringify(filteredModulesData)], { type: 'application/json' });
        formData.append('json_file', jsonBlob, 'filtered_modules.json');
        formData.append('_token', token);

        const response = await fetch('{{ route('admin.modules.import') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        let data;
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            data = await response.json();
        } else {
            const textResponse = await response.text();
            console.error("Server responded with non-JSON content:", textResponse);
            throw new Error("Server did not return a valid JSON response. Check server logs or response content directly.");
        }

        if (response.ok) {
            window.location.href = data.redirect || '{{ route('admin.modules.import.results') }}';
        } else {
            let errorMessage = 'Erreur inattendue lors de l\'importation.';
            let detailedErrors = [];

            if (data && data.message) {
                errorMessage = data.message;
            }

            if (data && data.errors) {
                if (Array.isArray(data.errors)) {
                    // Case 1: data.errors is an array of objects (from your custom error array in controller)
                    data.errors.forEach(err => {
                        if (err.message) {
                            detailedErrors.push(err.message);
                        }
                    });
                } else if (typeof data.errors === 'object' && data.errors !== null) {
                    // Case 2: data.errors is an object where values are arrays (from Laravel ValidationException)
                    for (const key in data.errors) {
                        if (Array.isArray(data.errors[key])) {
                            detailedErrors.push(...data.errors[key]); // Spread the array messages
                        } else {
                            detailedErrors.push(data.errors[key]); // If it's a string or other non-array value
                        }
                    }
                }
            }

            if (detailedErrors.length > 0) {
                errorMessage += '\n\nDétails des erreurs:\n' + detailedErrors.join('\n');
            }

            alert('Erreur d\'importation: ' + errorMessage);
        }
    } catch (error) {
        console.error('Error during import:', error);
        alert('Une erreur s\'est produite lors de l\'importation. Veuillez réessayer. (Détails dans la console du navigateur)');
    } finally {
        progressOverlay.style.display = 'none';
        importBtn.innerHTML = '<i>📥</i> Importer les Modules';
        importBtn.disabled = false;
    }
}
// Ensure CSRF token meta tag is present in your layouts/admin.blade.php for submitImportForm to work correctly
// <head>
//     ...
//     <meta name="csrf-token" content="{{ csrf_token() }}">
//     ...
// </head>
</script>
@endsection
