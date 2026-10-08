<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] }}</title>
    <meta name="description" content="{{ $meta['description'] }}">
    <meta name="robots" content="{{ $robotsMeta }}">
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
    @if (in_array($pageName, ['home', 'skills'], true))
        <link rel="stylesheet" href="{{ $assets['portfolioCss'] }}">
    @endif
    @if (in_array($pageName, ['home', 'skills'], true))
        <noscript><style>
            @media (max-width: 760px) {
                .portfolio-v2 .nav-container { position: relative; }
                .portfolio-v2 #main-nav { display: flex; position: static; flex-basis: 100%; }
                .portfolio-v2 .hamburger { display: none; }
                .portfolio-v2 main { padding-top: 0; }
            }
        </style></noscript>
    @endif
</head>

<body class="{{ in_array($pageName, ['home', 'skills'], true) ? 'portfolio-v2' : '' }}">

    <canvas id="matrix" aria-hidden="true"></canvas>

    <div class="language-switch">
        <a href="{{ $pageName === 'home' ? $homeHuUrl : ($pageName === 'skills' ? $skillsHuUrl : $statisticsHuUrl) }}" class="{{ $lang === 'hu' ? 'active' : '' }}" aria-label="Magyar nyelv">HUN</a>
        <a href="{{ $pageName === 'home' ? $homeEnUrl : ($pageName === 'skills' ? $skillsEnUrl : $statisticsEnUrl) }}" class="{{ $lang === 'en' ? 'active' : '' }}" aria-label="English language">ENG</a>
    </div>

    <div class="nav-container">
        @if (in_array($pageName, ['home', 'skills'], true))
            <a href="{{ $homeUrl }}" class="v2-brand">PZ <span>/ Papp Zoltán</span></a>
        @endif
        <button
            type="button"
            class="hamburger"
            aria-controls="main-nav"
            aria-expanded="false"
            aria-label="{{ $lang === 'hu' ? 'Navigáció megnyitása' : 'Open navigation' }}"
        >☰</button>
        <nav id="main-nav">
            @unless (in_array($pageName, ['home', 'skills'], true))
                <a href="{{ $homeUrl }}" class="personal-brand">PZ / Papp Zoltán</a>
            @endunless
            <a href="{{ $links['about'] }}">{{ $nav['about'] }}</a>
            <a href="{{ $links['knowledge'] }}">{{ $nav['knowledge'] }}</a>
            <a href="{{ $links['projects'] }}">{{ $nav['projects'] }}</a>
            <a href="{{ $links['contact'] }}">{{ $nav['contact'] }}</a>
            <a href="{{ config('portfolio.szoftlab_url') }}" class="company-link" target="_blank" rel="noopener noreferrer">SzoftLab ↗</a>
        </nav>
    </div>

    @unless (in_array($pageName, ['home', 'skills'], true))
    <div class="typewriter-container" aria-hidden="true">
        <pre id="typewriter">{{ implode("\n", $typewriterLines) }}</pre>
    </div>
    @endunless

    <main>@yield('content')</main>

    @if (in_array($pageName, ['home', 'skills'], true))
        <footer class="personal-footer">
            <p><strong>Papp Zoltán</strong><br>{{ config("portfolio.locales.{$lang}.footer.line") }}</p>
            <a href="mailto:{{ $person['contact']['email'] }}">{{ $person['contact']['email'] }}</a>
            <a href="{{ $pageName === 'skills' ? ($lang === 'hu' ? $skillsEnUrl : $skillsHuUrl) : ($lang === 'hu' ? $homeEnUrl : $homeHuUrl) }}">{{ $lang === 'hu' ? 'English' : 'Magyar' }}</a>
            <a href="{{ config('portfolio.szoftlab_url') }}" class="company-link" target="_blank" rel="noopener noreferrer">SzoftLab ↗</a>
            <p>&copy; {{ date('Y') }} Papp Zoltán</p>
        </footer>
    @endif

    <script>
        const typewriterLines = @json($typewriterLines);
    </script>
    <script src="{{ $assets['js'] }}" defer></script>
</body>

</html>
