@extends('layouts.admin')

@section('title', 'Importer des Étudiants')

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
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📥</i> Importer des Étudiants</h2>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                <i>⬅️</i> Retour à la liste
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <div class="card-body text-white">
                        <h3 id="current-students">{{ \App\Models\Student::count() }}</h3>
                        <p class="mb-0">Étudiants Actuels</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <div class="card-body text-white">
                        <h3 id="ready-to-import">0</h3>
                        <p class="mb-0">Prêts à Importer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                    <div class="card-body text-white">
                        <h3 id="validation-status">En Attente</h3>
                        <p class="mb-0">Statut</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Import Form -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i>📁</i> Sélectionner le Fichier JSON</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" id="import-form">
                            @csrf

                            <!-- File Drop Zone -->
                            <div class="file-drop-zone mb-4" id="drop-zone">
                                <div class="file-upload-icon">📁</div>
                                <h5>Glissez-déposez votre fichier JSON ici</h5>
                                <p class="text-muted">ou cliquez pour sélectionner un fichier</p>
                                <input type="file"
                                       class="d-none @error('json_file') is-invalid @enderror"
                                       id="json_file"
                                       name="json_file"
                                       accept=".json,application/json">
                            </div>

                            @error('json_file')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <!-- File Info -->
                            <div id="file-info" class="mb-4" style="display: none;">
                                <div class="alert alert-info">
                                    <strong>Fichier sélectionné:</strong> <span id="file-name"></span><br>
                                    <strong>Taille:</strong> <span id="file-size"></span><br>
                                    <strong>Nombre d'étudiants:</strong> <span id="student-count"></span>
                                </div>
                            </div>

                            <!-- Validation Results -->
                            <div id="validation-results" class="mb-4" style="display: none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Résultats de la Validation</h6>
                                    </div>
                                    <div class="card-body" id="validation-content">
                                        <!-- Content will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Section -->
                            <div id="preview-section" class="mb-4" style="display: none;">
                                <h6>Aperçu du fichier (premiers 3 étudiants):</h6>
                                <div class="json-preview" id="file-preview"></div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                                    <i>❌</i> Annuler
                                </a>
                                <button type="submit" class="btn btn-danger" id="import-btn" disabled>
                                    <i>📥</i> Importer les Étudiants
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Instructions Sidebar -->
            <div class="col-md-4">
                <!-- Import Instructions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i>ℹ️</i> Instructions</h6>
                    </div>
                    <div class="card-body">
                        <h6>Format JSON Attendu:</h6>
                        <p class="small">Le fichier doit contenir un tableau d'objets étudiants.</p>

                        <h6>Champs Requis:</h6>
                        <ul class="list-unstyled">
                            <li><i style="color: green;">✓</i> <code>COD_ETU</code> ou <code>COD_ETU_1</code></li>
                            <li><i style="color: green;">✓</i> <code>LIB_NOM_PAT_IND</code> ou <code>LIB_NOM_PAT_IND_1</code></li>
                            <li><i style="color: green;">✓</i> <code>LIB_PR1_IND</code> ou <code>LIB_PR1_IND_1</code></li>
                        </ul>

                        <h6 class="mt-3">Champs Optionnels:</h6>
                        <ul class="list-unstyled small">
                            <li><i>○</i> <code>DATE_NAI_IND</code> (Date naissance)</li>
                            <li><i>○</i> <code>COD_SEX_ETU</code> (Sexe)</li>
                            <li><i>○</i> <code>LIB_VIL_NAI_ETU</code> (Ville naissance)</li>
                            <li><i>○</i> <code>CIN_IND</code> (CIN)</li>
                            <li><i>○</i> <code>COD_ETP</code> (Code ETP)</li>
                            <li><i>○</i> <code>LIB_ETP</code> (Libellé ETP)</li>
                        </ul>

                        <div class="alert alert-warning mt-3">
                            <small>
                                <strong>⚠️ Limites :</strong><br>
                                • Max 20000 étudiants par fichier<br>
                                • Taille max : 50MB<br>
                                • Format : JSON uniquement
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Download Template -->
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

<!-- Progress Overlay -->
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

// File drop zone functionality
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('json_file');

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
            }
        };
        reader.readAsText(file);
    } else {
        showValidationResults(false, 'Veuillez sélectionner un fichier JSON valide.');
    }
}

