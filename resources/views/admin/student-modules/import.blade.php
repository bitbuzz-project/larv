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
                <button type="submit" class="btn btn-danger">Importer les modules</button>
            </form>

            <div id="loadingIndicator" class="mt-3" style="display:none;">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <span class="ms-2">Importation en cours, cela peut prendre un certain temps pour les gros fichiers...</span>
            </div>

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
    document.getElementById('importForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const loadingIndicator = document.getElementById('loadingIndicator');
        const errorMessagesDiv = document.getElementById('errorMessages');
        const errorList = document.getElementById('errorList');

        // Reset previous messages
        errorMessagesDiv.style.display = 'none';
        errorList.innerHTML = '';
        loadingIndicator.style.display = 'block';
        form.querySelector('button[type="submit"]').setAttribute('disabled', 'disabled');

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            // Always try to parse JSON, even if response is not ok
            return response.json().then(data => {
                if (!response.ok) {
                    // If response is not OK, reject the promise with the parsed data and status
                    return Promise.reject({ status: response.status, data: data });
                }
                return data;
            }).catch(() => {
                // If parsing JSON fails for a non-OK response, return a generic error
                return Promise.reject({ status: response.status, data: { message: 'Réponse du serveur non valide ou vide.' } });
            });
        })
        .then(data => {
            loadingIndicator.style.display = 'none';
            form.querySelector('button[type="submit"]').removeAttribute('disabled');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                // Fallback for success, should redirect to results page
                alert(data.message || 'Importation terminée avec succès.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingIndicator.style.display = 'none';
            form.querySelector('button[type="submit"]').removeAttribute('disabled');

            errorMessagesDiv.style.display = 'block';
            let displayMessage = 'Une erreur inattendue est survenue lors de l\'importation.';

            if (error.data && error.data.message) {
                displayMessage = error.data.message;
            }

            if (error.data && error.data.errors) {
                if (Array.isArray(error.data.errors)) {
                    // Handle custom error array from controller (e.g., validation errors with line/code)
                    error.data.errors.forEach(err => {
                        const li = document.createElement('li');
                        li.textContent = `Ligne ${err.line || 'N/A'}: Code ${err.code || 'N/A'} - ${err.message}`;
                        errorList.appendChild(li);
                    });
                } else {
                    // Handle Laravel's default validation errors (object of arrays)
                    for (const key in error.data.errors) {
                        error.data.errors[key].forEach(message => {
                            const li = document.createElement('li');
                            li.textContent = message;
                            errorList.appendChild(li);
                        });
                    }
                }
            } else {
                // For general errors or if 'errors' array is not present, add the main message
                const li = document.createElement('li');
                li.textContent = displayMessage;
                errorList.appendChild(li);
            }
        });
    });
</script>
@endsection
