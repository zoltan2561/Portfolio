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

    public function test_homepage_schema_contains_webpage_professional_service_and_faq_data(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertOk();
        $schema = $this->jsonLdFromResponse($response->getContent());
        $graph = collect($schema['@graph']);

        $this->assertTrue($graph->contains(fn (array $node): bool => $node['@type'] === 'WebPage' && $node['inLanguage'] === 'en-US'));
        $this->assertTrue($graph->contains(fn (array $node): bool => $node['@type'] === 'ProfessionalService' && $node['areaServed'] === 'Hungary'));
        $this->assertTrue($graph->contains(fn (array $node): bool => $node['@type'] === 'FAQPage'));

        $faqPage = $graph->first(fn (array $node): bool => $node['@type'] === 'FAQPage');
        $this->assertCount(8, $faqPage['mainEntity']);
        $this->assertSame('How long does it take to build a website?', $faqPage['mainEntity'][0]['name']);

        $service = $graph->first(fn (array $node): bool => $node['@type'] === 'ProfessionalService');
        $this->assertSame(['Hungarian', 'English'], $service['availableLanguage']);
        $this->assertSame('OfferCatalog', $service['hasOfferCatalog']['@type']);
        $this->assertCount(5, $service['hasOfferCatalog']['itemListElement']);
    }

    public function test_homepage_shows_new_conversion_sections_in_both_languages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Hogyan dolgozom?')
            ->assertSee('Kinek tudok segíteni?')
            ->assertSee('Mit kapsz az átadáskor?')
            ->assertSee('Átadás és támogatás');

        $this->get('/?lang=en')
            ->assertOk()
            ->assertSee('How I work')
            ->assertSee('Who I can help')
            ->assertSee('What you get at delivery')
            ->assertSee('Delivery and support');
    }

    public function test_sitemap_contains_only_public_language_urls_with_hreflang_alternates(): void
    {
        $sitemap = simplexml_load_string(file_get_contents(public_path('sitemap.xml')));

        $this->assertNotFalse($sitemap);
        $this->assertCount(4, $sitemap->url);

        foreach ($sitemap->url as $url) {
            $loc = (string) $url->loc;

            $this->assertStringNotContainsString('statistics', $loc);
            $this->assertSame('2026-06-08', (string) $url->lastmod);
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
