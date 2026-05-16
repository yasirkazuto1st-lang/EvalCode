<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Autograder Unsulbar') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- Scripts and Styles -->
    @viteReactRefresh
    @vite(['resources/sass/app.scss', 'resources/js/app.jsx'])
</head>

<body class="bg-light" style="height: 100vh; overflow: hidden;">
    <div id="app" class="d-flex flex-column h-100">
        <nav class="navbar navbar-expand-md navbar-dark navbar-unsulbar shadow-sm py-2">
            <div class="container">
                <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="{{ url('/') }}">
                    <i class="bi bi-braces fs-4 me-2"></i> EvalCode
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar (Empty, we push everything to right) -->
                    <ul class="navbar-nav me-auto">
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item">
                            <a class="nav-link fw-semibold px-3 text-white" href="{{ route('dashboard') }}">Ujian</a>
                        </li>

                        <li class="nav-item dropdown ms-3 border-start border-light ps-3">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle fw-bold text-white" href="#"
                                role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                v-pre>
                                {{ Auth::check() ? Auth::user()->name : 'Ahmad Fauzi' }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end shadow border-0 mt-2"
                                aria-labelledby="navbarDropdown">
                                <!-- Info Nama di Dropdown -->
                                <div class="dropdown-header text-dark fw-bold">
                                    {{ Auth::check() ? Auth::user()->name : 'Ahmad Fauzi' }}
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                    data-bs-target="#mahasiswaPasswordModal">
                                    <i class="bi bi-key me-2"></i> Ganti Password
                                </a>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4 flex-grow-1" style="overflow-y: auto;">
            @yield('content')
        </main>
    </div>

    <!-- Modal Ganti Password Mahasiswa -->
    <div class="modal fade" id="mahasiswaPasswordModal" tabindex="-1" aria-labelledby="mahasiswaPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="mahasiswaPasswordModalLabel"><i
                            class="bi bi-key text-unsulbar me-2"></i>Ganti Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Password Sekarang</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" required minlength="8">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-semibold">Ulang Password Baru</label>
                            <input type="password" class="form-control" name="new_password_confirmation" required
                                minlength="8">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-unsulbar fw-semibold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
