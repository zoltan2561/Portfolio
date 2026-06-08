<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? ($lang === 'en' ? 'Statistics Login' : 'Statisztika Belepes') }}</title>
    <meta name="description" content="{{ $meta['description'] ?? '' }}">
    <meta name="robots" content="{{ $robotsMeta ?? 'noindex, nofollow' }}">
    @isset($canonicalUrl)
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endisset
    <link rel="stylesheet" href="{{ $assets['css'] ?? asset('Style.css') }}">
</head>

<body>
    <canvas id="matrix"></canvas>

    <div class="statistics-shell">
        @yield('content')
    </div>

    <script>
        const typewriterLines = [];
        const preloaderLines = [];
    </script>
    <script src="{{ $assets['js'] ?? asset('script.js') }}"></script>
</body>

</html>
