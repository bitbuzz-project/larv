@extends('layouts.admin')

@section('title', 'Gestion des Modules')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i>📚</i> Gestion des Modules</h2>
    <div class="btn-group">
        <a href="{{ route('admin.modules.create') }}" class="btn btn-danger">
            <i>➕</i> Ajouter Module
        </a>
        <a href="{{ route('admin.modules.import') }}" class="btn btn-outline-primary">
            <i>📥</i> Importer JSON
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i>🔍</i></span>
                    <input type="text" class="form-control" name="search"
                           placeholder="Rechercher par code, libellé ou composante..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-control" name="status">
                    <option value="">-- Tous les statuts --</option>
                    <option value="A" {{ request('status') == 'A' ? 'selected' : '' }}>Actif</option>
                    <option value="I" {{ request('status') == 'I' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                    @if(request('search') || request('status') || request('component'))
                        <a href="{{ route('admin.modules.index') }}" class="btn btn-outline-secondary">Effacer</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #17a2b8, #138496);">
            <div class="card-body text-white">
                <h3>{{ $modules->total() }}</h3>
                <p class="mb-0">Total Modules</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="card-body text-white">
                <h3>{{ $modules->count() }}</h3>
                <p class="mb-0">Affichés</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
            <div class="card-body text-white">
                <h3>{{ $modules->where('eta_elp', 'A')->count() }}</h3>
                <p class="mb-0">Actifs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #6f42c1, #5a67d8);">
            <div class="card-body text-white">
                <h3>{{ $modules->lastPage() }}</h3>
                <p class="mb-0">Pages</p>
            </div>
        </div>
    </div>
</div>

<!-- Modules Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i>📋</i> Liste des Modules</h5>
    </div>
    <div class="card-body p-0">
        @if($modules->isEmpty())
            <div class="text-center py-5">
                <i style="font-size: 3rem; color: #6c757d;">📚</i>
                <h5 class="mt-3 text-muted">Aucun module trouvé</h5>
                @if(request('search'))
                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                @else
                    <p class="text-muted">La base de données des modules semble vide</p>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Code Module</th>
                            <th>Libellé</th>
                            <th>Composante</th>
                            <th>ECTS</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ $module->cod_elp ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ Str::limit($module->lib_elp ?? 'Sans titre', 50) }}</strong>
                                        @if($module->lib_elp_arb)
                                            <br><small class="text-muted">{{ Str::limit($module->lib_elp_arb, 40) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($module->cod_cmp)
                                        <span class="badge bg-secondary">{{ $module->cod_cmp }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($module->nbr_pnt_ect_elp)
                                        <span class="badge bg-info">{{ $module->nbr_pnt_ect_elp }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($module->eta_elp == 'A')
                                        <span class="badge bg-success">Actif</span>
                                    @elseif($module->eta_elp == 'I')
                                        <span class="badge bg-danger">Inactif</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $module->eta_elp ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.modules.show', $module) }}"
                                           class="btn btn-outline-info" title="Voir détails">
                                            <i>👁️</i>
                                        </a>
                                        <a href="{{ route('admin.modules.edit', $module) }}"
                                           class="btn btn-outline-primary" title="Modifier">
                                            <i>✏️</i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.modules.destroy', $module) }}"
                                              style="display: inline;"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce module?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                                <i>🗑️</i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if($modules->hasPages())
    <div class="mt-4">
        {{ $modules->links() }}
    </div>
@endif
@endsection
