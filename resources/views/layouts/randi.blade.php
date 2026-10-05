<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="referrer" content="no-referrer">
    <meta name="theme-color" content="#fbf5ef">
    <meta name="description" content="Egy kis meghívó, csak neked.">
    <meta property="og:title" content="Van egy kérdésem… 💌">
    <meta property="og:description" content="Egy kis meghívó, csak neked.">
    <meta property="og:type" content="website">
    <title>Van egy kérdésem… 💌</title>
    <link rel="icon" href="{{ asset('ico.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/randi/randi.css') }}?v={{ filemtime(public_path('assets/randi/randi.css')) }}">
    <script src="{{ asset('assets/randi/randi.js') }}?v={{ filemtime(public_path('assets/randi/randi.js')) }}" defer></script>
</head>
<body class="randi @yield('body-class')">
    <main class="randi-shell">
        <div class="randi-wordmark" aria-hidden="true"><span>egy kis</span> meghívó <span class="wordmark-heart">♡</span></div>
        @yield('content')
        <footer class="randi-footer">Két ember. Egy jó program. <span aria-hidden="true">✧</span></footer>
    </main>
</body>
</html>
