<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortfolioSeoTest extends TestCase
{
    public function test_canonical_urls_are_self_referencing_for_public_language_versions(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false);

        $this->get('/?lang=en')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('home', ['lang' => 'en']).'">', false);

        $this->get('/skills')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('skills').'">', false);

        $this->get('/skills?lang=en')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('skills', ['lang' => 'en']).'">', false);
    }

    public function test_robots_meta_is_dynamic_for_public_and_private_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false);

        $this->get('/skills')
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false);

        $this->get('/statistics')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        config(['portfolio.statistics_password' => 'audit-secret']);
        $this->post('/statistics/login', [
            'password' => 'audit-secret',
            'lang' => 'en',
        ])->assertRedirect('/statistics?lang=en');

        $this->get('/statistics?lang=en')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_homepage_schema_describes_a_personal_profile_without_service_offers(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertOk();
        $schema = $this->jsonLdFromResponse($response->getContent());
        $graph = collect($schema['@graph']);

        $profile = $graph->firstWhere('@type', 'ProfilePage');
        $person = $graph->firstWhere('@type', 'Person');
        $this->assertSame('en-US', $profile['inLanguage']);
        $this->assertSame($person['@id'], $profile['mainEntity']['@id']);
        $this->assertContains('Laravel', $person['knowsAbout']);
        $this->assertFalse($graph->contains(fn (array $node): bool => in_array($node['@type'], ['ProfessionalService', 'FAQPage', 'OfferCatalog'], true)));
    }

    public function test_homepage_shows_professional_sections_and_confirmed_employers_without_sales(): void
    {
        $hungarian = $this->get('/');

        $hungarian
            ->assertOk()
            ->assertSee('href="'.route('home').'#tudas"', false)
            ->assertSee('id="eletut"', false)
            ->assertSee('id="szemlelet"', false)
            ->assertSee('Technológiák')
            ->assertSee('Kicsit rólam')
            ->assertSee('Papp Zoltán · Fejlesztő és IT-szakember')
            ->assertSee('A hardvertől a kódig. Ez az én világom.')
            ->assertSee('10 év az IT több oldalán.')
            ->assertSee('Megkeresem az okát.')
            ->assertSee('Programtervező informatikus BSc')
            ->assertSee('Nyíregyházi Egyetem')
            ->assertSee('felsőfokú nyelvtudás')
            ->assertDontSee('Kíváncsiságból indult.')
            ->assertDontSee('Tudásom')
            ->assertDontSee('id="preloader"', false)
            ->assertDontSee('id="typewriter"', false)
            ->assertDontSee('id="szolgaltatasok"', false)
            ->assertDontSee('ingyenes felmérés')
            ->assertSee('Hoya Lens')
            ->assertSee('Oxford One');

        $this->get('/?lang=en')
            ->assertOk()
            ->assertSee('href="'.route('home', ['lang' => 'en']).'#tudas"', false)
            ->assertSee('Technologies')
            ->assertSee('Papp Zoltán · Developer and IT professional')
            ->assertSee('From hardware to code. This is my world.')
            ->assertSee('10 years across different sides of IT.')
            ->assertSee('I find the cause.')
            ->assertSee('BSc in Computer Science.')
            ->assertSee('University of Nyíregyháza')
            ->assertSee('advanced English proficiency')
            ->assertDontSee('It started with curiosity.')
            ->assertDontSee('My knowledge')
            ->assertDontSee('ProfessionalService')
            ->assertDontSee('free discovery')
            ->assertSee('Hoya Lens')
            ->assertSee('Oxford One');
    }

    public function test_skills_page_shows_ai_section_in_both_languages(): void
    {
        $this->get('/skills')
            ->assertOk()
            ->assertSee('AI és automatizálás')
            ->assertSee('OpenAI API')
            ->assertSee('Ollama')
            ->assertSee('LM Studio')
            ->assertSee('CI/CD')
            ->assertSee('Fejlődési irányok')
            ->assertSee('Alapismeret')
            ->assertSee('Spring')
            ->assertDontSee('PHPUnit')
            ->assertDontSee('AWS')
            ->assertSee('TypeScript');

        $this->get('/skills?lang=en')
            ->assertOk()
            ->assertSee('AI and automation')
            ->assertSee('OpenAI API')
            ->assertSee('Ollama')
            ->assertSee('LM Studio')
            ->assertSee('CI/CD')
            ->assertSee('Learning focus')
            ->assertSee('Basic knowledge')
            ->assertSee('Spring')
            ->assertDontSee('PHPUnit')
            ->assertDontSee('AWS')
            ->assertSee('TypeScript');
    }

    public function test_personal_sections_are_in_order_and_signature_occurs_once_per_language(): void
    {
        foreach (['hu', 'en'] as $lang) {
            $response = $this->get($lang === 'en' ? '/?lang=en' : '/')->assertOk();
            $html = $response->getContent();
            $previous = -1;
            foreach (['rolam', 'eletut', 'szemlelet', 'tudas', 'projektek', 'kapcsolat'] as $id) {
                $position = strpos($html, 'id="'.$id.'"');
                $this->assertNotFalse($position);
                $this->assertGreaterThan($previous, $position);
                $previous = $position;
            }
            $document = new \DOMDocument;
            @$document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
            $this->assertSame(1, $document->getElementsByTagName('h1')->length);
            $this->assertSame(config("portfolio.locales.{$lang}.home.hero_title"), $document->getElementsByTagName('h1')->item(0)->textContent);
            $this->assertSame(1, substr_count($document->textContent, config("portfolio.locales.{$lang}.home.hero_title")));
            $this->assertSame(4, substr_count($html, '<article class="v2-tech-card '));
            $response->assertSee('href="'.route('skills', $lang === 'en' ? ['lang' => 'en'] : []).'"', false);
        }
    }

    public function test_supplied_hungarian_body_copy_is_used_verbatim(): void
    {
        $document = file_get_contents(base_path('PZOLI_PORTFOLIO_V2_CODEX.md'));
        foreach (['home', 'skills'] as $page) {
            $html = $this->get($page === 'home' ? '/' : '/skills')->assertOk()->getContent();
            $copy = config("portfolio.locales.hu.{$page}");
            $walk = function (array $values) use (&$walk, $document, $html): void {
                foreach ($values as $key => $value) {
                    if (is_array($value)) {
                        if (! in_array($key, ['typewriter_sets', 'about'], true)) {
                            $walk($value);
                        }
                    } elseif (is_string($value) && mb_strlen($value) > 70 && $key !== 'hero_text') {
                        $this->assertStringContainsString($value, $document);
                        if (! in_array($key, ['success', 'error'], true)) {
                            $this->assertStringContainsString(e($value), $html);
                        }
                    }
                }
            };
            $walk($copy);
        }
    }

    public function test_updated_personal_story_is_visible_in_both_languages(): void
    {
        foreach (['hu', 'en'] as $lang) {
            $response = $this->get($lang === 'en' ? '/?lang=en' : '/')->assertOk();
            $document = new \DOMDocument;
            @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
            $xpath = new \DOMXPath($document);
            $paragraphs = $xpath->query('//section[@id="rolam"]//div[@class="v2-about-top"]/div/p | //div[@class="v2-about-story"]/p');
            $this->assertSame(6, $paragraphs->length);
            foreach (config("portfolio.locales.{$lang}.home.about.paragraphs") as $index => $paragraph) {
                $this->assertSame($paragraph['text'], $paragraphs->item($index)->textContent);
            }
            $milestones = $xpath->query('//ol[@id="eletut"]/li');
            $this->assertSame(8, $milestones->length);
            $roles = $lang === 'hu'
                ? ['Rendszergazda', 'IT-supporter', 'Junior fejlesztő', 'Medior fejlesztő', 'Vezető fejlesztő']
                : ['Systems administrator', 'IT support', 'Junior developer', 'Mid-level developer', 'Lead developer'];
            foreach (['Hétvezér Iskola', 'Hoya Lens', 'Hoya Lens', 'Oxford One', 'SzoftLab'] as $index => $employer) {
                $milestone = $milestones->item($index + 3);
                $this->assertSame($roles[$index], $xpath->query('.//h3', $milestone)->item(0)->textContent);
                $this->assertSame($employer, $xpath->query('.//p', $milestone)->item(0)->textContent);
            }
            $this->assertSame(config("portfolio.locales.{$lang}.home.hero_text"), $xpath->query('//p[@class="v2-intro"]')->item(0)->textContent);
            $response->assertDontSee('1-esek és 0-k');
            $response->assertDontSee('ones and zeros');
            if ($lang === 'hu') {
                $response->assertSee('Thaiföldön');
                $response->assertSee('Felsőfokú angol nyelvvizsgám van');
                $response->assertSee('Jelenleg medior Laravel-fejlesztőként dolgozom');
                $response->assertSee('vezető fejlesztőként');
            } else {
                $response->assertSee('Thailand');
                $response->assertSee('advanced English language exam qualification');
                $response->assertSee('mid-level Laravel developer');
                $response->assertSee('leading development');
            }
        }
    }

    public function test_technologies_have_visible_names_levels_and_accessible_local_icons(): void
    {
        foreach (['hu', 'en'] as $lang) {
            $response = $this->get($lang === 'en' ? '/skills?lang=en' : '/skills')->assertOk();
            $html = $response->getContent();
            $this->assertSame(7, substr_count($html, 'class="v2-category-title"'));
            $this->assertSame(3, substr_count($html, '<details>'));
            $response->assertSee('aria-hidden="true" focusable="false"', false);
            $response->assertDontSee('<script src="https://cdn', false);
            $response->assertDontSee('Tudásom');
            foreach (config("portfolio.locales.{$lang}.skills.groups") as $group) {
                $response->assertSee('href="#'.$group['id'].'"', false);
                $response->assertSee('id="'.$group['id'].'"', false);
                foreach ($group['items'] as $item) {
                    $response->assertSee($item['name']);
                }
            }
            $development = config("portfolio.locales.{$lang}.skills.groups.0.items");
            $mainFocus = array_values(array_filter($development, fn ($item) => ($item['level'] ?? '') === ($lang === 'hu' ? 'Fő fókusz' : 'Main focus')));
            $this->assertSame(['PHP', 'Laravel'], array_column($mainFocus, 'name'));
            $basic = array_values(array_filter($development, fn ($item) => ($item['level'] ?? '') === ($lang === 'hu' ? 'Alapismeret' : 'Basic knowledge')));
            $this->assertSame(['Tailwind CSS', 'Bootstrap', 'Spring'], array_column($basic, 'name'));
            $document = new \DOMDocument;
            @$document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
            $xpath = new \DOMXPath($document);
            $learning = $xpath->query('//section[@id="fejlodes"]')->item(0);
            foreach (['Java', 'Spring Boot', 'Node.js', 'TypeScript', 'React', 'Next.js'] as $technology) {
                $this->assertSame(1, $xpath->query('.//li/span[not(@aria-hidden) and text()="'.$technology.'"]', $learning)->length);
            }
            $this->assertStringContainsString($lang === 'hu' ? 'Fejlődési irányok' : 'Learning focus', $learning->textContent);
            $this->assertSame(9, $xpath->query('.//li', $learning)->length);
        }
    }

    public function test_sitemap_contains_only_public_language_urls_with_hreflang_alternates(): void
    {
        $sitemap = simplexml_load_string(file_get_contents(public_path('sitemap.xml')));

        $this->assertNotFalse($sitemap);
        $this->assertCount(4, $sitemap->url);

        foreach ($sitemap->url as $url) {
            $loc = (string) $url->loc;

            $this->assertStringNotContainsString('statistics', $loc);
            $this->assertSame('2026-10-08', (string) $url->lastmod);
            $this->assertCount(3, $url->children('http://www.w3.org/1999/xhtml')->link);
        }
    }

    private function jsonLdFromResponse(string $content): array
    {
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $content, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
