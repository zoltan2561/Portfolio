<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortfolioProjectsTest extends TestCase
{
    public function test_homepage_shows_new_hungarian_reference_projects(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('TiszaszalkaSE.com');
        $response->assertSee('https://tiszaszalkase.com');
        $response->assertSee('MLSZ');
        $response->assertSee('NapiInfo.com');
        $response->assertSee('https://napiinfo.com');
        $response->assertSee('AI');
        $response->assertSee('Laravel');
    }

    public function test_homepage_shows_new_english_reference_projects(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('TiszaszalkaSE.com');
        $response->assertSee('https://tiszaszalkase.com');
        $response->assertSee('MLSZ');
        $response->assertSee('NapiInfo.com');
        $response->assertSee('https://napiinfo.com');
        $response->assertSee('AI');
        $response->assertSee('Laravel');
    }

    public function test_homepage_shows_github_contact_link(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('GitHub');
        $response->assertSee('https://github.com/zoltan2561');
    }

    public function test_homepage_shows_hungarian_faq_section_and_navigation(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('GYIK');
        $response->assertSee('#faq"', false);
        $response->assertSee('id="faq"', false);
        $response->assertSee('details class="faq-item"', false);
        $response->assertSee('Gyakran Ismételt Kérdések');
        $response->assertSee('Mennyi idő alatt készül el egy weboldal?');
        $response->assertSee('Segítesz domain, tárhely vagy email beállításban is?');
    }

    public function test_homepage_shows_english_faq_section_and_navigation(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('FAQ');
        $response->assertSee('lang=en#faq"', false);
        $response->assertSee('Frequently Asked Questions');
        $response->assertSee('How long does it take to build a website?');
        $response->assertSee('Can you help with domain, hosting or email setup?');
    }
}
