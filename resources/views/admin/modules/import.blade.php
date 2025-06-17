@extends('layouts.admin')

@section('title', 'Importer des Modules')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i>📥</i> Importer des Modules</h2>
            <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">
                <i>⬅️</i> Retour à la liste
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <div class="card-body text-white">
                        <h3 id="current-modules">{{ \App\Models\Module::count() }}</h3>
                        <p class="mb-0">Modules Actuels</p>
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
