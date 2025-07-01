<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم - الطالب')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
        }

        /* Fixed sidebar styles */
        .sidebar {
            min-height: calc(100vh - 56px);
            max-height: calc(100vh - 56px);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            overflow-y: auto;
            overflow-x: hidden;
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
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 5px 0;
            padding: 10px 15px;
            border-radius: 8px;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .main-content {
            padding: 20px;
            min-height: calc(100vh - 56px);
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                min-height: 100vh;
                max-height: 100vh;
                position: fixed;
                top: 0;
                left: 0;
                width: 280px;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                padding: 15px;
            }

            /* Overlay for mobile */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1049;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        .nav-section {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 15px;
            padding-top: 10px;
        }

        /* Add some padding to the navigation container */
        .sidebar-nav-container {
            padding-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('student.dashboard') }}">
                🎓 كلية العلوم القانونية والسياسية
            </a>
            <button class="navbar-toggler d-lg-none" type="button" id="toggleSidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            👤 {{ auth()->user()->full_name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('student.profile.show') }}">الملف الشخصي</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">تسجيل الخروج</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3" id="sidebar">
                    <div class="sidebar-nav-container">
                        <!-- User Info -->
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <span style="font-size: 1.5rem;">{{ auth()->user()->initials }}</span>
                            </div>
                            <h6 class="mt-2 mb-0">{{ auth()->user()->full_name }}</h6>
                            <small class="opacity-75">{{ auth()->user()->apoL_a01_code }}</small>
                        </div>

                        <nav class="nav flex-column">
                            <!-- Dashboard -->
                            <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                                📊 لوحة التحكم
                            </a>

                            <!-- Modules Section -->
                            <div class="nav-section mt-3 mb-2">
                                <small class="text-light opacity-75 px-3">المواد الدراسية</small>
                            </div>
                            <a href="{{ route('student.modules.current-session') }}" class="nav-link {{ request()->routeIs('student.modules.current-session') ? 'active' : '' }}">
                                📖 المواد الحالية
                            </a>
                            <a href="{{ route('student.modules.index') }}" class="nav-link {{ request()->routeIs('student.modules.index') ? 'active' : '' }}">
                                📋 جميع المواد
                            </a>

                            <!-- Academic Situation -->
                            <div class="nav-section mt-3 mb-2">
                                <small class="text-light opacity-75 px-3">الوضعية الأكاديمية</small>
                            </div>
                            <a href="{{ route('student.situation-pedagogique.index') }}" class="nav-link {{ request()->routeIs('student.situation-pedagogique.*') ? 'active' : '' }}">
                                📚 الوضعية البيداغوجية
                            </a>
                            <a href="{{ route('student.notes.index') }}" class="nav-link {{ request()->routeIs('student.notes.*') ? 'active' : '' }}">
                                📊 النتائج والنقط
                            </a>

                            <!-- Other Services -->
                            <div class="nav-section mt-3 mb-2">
                                <small class="text-light opacity-75 px-3">خدمات أخرى</small>
                            </div>
                            <a href="#" class="nav-link">
                                ⚠️ الشكاوى
                            </a>
                            <a href="#" class="nav-link">
                                📧 المراسلات
                            </a>
                            <a href="#" class="nav-link">
                                📄 الوثائق
                            </a>

                            <!-- Profile Section -->
                            <div class="nav-section mt-3 mb-2">
                                <small class="text-light opacity-75 px-3">الملف الشخصي</small>
                            </div>
                            <a href="{{ route('student.profile.show') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
                                👤 الملف الشخصي
                            </a>
                            <a href="{{ route('student.profile.change-password') }}" class="nav-link">
                                🔒 تغيير كلمة المرور
                            </a>

                            <!-- System -->
                            <div class="nav-section mt-3 mb-2">
                                <small class="text-light opacity-75 px-3">النظام</small>
                            </div>
                            <a href="#" class="nav-link">
                                ⚙️ الإعدادات
                            </a>
                            <a href="#" class="nav-link">
                                💬 المساعدة
                            </a>

                            <!-- Logout -->
                            <div class="nav-section mt-3 mb-2"></div>
                            <form method="POST" action="{{ route('logout') }}" class="px-3">
                                @csrf
                                <button type="submit" class="btn btn-outline-light w-100">
                                    🚪 تسجيل الخروج
                                </button>
                            </form>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
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
        </div>
    </div>

    <script>
        // Mobile sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggleBtn && sidebar && overlay) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.add('show');
                    overlay.classList.add('show');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });

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
            }
        });
    </script>
</body>
</html>
