<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        /* Fixed sidebar styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            flex-shrink: 0;
            transition: transform 0.3s ease-in-out;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            overflow-x: hidden;
            max-height: 100vh;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }

        /* Custom scrollbar for webkit browsers */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
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
            max-height: 100vh;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                max-height: 100vh;
                transform: translateX(-100%);
                z-index: 10;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content {
                padding: 15px;
            }

            /* Mobile overlay */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 9;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
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

        /* Add padding to sidebar content container */
        .sidebar-content {
            padding-bottom: 30px;
        }
    </style>
</head>
<body>
<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="wrapper">
    <div class="sidebar" id="sidebar">
        <button class="close-sidebar" id="closeSidebar">&times;</button>

        <div class="sidebar-content">
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
                    <a href="{{ route('admin.students.index') }}" class="btn btn-link text-start {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                        <i>👥</i> Liste des étudiants
                    </a>
                    <a href="{{ route('admin.students.create') }}" class="btn btn-link text-start">
                        <i>➕</i> Ajouter étudiant
                    </a>
                    <a href="{{ route('admin.students.import') }}" class="btn btn-link text-start {{ request()->routeIs('admin.students.import*') ? 'active' : '' }}">
                        <i>📥</i> Importer JSON Étudiants
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Gestion des notes</div>
                    <a href="{{ route('admin.notes.import') }}" class="btn btn-link text-start {{ request()->routeIs('admin.notes.*') ? 'active' : '' }}">
                        <i>📊</i> Importer Notes (ODS/CSV)
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Gestion des modules</div>
                    <a href="{{ route('admin.modules.index') }}" class="btn btn-link text-start {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                        <i>📚</i> Liste des modules
                    </a>
                    <a href="{{ route('admin.modules.create') }}" class="btn btn-link text-start">
                        <i>➕</i> Ajouter module
                    </a>
                    <a href="{{ route('admin.modules.import') }}" class="btn btn-link text-start {{ request()->routeIs('admin.modules.import*') ? 'active' : '' }}">
                        <i>📥</i> Importer JSON Modules
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Inscriptions modules</div>
                    <a href="{{ route('admin.student-modules.import') }}" class="btn btn-link text-start {{ request()->routeIs('admin.student-modules.import*') ? 'active' : '' }}">
                        <i>📥</i> Importer Inscriptions CSV
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
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-link text-start {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i>📈</i> Rapports Détaillés
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-link text-start">
                        <i>📊</i> Statistiques Avancées
                    </a>
                    <a href="{{ route('admin.reports.export-pdf') }}" class="btn btn-link text-start">
                        <i>📄</i> Exporter PDF
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
                    <a href="#" class="btn btn-link text-start">
                        <i>🔒</i> Sécurité
                    </a>
                    <a href="#" class="btn btn-link text-start">
                        <i>📋</i> Logs système
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Configuration</div>
                    <a href="#" class="btn btn-link text-start">
                        <i>🏫</i> Paramètres faculté
                    </a>
                    <a href="#" class="btn btn-link text-start">
                        <i>👨‍🎓</i> Gestion utilisateurs
                    </a>
                    <a href="#" class="btn btn-link text-start">
                        <i>🔧</i> Maintenance
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
    </div>

    <div class="content">
        <!-- Navbar for mobile -->
        <nav class="navbar navbar-dark bg-danger d-md-none">
            <div class="container-fluid">
                <button class="btn btn-outline-light" id="toggleSidebar">☰ Menu Admin</button>
                <span class="navbar-brand">@yield('title', 'Admin Dashboard')</span>
            </div>
        </nav>

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
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('toggleSidebar');
        const closeButton = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (toggleButton) {
            toggleButton.addEventListener('click', () => {
                sidebar.classList.add('show');
                overlay.classList.add('show');
            });
        }

        if (closeButton) {
            closeButton.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        // Close sidebar when clicking a link on mobile
        const sidebarLinks = sidebar.querySelectorAll('a, button');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });
        });
    });
</script>
</body>
</html>
