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
}
