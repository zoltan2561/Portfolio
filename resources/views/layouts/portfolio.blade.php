<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] }}</title>
    <meta name="description" content="{{ $meta['description'] }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="hu" href="{{ $pageName === 'home' ? $homeHuUrl : ($pageName === 'skills' ? $skillsHuUrl : $statisticsHuUrl) }}">
    <link rel="alternate" hreflang="en" href="{{ $pageName === 'home' ? $homeEnUrl : ($pageName === 'skills' ? $skillsEnUrl : $statisticsEnUrl) }}">
    <link rel="alternate" hreflang="x-default" href="{{ $pageName === 'home' ? $homeHuUrl : ($pageName === 'skills' ? $skillsHuUrl : $statisticsHuUrl) }}">

    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $ogImageAlt }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="{{ $ogImageWidth }}">
    <meta property="og:image:height" content="{{ $ogImageHeight }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:site_name" content="{{ config('portfolio.site_name') }}">
    <meta property="og:locale" content="{{ $lang === 'hu' ? 'hu_HU' : 'en_US' }}">
    <meta property="og:logo" content="{{ asset('images/logo.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $meta['title'] }}">
    <meta name="twitter:description" content="{{ $meta['description'] }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="{{ $ogImageAlt }}">

    <link rel="icon" href="{{ $favicon }}" type="image/png">
    <script type="application/ld+json">{!! $schemaJson !!}</script>
    <link rel="stylesheet" href="{{ $assets['css'] }}">
</head>

<body>
    @if ($preloaderLines)
        <div id="preloader">
            <pre id="preloader-text"></pre>
        </div>
    @endif

    <canvas id="matrix"></canvas>

    <div class="language-switch">
        <a href="{{ $pageName === 'home' ? $homeHuUrl : ($pageName === 'skills' ? $skillsHuUrl : $statisticsHuUrl) }}" class="{{ $lang === 'hu' ? 'active' : '' }}">HUN</a> |
        <a href="{{ $pageName === 'home' ? $homeEnUrl : ($pageName === 'skills' ? $skillsEnUrl : $statisticsEnUrl) }}" class="{{ $lang === 'en' ? 'active' : '' }}">ENG</a>
    </div>

    <div class="nav-container">
        <div class="hamburger" onclick="toggleMenu()">☰</div>
        <nav id="main-nav">
            <a href="{{ $links['about'] }}">{{ $nav['about'] }}</a>
            <a href="{{ $links['services'] }}">{{ $nav['services'] }}</a>
            <a href="{{ $links['projects'] }}">{{ $nav['projects'] }}</a>
            <a href="{{ $skillsUrl }}" class="{{ $pageName === 'skills' ? 'active' : '' }}">{{ $nav['skills'] }}</a>
            <a href="{{ $links['contact'] }}">{{ $nav['contact'] }}</a>
        </nav>
    </div>

    <div class="typewriter-container fade-in">
        <pre id="typewriter"></pre>
    </div>

    @yield('content')

    <script>
        const typewriterLines = @json($typewriterLines);
        const preloaderLines = @json($preloaderLines);
    </script>
    <script src="{{ $assets['js'] }}"></script>
</body>

</html>
