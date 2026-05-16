<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EvalCode') }} - Authentication</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">


    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts and Styles -->
    @viteReactRefresh
    @vite(['resources/sass/app.scss', 'resources/js/app.jsx'])

    <style>
        .auth-bg {
            background: linear-gradient(135deg, var(--bs-primary) 0%, #3a0000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        /* Floating Glowing Orbs for Elegance */
        .auth-bg::before,
        .auth-bg::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            animation: float 10s infinite alternate ease-in-out;
        }

        .auth-bg::before {
            width: 400px;
            height: 400px;
            background: rgba(255, 193, 7, 0.12);
            /* Subtle Gold */
            top: -100px;
            left: -100px;
        }

        .auth-bg::after {
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.08);
            /* Subtle White */
            bottom: -150px;
            right: -100px;
            animation-delay: -5s;
        }

        @keyframes float {
            0% {
                transform: translateY(0) scale(1);
            }

            100% {
                transform: translateY(40px) scale(1.05);
            }
        }

        /* Subtle grid pattern overlay */
        .auth-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.15) 1px, transparent 1px);
            background-size: 30px 30px;
            z-index: -1;
            opacity: 0.6;
        }

        /* Code Decorations */
        .code-decor {
            position: absolute;
            font-family: 'Courier New', Courier, monospace;
            color: rgba(255, 255, 255, 0.08);
            z-index: 0;
            white-space: pre;
            user-select: none;
            pointer-events: none;
            line-height: 1.4;
        }

        .code-1 {
            top: 5%;
            left: 4%;
            font-size: 1.1rem;
        }

        .code-5 {
            top: 35%;
            left: 2%;
            font-size: 1.35rem;
        }

        .code-10 {
            bottom: 35%;
            left: 4%;
            font-size: 1.15rem;
        }

        .code-3 {
            bottom: 5%;
            left: 8%;
            font-size: 0.9rem;
        }

        .code-4 {
            top: 8%;
            right: 6%;
            font-size: 0.85rem;
        }

        .code-9 {
            top: 32%;
            right: 3%;
            font-size: 0.95rem;
        }

        .code-6 {
            bottom: 38%;
            right: 2%;
            font-size: 1.05rem;
        }

        .code-2 {
            bottom: 8%;
            right: 5%;
            font-size: 1.4rem;
        }

        .code-7 {
            top: 2%;
            left: 40%;
            font-size: 0.8rem;
        }

        .code-8 {
            bottom: 2%;
            right: 40%;
            font-size: 1.2rem;
        }
    </style>
</head>

<body class="auth-bg">
    <div class="auth-pattern"></div>

    <!-- Code Background Decorations -->
    <div class="code-decor code-1">
        while (alive) {
        eat();
        code();
        sleep();
        }
    </div>
    <div class="code-decor code-2">
        if (coffee.isEmpty()) {
        developer.refill();
        }
    </div>
    <div class="code-decor code-3">
        throw new Error("Works on my machine 🤷‍♂️");
    </div>
    <div class="code-decor code-4">
        // TODO: Fix this later
        // (Written 3 years ago)
    </div>
    <div class="code-decor code-5">
        [1, 2, 3].map(parseInt);
        // Returns: [1, NaN, NaN]
    </div>
    <div class="code-decor code-6">
        !false // It's funny because it's true
    </div>
    <div class="code-decor code-7">
        try {
        // do something risky
        } catch (e) {
        // ignore
        }
    </div>
    <div class="code-decor code-8">
        git commit -m "minor tweaks"
        // *changed 124 files*
    </div>
    <div class="code-decor code-9">
        let random = 4;
        // chosen by fair dice roll
    </div>
    <div class="code-decor code-10">
        console.log("here 1");
        console.log("here 2");
    </div>

    <div id="app" class="w-100 position-relative" style="z-index: 2;">
        <main>
            @yield('content')
        </main>
    </div>
</body>

</html>
