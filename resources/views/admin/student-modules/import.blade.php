@extends('layouts.admin')

@section('title', 'Importer les modules des étudiants')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Importer les modules des étudiants</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">Télécharger un fichier JSON des modules étudiants</h6>
        </div>
        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form id="importForm" action="{{ route('admin.student-modules.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="json_file" class="form-label">Sélectionner un fichier JSON:</label>
                    <input type="file" class="form-control" id="json_file" name="json_file" accept=".json">
                    @error('json_file')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="chunk_size" class="form-label">Taille des chunks (optionnel):</label>
                    <select class="form-control" id="chunk_size" name="chunk_size">
                        <option value="25">25 enregistrements par chunk (pour très gros fichiers)</option>
                        <option value="50" selected>50 enregistrements par chunk (recommandé)</option>
                        <option value="100">100 enregistrements par chunk</option>
                        <option value="200">200 enregistrements par chunk (pour petits fichiers)</option>
                    </select>
                    <small class="form-text text-muted">Pour les très gros fichiers (90k+ enregistrements), utilisez 25-50 pour éviter les timeouts.</small>
                </div>

                <button type="submit" class="btn btn-danger" id="submitBtn">Importer les modules</button>
            </form>

            <!-- Progress Section -->
            <div id="progressSection" class="mt-4" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Progression de l'importation</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 25px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Total</h5>
                                        <p class="card-text h4" id="totalRecords">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Importés</h5>
                                        <p class="card-text h4" id="importedRecords">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Ignorés</h5>
                                        <p class="card-text h4" id="skippedRecords">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Erreurs</h5>
                                        <p class="card-text h4" id="errorRecords">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="mb-1">Traitement en cours... <span id="processedRecords">0</span> / <span id="totalRecordsText">0</span></p>
                            <p class="mb-0 text-muted">Veuillez ne pas fermer cette page pendant l'importation.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="mt-3" style="display:none;">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <span class="ms-2">Initialisation de l'importation...</span>
            </div>

            <!-- Error Messages -->
            <div id="errorMessages" class="mt-3" style="display:none;">
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Erreurs d'importation:</h4>
                    <ul id="errorList"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let importInProgress = false;
    let continueUrl = null;
    let retryCount = 0;
    let maxRetries = 3;

    document.getElementById('importForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (importInProgress) {
            return;
        }

        const form = e.target;
        const formData = new FormData(form);
        const loadingIndicator = document.getElementById('loadingIndicator');
        const errorMessagesDiv = document.getElementById('errorMessages');
        const progressSection = document.getElementById('progressSection');
        const submitBtn = document.getElementById('submitBtn');

        // Reset UI
        resetUI();
        loadingIndicator.style.display = 'block';
        submitBtn.disabled = true;
        importInProgress = true;
        retryCount = 0;

        // Start import
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Initial response:', data);
            loadingIndicator.style.display = 'none';

            if (data.status === 'processing') {
                progressSection.style.display = 'block';

                // Build continue URL more reliably
                if (data.continue_url) {
                    continueUrl = data.continue_url;
                } else if (data.import_id) {
                    // Fallback: construct URL manually
                    continueUrl = window.location.origin + '/admin/student-modules-import/process-chunk/' + data.import_id;
                } else {
                    handleError({ message: 'URL de continuation manquante dans la réponse du serveur.' });
                    return;
                }

                console.log('Continue URL set to:', continueUrl);
                updateProgress(data);
                setTimeout(continueImport, 1000); // Start with 1 second delay
            } else if (data.status === 'completed') {
                handleImportComplete(data);
            } else {
                handleError(data);
            }
        })
        .catch(error => {
            console.error('Initial import error:', error);
            loadingIndicator.style.display = 'none';
            handleError({ message: 'Erreur de connexion au serveur: ' + error.message });
        });
    });

    function continueImport() {
        if (!continueUrl || !importInProgress) {
            console.log('Stopping import - no URL or not in progress');
            return;
        }

        if (retryCount >= maxRetries) {
            console.error('Max retries reached');
            handleError({ message: 'Nombre maximum de tentatives atteint. Veuillez réessayer.' });
            return;
        }

        console.log('Continuing import, retry count:', retryCount, 'URL:', continueUrl);

        fetch(continueUrl, {
            method: 'POST', // Use POST for consistency
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Continue response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Continue response data:', data);
            retryCount = 0; // Reset retry count on success

            if (data.status === 'processing') {
                updateProgress(data);
                // Update continue URL in case it changed
                if (data.continue_url) {
                    continueUrl = data.continue_url;
                }
                // Continue processing with a 2-second delay to prevent overwhelming the server
                setTimeout(continueImport, 2000);
            } else if (data.status === 'completed') {
                handleImportComplete(data);
            } else {
                handleError(data);
            }
        })
        .catch(error => {
            console.error('Continue import error:', error);
            retryCount++;

            if (retryCount < maxRetries) {
                console.log(`Retrying in 5 seconds (attempt ${retryCount}/${maxRetries})`);
                setTimeout(continueImport, 5000); // Wait 5 seconds before retry
            } else {
                handleError({ message: 'Erreur lors du traitement: ' + error.message });
            }
        });
    }

    function updateProgress(data) {
        const progress = Math.round(data.progress || 0);
        const progressBar = document.getElementById('progressBar');

        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        progressBar.textContent = progress + '%';

        document.getElementById('totalRecords').textContent = data.total || 0;
        document.getElementById('totalRecordsText').textContent = data.total || 0;
        document.getElementById('processedRecords').textContent = data.processed || 0;
        document.getElementById('importedRecords').textContent = data.imported || 0;
        document.getElementById('skippedRecords').textContent = data.skipped || 0;
        document.getElementById('errorRecords').textContent = data.errors || 0;
    }

    function handleImportComplete(data) {
        importInProgress = false;

        // Update final progress
        updateProgress({
            progress: 100,
            total: data.stats.total,
            processed: data.stats.total,
            imported: data.stats.imported,
            skipped: data.stats.skipped,
            errors: data.stats.errors
        });

        // Update progress bar to success
        const progressBar = document.getElementById('progressBar');
        progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
        progressBar.classList.add('bg-success');

        // Show success message and redirect
        setTimeout(() => {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                alert('Importation terminée avec succès!');
                resetForm();
            }
        }, 2000);
    }

    function handleError(data) {
        importInProgress = false;
        const errorMessagesDiv = document.getElementById('errorMessages');
        const errorList = document.getElementById('errorList');

        errorMessagesDiv.style.display = 'block';
        errorList.innerHTML = '';

        let displayMessage = data.message || 'Une erreur inattendue est survenue.';

        if (data.errors) {
            if (Array.isArray(data.errors)) {
                data.errors.forEach(err => {
                    const li = document.createElement('li');
                    li.textContent = `Ligne ${err.line || 'N/A'}: Code ${err.code || 'N/A'} - ${err.message}`;
                    errorList.appendChild(li);
                });
            } else {
                for (const key in data.errors) {
                    data.errors[key].forEach(message => {
                        const li = document.createElement('li');
                        li.textContent = message;
                        errorList.appendChild(li);
                    });
                }
            }
        } else {
            const li = document.createElement('li');
            li.textContent = displayMessage;
            errorList.appendChild(li);
        }

        resetForm();
    }

    function resetUI() {
        document.getElementById('errorMessages').style.display = 'none';
        document.getElementById('progressSection').style.display = 'none';
        document.getElementById('errorList').innerHTML = '';
    }

    function resetForm() {
        document.getElementById('submitBtn').disabled = false;
        importInProgress = false;
        continueUrl = null;
    }

    // Prevent page unload during import
    window.addEventListener('beforeunload', function(e) {
        if (importInProgress) {
            e.preventDefault();
            e.returnValue = 'Une importation est en cours. Êtes-vous sûr de vouloir quitter cette page?';
        }
    });
</script>
@endsection
