<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortfolioAccessibilitySecurityTest extends TestCase
{
    public function test_mobile_menu_button_has_accessible_state_attributes(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('button', false);
        $response->assertSee('aria-controls="main-nav"', false);
        $response->assertSee('aria-expanded="false"', false);
    }

    public function test_contact_form_fields_have_labels(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('for="contact-name"', false);
        $response->assertSee('id="contact-name"', false);
        $response->assertSee('for="contact-email"', false);
        $response->assertSee('id="contact-email"', false);
        $response->assertSee('for="contact-message"', false);
        $response->assertSee('id="contact-message"', false);
        $response->assertSee('for="contact-website"', false);
        $response->assertSee('id="contact-website"', false);
    }

    public function test_contact_route_is_rate_limited(): void
    {
        $this->post('/contact', [
            'lang' => 'hu',
            'name' => 'Audit User',
            'email' => 'invalid@example.invalid',
            'message' => 'Audit message',
        ])->assertSessionHasErrors('email');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/contact', [
                'lang' => 'hu',
                'name' => 'Audit User',
                'email' => 'invalid@example.invalid',
                'message' => 'Audit message',
            ]);
        }

        $this->post('/contact', [
            'lang' => 'hu',
            'name' => 'Audit User',
            'email' => 'invalid@example.invalid',
            'message' => 'Audit message',
        ])->assertTooManyRequests();
    }

    public function test_statistics_login_route_is_rate_limited(): void
    {
        config(['portfolio.statistics_password' => 'audit-secret']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/statistics')->post('/statistics/login', [
                'password' => 'wrong-password',
                'lang' => 'hu',
            ]);
        }

        $this->from('/statistics')->post('/statistics/login', [
            'password' => 'wrong-password',
            'lang' => 'hu',
        ])->assertTooManyRequests();
    }
}
