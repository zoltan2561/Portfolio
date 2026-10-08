<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortfolioProjectsTest extends TestCase
{
    public function test_homepage_shows_new_hungarian_reference_projects(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('FoodPRO');
        $response->assertSee('NapiInfo');
        $response->assertSee('SzervizPRO');
        $response->assertSee('Saját online rendelési rendszer fizetéssel és adminfelülettel.');
        $response->assertSee('Saját szervizadminisztrációs rendszer a műhely napi munkájához.');
        $response->assertSee('AI');
        $response->assertSee('Laravel');
    }

    public function test_homepage_shows_new_english_reference_projects(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('FoodPRO');
        $response->assertSee('NapiInfo');
        $response->assertSee('SzervizPRO');
        $response->assertSee('My own online ordering system with payments and an admin interface.');
        $response->assertSee('My own service administration system for a workshop’s daily work.');
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

    public function test_both_languages_show_current_projects_and_no_service_sections(): void
    {
        foreach (['/', '/?lang=en'] as $url) {
            $response = $this->get($url)->assertOk();
            foreach (['FoodPRO', 'NapiInfo', 'SzervizPRO'] as $project) {
                $response->assertSee($project);
            }
            $response->assertSee('href="https://szoftlab.hu"', false);
            $response->assertDontSee('szoftlab.hu/termekek/');
            $this->assertSame(3, substr_count($response->getContent(), '<article class="v2-project '));
            foreach (['foodpro', 'napiinfo', 'szervizpro'] as $project) {
                $response->assertSee('assets/portfolio/'.$project.'-cover.svg');
                $this->assertFileExists(public_path('assets/portfolio/'.$project.'-cover.svg'));
            }
            $response->assertSee('https://napiinfo.com/');
            $response->assertDontSee('id="faq"', false);
            $response->assertDontSee('id="szolgaltatasok"', false);
            $response->assertDontSee('id="kinek"', false);
        }
    }

    public function test_existing_references_are_retained_outside_the_homepage_selection(): void
    {
        foreach (['hu', 'en'] as $lang) {
            $references = config("portfolio.references.{$lang}");
            $this->assertContains('https://tiszaszalkase.com', array_column($references, 'url'));
            $this->assertContains('https://gyroscity.eu', array_column($references, 'url'));
            $this->assertContains('https://zcutzbarber.com', array_column($references, 'url'));
            $this->assertContains('https://napiinfo.com', array_column($references, 'url'));
        }
    }

    public function test_additional_projects_are_in_a_native_closed_reference_list(): void
    {
        foreach (['hu', 'en'] as $lang) {
            $response = $this->get($lang === 'en' ? '/?lang=en' : '/')->assertOk();
            $document = new \DOMDocument;
            @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
            $xpath = new \DOMXPath($document);
            $details = $xpath->query('//details[@class="v2-more-projects"]');
            $this->assertSame(1, $details->length);
            $this->assertFalse($details->item(0)->hasAttribute('open'));
            $this->assertSame($lang === 'hu' ? 'További projektek és referenciák' : 'More projects and references', $xpath->query('./summary', $details->item(0))->item(0)->textContent);
            $this->assertSame(5, $xpath->query('.//article[@class="v2-reference"]', $details->item(0))->length);
            foreach (['FotoKlikk', 'SzoftLab', 'GyrosCity', 'ZCutzBarber', 'TiszaszalkaSE.com'] as $name) {
                $this->assertStringContainsString($name, $details->item(0)->textContent);
            }
            foreach (['https://fotoklikk.eu/', 'https://szoftlab.hu', 'https://gyroscity.eu', 'https://zcutzbarber.com', 'https://tiszaszalkase.com'] as $url) {
                $this->assertSame(1, $xpath->query('.//a[@href="'.$url.'"]', $details->item(0))->length);
            }
            $this->assertStringNotContainsString('NapiInfo', $details->item(0)->textContent);
        }
    }
}
