<?php

namespace App\Http\Controllers;

use App\Services\VisitorCounter;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    private const STATISTICS_SESSION_KEY = 'statistics_authenticated';

    public function home(Request $request, VisitorCounter $counter)
    {
        $lang = $this->resolveLocale($request);
        $counter->track($request);

        return view('portfolio.home', $this->buildPageData($lang, 'home'));
    }

    public function skills(Request $request, VisitorCounter $counter)
    {
        $lang = $this->resolveLocale($request);
        $counter->track($request);

        return view('portfolio.skills', $this->buildPageData($lang, 'skills'));
    }

    public function statistics(Request $request, VisitorCounter $counter)
    {
        $lang = $this->resolveLocale($request);

        if (! $request->session()->get(self::STATISTICS_SESSION_KEY, false)) {
            return view('portfolio.statistics-login', [
                'lang' => $lang,
                'passwordHint' => app()->environment('local') ? env('STATISTICS_PASSWORD', 'zoliadmin') : null,
            ]);
        }

        $counter->track($request);
        $stats = $counter->statistics();

        return view('portfolio.statistics', array_merge(
            $this->buildPageData($lang, 'statistics'),
            ['stats' => $stats]
        ));
    }

    public function authenticateStatistics(Request $request)
    {
        $lang = $this->resolveLocale($request);
        $request->validate([
            'password' => ['required', 'string'],
            'lang' => ['nullable', 'in:hu,en'],
        ]);

        $configuredPassword = (string) env('STATISTICS_PASSWORD', 'zoliadmin');

        if (! hash_equals($configuredPassword, (string) $request->input('password'))) {
            return redirect()
                ->route('statistics', $lang === 'en' ? ['lang' => 'en'] : [])
                ->withErrors([
                    'password' => $lang === 'en' ? 'Invalid password.' : 'Hibas jelszo.',
                ]);
        }

        $request->session()->put(self::STATISTICS_SESSION_KEY, true);
        $request->session()->regenerate();

        return redirect()->route('statistics', $lang === 'en' ? ['lang' => 'en'] : []);
    }

    public function logoutStatistics(Request $request)
    {
        $lang = $this->resolveLocale($request);

        $request->session()->forget(self::STATISTICS_SESSION_KEY);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('statistics', $lang === 'en' ? ['lang' => 'en'] : []);
    }

    private function resolveLocale(Request $request): string
    {
        $lang = $request->query('lang') === 'en' ? 'en' : 'hu';

        app()->setLocale($lang);

        return $lang;
    }

    private function buildPageData(string $lang, string $page): array
    {
        $localeContent = config("portfolio.locales.{$lang}");
        $person = config('portfolio.person');
        $baseUrl = rtrim(config('portfolio.base_url') ?: config('app.url') ?: url('/'), '/');

        $homeUrl = route('home', $lang === 'en' ? ['lang' => 'en'] : []);
        $skillsUrl = route('skills', $lang === 'en' ? ['lang' => 'en'] : []);
        $statisticsUrl = route('statistics', $lang === 'en' ? ['lang' => 'en'] : []);

        $homeHuUrl = route('home');
        $homeEnUrl = route('home', ['lang' => 'en']);
        $skillsHuUrl = route('skills');
        $skillsEnUrl = route('skills', ['lang' => 'en']);
        $statisticsHuUrl = route('statistics');
        $statisticsEnUrl = route('statistics', ['lang' => 'en']);

        $meta = $this->resolveMeta($lang, $page);
        $currentUrl = match ($page) {
            'home' => $lang === 'en' ? $homeEnUrl : $homeHuUrl,
            'skills' => $lang === 'en' ? $skillsEnUrl : $skillsHuUrl,
            'statistics' => $lang === 'en' ? $statisticsEnUrl : $statisticsHuUrl,
        };
        $canonicalUrl = match ($page) {
            'home' => $homeHuUrl,
            'skills' => $skillsHuUrl,
            'statistics' => $statisticsHuUrl,
        };

        $typewriterSets = $this->resolvePageContent($lang, $page)['typewriter_sets'];
        $typewriterLines = $typewriterSets[array_rand($typewriterSets)];

        $preloaderSets = $page === 'home' ? $localeContent['home']['preloader_sets'] : [];
        $preloaderLines = $preloaderSets ? $preloaderSets[array_rand($preloaderSets)] : [];

        $schema = $this->buildSchema($baseUrl, $currentUrl, $page, $meta, $person);

        return [
            'lang' => $lang,
            'pageName' => $page,
            'meta' => $meta,
            'person' => $person,
            'nav' => $this->resolveNav($lang),
            'page' => $this->resolvePageContent($lang, $page),
            'homeUrl' => $homeUrl,
            'skillsUrl' => $skillsUrl,
            'statisticsUrl' => $statisticsUrl,
            'homeHuUrl' => $homeHuUrl,
            'homeEnUrl' => $homeEnUrl,
            'skillsHuUrl' => $skillsHuUrl,
            'skillsEnUrl' => $skillsEnUrl,
            'statisticsHuUrl' => $statisticsHuUrl,
            'statisticsEnUrl' => $statisticsEnUrl,
            'canonicalUrl' => $canonicalUrl,
            'currentUrl' => $currentUrl,
            'schemaJson' => json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            'typewriterLines' => $typewriterLines,
            'preloaderLines' => $preloaderLines,
            'ogImage' => asset('icons/og.jpg'),
            'ogImageAlt' => $lang === 'en'
                ? 'Papp Zoltan portfolio preview image'
                : 'Papp Zoltan portfolio elonezeti kep',
            'ogImageWidth' => 1254,
            'ogImageHeight' => 1254,
            'favicon' => asset('ico.png'),
            'assets' => [
                'css' => asset('Style.css'),
                'js' => asset('script.js'),
                'profile' => asset($person['image']),
                'cv' => asset('cv.pdf'),
            ],
            'links' => [
                'about' => $homeUrl.'#rolam',
                'services' => $homeUrl.'#szolgaltatasok',
                'projects' => $homeUrl.'#projektek',
                'contact' => $homeUrl.'#kapcsolat',
            ],
        ];
    }

    private function resolveMeta(string $lang, string $page): array
    {
        $localeContent = config("portfolio.locales.{$lang}");

        if (isset($localeContent['meta'][$page])) {
            return $localeContent['meta'][$page];
        }

        return $lang === 'en'
            ? [
                'title' => 'Papp Zoltan - Visitor Statistics',
                'description' => 'Quick overview of visitor activity, daily unique visits, and device usage on the portfolio.',
            ]
            : [
                'title' => 'Papp Zoltan - Latogatoi statisztikak',
                'description' => 'Gyors attekintes a portfolio napi egyedi latogatoirol es az eszkozaranyokrol.',
            ];
    }

    private function resolveNav(string $lang): array
    {
        $localeContent = config("portfolio.locales.{$lang}");
        $nav = $localeContent['nav'];
        $nav['statistics'] = $lang === 'en' ? 'Statistics' : 'Statisztika';

        return $nav;
    }

    private function resolvePageContent(string $lang, string $page): array
    {
        $localeContent = config("portfolio.locales.{$lang}");

        if (isset($localeContent[$page])) {
            return $localeContent[$page];
        }

        return $lang === 'en'
            ? [
                'hero_title' => 'Visitor Statistics',
                'hero_text' => 'A quick overview of how the portfolio is performing based on the built-in visit tracker.',
                'intro' => 'This page shows daily unique visits and the device split, so incoming traffic is easy to follow at a glance.',
                'cards' => [
                    'today' => 'Today',
                    'total' => 'Total unique visits',
                    'pageviews' => 'Pageviews today',
                    'mobile' => 'Mobile today',
                    'desktop' => 'Desktop today',
                    'tablet' => 'Tablet today',
                ],
                'chart_title' => 'Last 7 days',
                'chart_text' => 'Daily unique visits based on the current built-in tracking.',
                'devices_title' => 'Device split today',
                'recent_title' => 'Latest visitors',
                'recent_text' => 'Recent unique visits captured in the last 24 hours.',
                'empty' => 'No visitor data has been recorded yet.',
                'typewriter_sets' => [
                    ["> Analytics module loaded...", "> Built-in tracker online", "> Visitor data ready", " Statistics dashboard unlocked"],
                ],
            ]
            : [
                'hero_title' => 'Latogatoi statisztika',
                'hero_text' => 'Gyors attekintes a portfolio teljesitmenyerol a beepitett latogatokovetes alapjan.',
                'intro' => 'Itt egybol latod a napi egyedi latogatokat es az eszkozaranyokat, hogy konnyen kovethesd az oldal forgalmat.',
                'cards' => [
                    'today' => 'Mai egyedi latogatok',
                    'total' => 'Osszes egyedi latogatas',
                    'pageviews' => 'Mai oldalletoltesek',
                    'mobile' => 'Mai mobil',
                    'desktop' => 'Mai asztali',
                    'tablet' => 'Mai tablet',
                ],
                'chart_title' => 'Utolso 7 nap',
                'chart_text' => 'Napi egyedi latogatok a jelenlegi beepitett kovetes alapjan.',
                'devices_title' => 'Mai eszkozarany',
                'recent_title' => 'Legutobbi latogatok',
                'recent_text' => 'A legfrissebb egyedi latogatasok az elmult 24 orabol.',
                'empty' => 'Meg nincs rogzitett latogatoi adat.',
                'typewriter_sets' => [
                    ["> Analitika modul betoltve...", "> Beepitett kovetes aktiv", "> Latogatoi adatok elerhetok", " Statisztikai dashboard keszen"],
                ],
            ];
    }

    private function buildSchema(string $baseUrl, string $currentUrl, string $page, array $meta, array $person): array
    {
        $graph = [
            [
                '@type' => 'Person',
                '@id' => $baseUrl.'#person',
                'name' => $person['name'],
                'url' => $baseUrl,
                'image' => $baseUrl.'/icons/profile-bw.jpg',
                'jobTitle' => $person['job_title'],
                'sameAs' => $person['same_as'],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $baseUrl.'#website',
                'url' => $baseUrl,
                'name' => config('portfolio.site_name'),
                'inLanguage' => ['hu-HU', 'en-US'],
            ],
        ];

        if ($page === 'home') {
            $graph[] = [
                '@type' => 'ProfessionalService',
                '@id' => $baseUrl.'#service',
                'name' => 'Papp Zoltán digitális szolgáltatások',
                'url' => $baseUrl,
                'provider' => ['@id' => $baseUrl.'#person'],
                'areaServed' => 'HU',
                'serviceType' => config('portfolio.service_types'),
            ];
        } else {
            $graph[] = [
                '@type' => 'WebPage',
                '@id' => $currentUrl.'#webpage',
                'url' => $currentUrl,
                'name' => $meta['title'],
                'description' => $meta['description'],
                'isPartOf' => ['@id' => $baseUrl.'#website'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }
}
