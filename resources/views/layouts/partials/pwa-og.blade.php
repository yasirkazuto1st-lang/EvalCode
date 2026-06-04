<!-- PWA Settings -->
<meta name="theme-color" content="#800000">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="EvalCode">
<link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
<link rel="manifest" href="{{ asset('manifest.json') }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="EvalCode - Platform Ujian Coding">
<meta property="og:description" content="Platform Ujian Pemrograman Praktis, Otomatis, dan Aman Universitas Sulawesi Barat.">
<meta property="og:image" content="{{ asset('images/og-image.png') }}">
<meta property="og:site_name" content="EvalCode">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="EvalCode - Platform Ujian Coding">
<meta name="twitter:description" content="Platform Ujian Pemrograman Praktis, Otomatis, dan Aman Universitas Sulawesi Barat.">
<meta name="twitter:image" content="{{ asset('images/og-image.png') }}">

<!-- PWA Service Worker Registration -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((reg) => {
                    console.log('Service Worker registered successfully. Scope:', reg.scope);
                })
                .catch((err) => {
                    console.error('Service Worker registration failed:', err);
                });
        });
    }
</script>
