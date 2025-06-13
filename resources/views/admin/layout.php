<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            height: 100vh;
            overflow: hidden;
        }
        .wrapper {
            display: flex;
            flex-direction: row;
            height: 100%;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            flex-shrink: 0;
            transition: transform 0.3s ease-in-out;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            margin-bottom: 8px;
            display: block;
            padding: 12px 20px;
            border-radius: 8px;
            margin-left: 15px;
            margin-right: 15px;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.3);
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            background-color: #f8f9fa;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                transform: translateX(-100%);
                z-index: 10;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content {
                padding: 15px;
            }
        }
        .close-sidebar {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .close-sidebar {
                display: block;
            }
        }
        .admin-badge {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #dc3545;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
            margin: 10px 15px;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }
        .sidebar-section {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 20px;
            padding-top: 15px;
        }
        .sidebar-section-title {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 20px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Admin Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="close-sidebar" id="closeSidebar">&times;</button>

        <!-- Admin Logo/Header -->
        <div class="px-3 text-center py-3">
            <h5 class="mb-0">Admin Panel</h5>
        </div>

        <!-- Admin Badge -->
        <div class="admin-badge">
            🛡️ ADMINISTRATEUR
        </div>

        <!-- Admin Info Card -->
        <div class="card mx-3 mb-3" style="background: rgba(255, 255, 255, 0.1); border: none;">
            <div class="card-body text-center py-2">
                <h6 class="text-white mb-1">{{ auth()->user()->apoL_a03_prenom }} {{ auth()->user()->apoL_a02_nom }}</h6>
                <small class="text-light">Code: {{ auth()->user()->apoL_a01_code }}</small>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="d-grid gap-1 px-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-link text-start {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i>📊</i> Tableau de bord
            </a>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Gestion des étudiants</div>
                <a href="{{ route('admin.students.index') }}" class="btn btn-link text-start {{ request()->routeIs('admin.students.index') ? 'active' : '' }}">
                    <i>👥</i> Liste des étudiants
                </a>
                <a href="{{ route('admin.students.create') }}" class="btn btn-link text-start {{ request()->routeIs('admin.students.create') ? 'active' : '' }}">
                    <i>➕</i> Ajouter étudiant
                </a>
                <a href="{{ route('admin.students.import') }}" class="btn btn-link text-start {{ request()->routeIs('admin.students.import*') ? 'active' : '' }}">
                    <i>📥</i> Importer JSON
                </a>
                <a href="#" class="btn btn-link text-start">
                    <i>📋</i> Gérer les résultats
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Réclamations & Support</div>
                <a href="#" class="btn btn-link text-start">
                    <i>⚠️</i> Gestion des réclamations
                </a>
                <a href="#" class="btn btn-link text-start">
                    <i>🔔</i> Réclamations en attente
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Rapports & Statistiques</div>
                <a href="#" class="btn btn-link text-start">
                    <i>📈</i> Rapports
                </a>
                <a href="#" class="btn btn-link text-start">
                    <i>📊</i> Statistiques
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">Système</div>
                <a href="#" class="btn btn-link text-start">
                    <i>⚙️</i> Paramètres
                </a>
                <a href="#" class="btn btn-link text-start">
                    <i>💾</i> Sauvegarde
                </a>
            </div>
        </div>

        <!-- Logout Button -->
        <div class="px-3 mt-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-warning btn-block w-100">
                    <i>🚪</i> Se déconnecter
                </button>
            </form>
        </div>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Navbar for mobile -->
        <nav class="navbar navbar-dark bg-danger d-md-none">
            <div class="container-fluid">
                <button class="btn btn-outline-light" id="toggleSidebar">☰ Menu Admin</button>
                <span class="navbar-brand">@yield('title', 'Admin Dashboard')</span>
            </div>
        </nav>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('toggleSidebar');
    const closeButton = document.getElementById('closeSidebar');

    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            sidebar.classList.add('show');
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', () => {
            sidebar.classList.remove('show');
        });
    }
</script>
</body>
</html>