function validateJsonData(data) {
    const results = {
        valid: true,
        errors: [],
        warnings: [],
        studentCount: 0
    };

        console.log('Raw JSON data received:', typeof data);
    console.log('Is data an object?', typeof data === 'object');
    console.log('Data keys:', Object.keys(data));

    if (data.results) {
        console.log('Results found:', Array.isArray(data.results), data.results.length);
        if (data.results[0]) {
            console.log('First result keys:', Object.keys(data.results[0]));
            if (data.results[0].items) {
                console.log('Items found:', data.results[0].items.length);
            }
            if (data.results[0].columns) {
                console.log('Columns found:', data.results[0].columns.length);
            }
        }
    }

    // Handle Oracle export format
    let studentsData = [];

    // Debug: Show JSON structure
    results.warnings.push(`Structure détectée: ${Array.isArray(data) ? 'Array' : typeof data}`);

if (data.results && Array.isArray(data.results)) {
    // Oracle export format
    console.log('Oracle format detected, results length:', data.results.length);

    data.results.forEach((result, resultIndex) => {
        console.log(`Processing result ${resultIndex}:`, result);

        if (result.items && Array.isArray(result.items) && result.columns && Array.isArray(result.columns)) {
            const columns = result.columns;
            const columnNames = columns.map(col => col.name || col);

            console.log('Column names found:', columnNames.length, 'columns');
            console.log('Items found:', result.items.length, 'items');

            result.items.forEach((item, itemIndex) => {
                if (Array.isArray(item)) {
                    // Item is an array of values, map to column names
                    const studentRow = {};
                    item.forEach((value, index) => {
                        if (columnNames[index]) {
                            studentRow[columnNames[index]] = value;
                        }
                    });
                    studentsData.push(studentRow);

                    // Log first few students for debugging
                    if (itemIndex < 3) {
                        console.log(`Student ${itemIndex}:`, studentRow);
                    }
                } else if (typeof item === 'object' && item !== null) {
                    // Item is already an object
                    studentsData.push(item);
                    if (itemIndex < 3) {
                        console.log(`Student object ${itemIndex}:`, item);
                    }
                }
            });
        } else {
            console.log('Missing items or columns in result:', result);
        }
    });

    console.log('Total students parsed:', studentsData.length);
} else if (Array.isArray(data)) {
        // Simple array format
        console.log('Simple array format detected, length:', data.length);
        studentsData = data;
    } else if (data.data && Array.isArray(data.data)) {
        // Data wrapped in data property
        console.log('Data.data format detected, length:', data.data.length);
        studentsData = data.data;
    } else if (data.items && Array.isArray(data.items)) {
        // Data wrapped in items property
        console.log('Data.items format detected, length:', data.items.length);
        studentsData = data.items;
    } else {
        // Try to find any array in the object
        const keys = Object.keys(data);
        let foundArray = false;

        for (const key of keys) {
            if (Array.isArray(data[key]) && data[key].length > 0) {
                console.log(`Found array in property '${key}', length:`, data[key].length);
                studentsData = data[key];
                foundArray = true;
                results.warnings.push(`Données trouvées dans la propriété: ${key}`);
                break;
            }
        }

        if (!foundArray) {
            results.valid = false;
            results.errors.push(`Structure JSON non reconnue. Propriétés disponibles: ${keys.join(', ')}`);
            console.log('Available properties:', keys);
            console.log('Full data object:', data);
        }
    }

    console.log('Final studentsData length:', studentsData.length);
    console.log('Sample student data:', studentsData.slice(0, 2));

    if (studentsData.length === 0) {
        results.valid = false;
        results.errors.push('Aucune donnée d\'étudiant trouvée dans le fichier.');

        // Add debugging info
        if (typeof data === 'object' && data !== null) {
            const keys = Object.keys(data);
            results.errors.push(`Propriétés trouvées dans le JSON: ${keys.join(', ')}`);

            // Show first level structure
            keys.forEach(key => {
                const value = data[key];
                if (Array.isArray(value)) {
                    results.errors.push(`- ${key}: Array de ${value.length} éléments`);
                } else if (typeof value === 'object' && value !== null) {
                    results.errors.push(`- ${key}: Objet avec propriétés [${Object.keys(value).join(', ')}]`);
                } else {
                    results.errors.push(`- ${key}: ${typeof value}`);
                }
            });
        }
    } else if (studentsData.length > 20000) {
        results.valid = false;
        results.errors.push('Maximum 20000 étudiants autorisés par fichier.');
    } else {
        results.studentCount = studentsData.length;

        // Show sample of first student for debugging
        if (studentsData.length > 0) {
            const firstStudent = studentsData[0];
            const availableFields = Object.keys(firstStudent);
            results.warnings.push(`Champs disponibles dans le premier étudiant: ${availableFields.slice(0, 10).join(', ')}${availableFields.length > 10 ? '...' : ''}`);
        }

        // Validate structure
        const requiredFields = ['cod_etu', 'lib_nom_pat_ind', 'lib_pr1_ind'];
        const alternativeFields = ['COD_ETU_1', 'LIB_NOM_PAT_IND_1', 'LIB_PR1_IND_1'];
        const sampleSize = Math.min(5, studentsData.length);

        for (let i = 0; i < sampleSize; i++) {
            const student = studentsData[i];
            if (!student || typeof student !== 'object') {
                results.errors.push(`Élément ${i + 1} n'est pas un objet valide`);
                results.valid = false;
                continue;
            }

            for (let j = 0; j < requiredFields.length; j++) {
                const field = requiredFields[j];
                const altField = alternativeFields[j];
                if (!student[field] && !student[altField]) {
                    results.errors.push(`Champ requis manquant "${field}" ou "${altField}" à la ligne ${i + 1}`);
                    results.valid = false;
                }
            }
        }

        // Show preview
        if (results.valid) {
            showPreview(studentsData.slice(0, 3));
        } else {
            // Show preview anyway for debugging
            showPreview(studentsData.slice(0, 1));
        }
    }

    showValidationResults(results.valid, null, results);
    updateStats(results.studentCount, results.valid);
}

