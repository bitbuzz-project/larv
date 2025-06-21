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
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            👤 {{ auth()->user()->full_name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">الملف الشخصي</a></li>
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

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <span style="font-size: 1.5rem;">{{ auth()->user()->initials }}</span>
                        </div>
                        <h6 class="mt-2 mb-0">{{ auth()->user()->full_name }}</h6>
                        <small class="opacity-75">{{ auth()->user()->apoL_a01_code }}</small>
                    </div>

                    <nav class="nav flex-column">
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
                            <a href="{{ route('student.situation-pedagogique.index') }}" class="nav-link {{ request()->routeIs('student.situation-pedagogique.*') ? 'active' : '' }}">
        📚 الوضعية البيداغوجية
    </a>
                    <a href="{{ route('student.notes.index') }}" class="nav-link {{ request()->routeIs('student.notes.*') ? 'active' : '' }}">
    📊 النتائج والنقط
</a>
                        <a href="#" class="nav-link">
                            📚 المواد
                        </a>
                        <a href="#" class="nav-link">
                            ⚠️ الشكاوى
                        </a>
                    <a href="{{ route('student.profile.show') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
        👤 الملف الشخصي
    </a>
                    </nav>
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
</body>
</html>
