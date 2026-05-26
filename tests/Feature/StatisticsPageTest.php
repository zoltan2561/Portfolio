<?php

namespace Tests\Feature;

use Tests\TestCase;

class StatisticsPageTest extends TestCase
{
    public function test_statistics_page_requires_password_before_dashboard_is_visible(): void
    {
        $response = $this->get('/statistics');

        $response->assertOk();
        $response->assertSee('PRIVATE STATS');
        $response->assertDontSee('PRIVATE ANALYTICS');
    }

    public function test_statistics_dashboard_can_be_opened_with_correct_password(): void
    {
        config(['portfolio.statistics_password' => 'audit-secret']);

        $response = $this->post('/statistics/login', [
            'password' => 'audit-secret',
            'lang' => 'hu',
        ]);

        $response->assertRedirect('/statistics');

        $dashboard = $this->followingRedirects()->get('/statistics');

        $dashboard->assertOk();
        $dashboard->assertSee('PRIVATE ANALYTICS');
    }

    public function test_statistics_dashboard_rejects_invalid_password(): void
    {
        config(['portfolio.statistics_password' => 'audit-secret']);

        $response = $this->from('/statistics')->post('/statistics/login', [
            'password' => 'hibas',
            'lang' => 'hu',
        ]);

        $response->assertRedirect('/statistics');

        $followed = $this->followingRedirects()->get('/statistics');

        $followed->assertSee('Hibas jelszo.');
    }

    public function test_statistics_dashboard_does_not_use_default_password_when_unconfigured(): void
    {
        config(['portfolio.statistics_password' => null]);

        $response = $this->from('/statistics')->post('/statistics/login', [
            'password' => 'zoliadmin',
            'lang' => 'hu',
        ]);

        $response->assertStatus(503);
    }
}