function showValidationResults(isValid, errorMessage, results = null) {
    validationPassed = isValid;
    const importBtn = document.getElementById('import-btn');
    const validationResults = document.getElementById('validation-results');
    const validationContent = document.getElementById('validation-content');

    importBtn.disabled = !isValid;
    validationResults.style.display = 'block';

    if (isValid) {
        validationContent.innerHTML = `
            <div class="alert alert-success">
                <i style="color: green;">✓</i> <strong>Validation réussie!</strong><br>
                ${results.studentCount} étudiants prêts à être importés.
                ${results.warnings.length > 0 ? '<br><small>' + results.warnings.join('<br>') + '</small>' : ''}
            </div>
        `;
    } else {
        let content = `<div class="alert alert-danger">
            <i style="color: red;">✗</i> <strong>Erreurs de validation:</strong><br>`;

        if (errorMessage) {
            content += `• ${errorMessage}<br>`;
        }

        if (results && results.errors.length > 0) {
            results.errors.forEach(error => {
                content += `• ${error}<br>`;
            });
        }

        if (results && results.warnings.length > 0) {
            content += `<br><strong>Informations de débogage:</strong><br>`;
            results.warnings.forEach(warning => {
                content += `<small>• ${warning}</small><br>`;
            });
        }

        content += `</div>`;
        validationContent.innerHTML = content;
    }
}

function showPreview(students) {
    const previewSection = document.getElementById('preview-section');
    const filePreview = document.getElementById('file-preview');

    previewSection.style.display = 'block';
    filePreview.textContent = JSON.stringify(students, null, 2);
}

function updateStats(studentCount, isValid) {
    document.getElementById('ready-to-import').textContent = isValid ? studentCount : 0;
    document.getElementById('validation-status').textContent = isValid ? 'Validé' : 'Erreur';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Download template function
function downloadTemplate() {
    const template = [
        {
            "COD_ETU": "12345678",
            "LIB_NOM_PAT_IND": "Alami",
            "LIB_PR1_IND": "Mohammed",
            "DATE_NAI_IND": "15/03/2000",
            "COD_SEX_ETU": "M",
            "LIB_VIL_NAI_ETU": "Casablanca",
            "CIN_IND": "AB123456",
            "COD_ETP": "ETP001",
            "LIB_ETP": "Licence Fondamentale"
        },
        {
            "COD_ETU": "12345679",
            "LIB_NOM_PAT_IND": "Benali",
            "LIB_PR1_IND": "Fatima",
            "DATE_NAI_IND": "22/07/1999",
            "COD_SEX_ETU": "F",
            "LIB_VIL_NAI_ETU": "Rabat",
            "CIN_IND": "CD789012",
            "COD_ETP": "ETP001",
            "LIB_ETP": "Licence Fondamentale"
        },
        {
            "COD_ETU": "12345680",
            "LIB_NOM_PAT_IND": "Chakir",
            "LIB_PR1_IND": "Ahmed",
            "DATE_NAI_IND": "10/11/2001",
            "COD_SEX_ETU": "M",
            "LIB_VIL_NAI_ETU": "Fès",
            "CIN_IND": "EF345678",
            "COD_ETP": "ETP002",
            "LIB_ETP": "Master Spécialisé"
        }
    ];

    const dataStr = JSON.stringify(template, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'template_students.json';
    link.click();
    URL.revokeObjectURL(url);
}

// Form submission with loading state
document.getElementById('import-form').addEventListener('submit', function(e) {
    if (!validationPassed) {
        e.preventDefault();
        alert('Veuillez d\'abord corriger les erreurs de validation.');
        return;
    }

    // Check if file is still selected
    const fileInput = document.getElementById('json_file');
    if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        alert('Veuillez sélectionner à nouveau le fichier JSON.');
        return;
    }

    const progressOverlay = document.getElementById('progress-overlay');
    progressOverlay.style.display = 'flex';

    const importBtn = document.getElementById('import-btn');
    importBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Importation...';
    importBtn.disabled = true;
});
</script>
@endsection
