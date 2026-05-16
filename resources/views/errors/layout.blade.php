<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EvalCode') }} - @yield('title', 'Error')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts and Styles -->
    @viteReactRefresh
    @vite(['resources/sass/app.scss', 'resources/js/app.jsx'])

    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #212529;
            margin: 0;
            padding: 1rem;
        }

        .error-card {
            background-color: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            max-width: 440px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .error-icon {
            font-size: 2.5rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .error-code {
            font-size: 3rem;
            font-weight: 700;
            color: var(--bs-primary, #800000);
            line-height: 1;
            margin-bottom: 0.75rem;
        }

        .error-title {
            font-size: 1.35rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.5rem;
        }

        .error-message {
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        .btn-clean {
            background-color: var(--bs-primary, #800000);
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 0.65rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            transition: opacity 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-clean:hover {
            opacity: 0.9;
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center px-3">
        <div class="error-card">
            @yield('error-content')

            @php
                $homeRoute = url('/');
                if (\Illuminate\Support\Facades\Auth::check()) {
                    $role = strtolower(\Illuminate\Support\Facades\Auth::user()->role);
                    if ($role == 'admin') {
                        $homeRoute = route('admin.dashboard');
                    } elseif ($role == 'pengawas') {
                        $homeRoute = route('pengawas.dashboard');
                    } elseif ($role == 'mahasiswa') {
                        $homeRoute = route('mahasiswa.dashboard');
                    }
                }
            @endphp

            <a href="{{ $homeRoute }}" class="btn-clean">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</body>

</html>
