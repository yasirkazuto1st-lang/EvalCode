<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EvalCode') }} - @yield('title', 'Dashboard')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">


    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts and Styles -->
    @viteReactRefresh
    @vite(['resources/sass/app.scss', 'resources/js/app.jsx'])

    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styling */
        #sidebar-wrapper {
            width: 250px;
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, var(--bs-primary) 0%, #3a0000 100%);
            /* Premium Gradient */
            transition: all 0.3s;
            color: white;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        #sidebar-wrapper.collapsed {
            width: 70px;
        }

        #sidebar-wrapper.collapsed .sidebar-text,
        #sidebar-wrapper.collapsed .sidebar-brand-content {
            display: none;
        }

        #sidebar-wrapper.collapsed .sidebar-heading {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .sidebar-heading {
            padding: 1.5rem 1.5rem;
            font-size: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-brand-content {
            display: flex;
            align-items: center;
        }

        .sidebar .list-group-item {
            color: rgba(255, 255, 255, 0.8);
            border: none;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .sidebar .list-group-item:hover,
        .sidebar .list-group-item.active {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            border-left: 4px solid #ffc107;
        }

        .sidebar .list-group-item.btn-logout {
            background-color: #dc3545 !important;
            color: white !important;
            border-left: none !important;
        }

        .sidebar .list-group-item.btn-logout:hover {
            background-color: #bb2d3b !important;
            color: white !important;
            border-left: none !important;
        }

        .sidebar-icon {
            width: 24px;
            font-size: 1.2rem;
            text-align: center;
            margin-right: 10px;
        }

        /* Page Content Styling */
        #page-content-wrapper {
            width: 100%;
            transition: all 0.3s;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        .top-navbar {
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            z-index: 999;
            flex-shrink: 0;
        }

        .main-content {
            flex-grow: 1;
            overflow-y: auto;
        }

        /* Sidebar overlay on mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Mobile toggle button shown inside top-navbar on mobile */
        .mobile-toggle {
            display: none;
        }

        @media (max-width: 991.98px) {

            /* Keep sidebar visible and toggle button shown */
            #sidebar-wrapper {
                position: static !important;
                left: 0 !important;
            }

            #sidebarToggle {
                display: block !important;
            }
        }


        @media (max-width: 575.98px) {
            .top-navbar {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .top-navbar h5 {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper" class="sidebar">
            <div class="sidebar-heading">
                <div class="sidebar-brand-content fw-bold">
                    <i class="bi bi-braces"></i> <span class="sidebar-text ms-2">EvalCode</span>
                </div>
                <button class="btn text-white p-0 m-0 border-0" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>
            <div class="list-group list-group-flush mt-3 flex-grow-1 d-flex flex-column">
                @yield('sidebar-menu')

                @php
                    $isPengawas = request()->is('pengawas*');
                    $pwdRoute = $isPengawas ? route('pengawas.password') : route('admin.password');
                    $isPwdActive = request()->routeIs('pengawas.password') || request()->routeIs('admin.password');
                @endphp
                <a href="{{ $pwdRoute }}"
                    class="list-group-item list-group-item-action bg-transparent {{ $isPwdActive ? 'active' : '' }}">
                    <i class="bi bi-key sidebar-icon"></i> <span class="sidebar-text">Ganti Password</span>
                </a>

                <div class="mt-auto">
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="list-group-item list-group-item-action btn-logout rounded-0">
                        <i class="bi bi-box-arrow-right sidebar-icon"></i> <span
                            class="sidebar-text fw-bold">Logout</span>
                    </a>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Top Navbar (White) -->
            <nav class="navbar navbar-expand-lg navbar-light top-navbar py-2 px-4">
                <div class="w-100 d-flex justify-content-end align-items-center">
                    <!-- User Info di Kanan -->
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end d-none d-sm-block">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">
                                {{ Auth::user()->name ?? 'Administrator' }}</div>
                            <span class="badge bg-light text-secondary border mt-1"
                                style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.5px;">{{ Auth::user()->role ?? 'Role' }}</span>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm text-white fw-bold border border-2 border-white"
                            style="width: 42px; height: 42px; background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%);">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <div class="container-fluid p-4 main-content">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById("sidebar-wrapper");
            const overlay = document.getElementById("sidebarOverlay");

            // Desktop toggle (collapse sidebar)
            const desktopToggle = document.getElementById("sidebarToggle");
            desktopToggle.addEventListener("click", function() {
                sidebar.classList.toggle("collapsed");
            });

            // Mobile toggle (slide in/out)
            const mobileToggle = document.getElementById("mobileSidebarToggle");
            mobileToggle.addEventListener("click", function() {
                sidebar.classList.toggle("show");
                overlay.classList.toggle("show");
            });

            // Close sidebar when clicking overlay
            overlay.addEventListener("click", function() {
                sidebar.classList.remove("show");
                overlay.classList.remove("show");
            });
        });
    </script>
    @yield('scripts')

    @auth
        <script>
            setInterval(function() {
                fetch('{{ route('check.session') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.status === 401) {
                            response.json().then(data => {
                                alert(data.error ||
                                    'Akun Anda telah login di perangkat lain. Sesi ini telah diakhiri otomatis.'
                                );
                                window.location.href = '{{ route('login') }}';
                            });
                        }
                    })
                    .catch(err => console.error('Session check error:', err));
            }, 5000); // Cek setiap 5 detik
        </script>
    @endauth
</body>

</html>
