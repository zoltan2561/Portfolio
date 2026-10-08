<?php

namespace Tests\Feature;

use App\Mail\PortfolioContactMessage;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class PortfolioContactTest extends TestCase
{
    public function test_success_is_shown_only_after_successful_backend_processing_in_both_languages(): void
    {
        foreach (['hu', 'en'] as $lang) {
            Mail::fake();
            $this->get($this->home($lang))->assertDontSee(config("portfolio.locales.{$lang}.home.contact.success"));
            $this->post('/contact', $this->payload($lang))
                ->assertRedirect(route('home', $lang === 'en' ? ['lang' => 'en'] : []).'#kapcsolat')
                ->assertSessionHas('contact_status', 'success');
            Mail::assertSent(PortfolioContactMessage::class, 1);
            $this->get($this->home($lang))->assertOk()
                ->assertSee(config("portfolio.locales.{$lang}.home.contact.success"))
                ->assertDontSee(config("portfolio.locales.{$lang}.home.contact.error"));
        }
    }

    public function test_mail_failure_shows_error_without_success_in_both_languages(): void
    {
        Mail::shouldReceive('to')->twice()->andReturnSelf();
        Mail::shouldReceive('send')->twice()->andThrow(new RuntimeException('Simulated local mail failure'));
        foreach (['hu', 'en'] as $lang) {
            $this->post('/contact', $this->payload($lang))
                ->assertRedirect(route('home', $lang === 'en' ? ['lang' => 'en'] : []).'#kapcsolat')
                ->assertSessionHas('contact_status', 'error');
            $this->get($this->home($lang))->assertOk()
                ->assertSee(config("portfolio.locales.{$lang}.home.contact.error"))
                ->assertDontSee(config("portfolio.locales.{$lang}.home.contact.success"));
        }
    }

    public function test_validation_and_honeypot_do_not_send_mail_or_claim_success(): void
    {
        Mail::fake();
        $this->post('/contact', array_replace($this->payload('hu'), ['email' => 'invalid']))
            ->assertSessionHasErrors('email');
        $this->get('/')->assertSee(config('portfolio.locales.hu.home.contact.error'));
        $this->post('/contact', array_replace($this->payload('en'), ['website' => 'spam']))
            ->assertSessionHas('contact_status', 'error');
        $this->get('/?lang=en')->assertSee(config('portfolio.locales.en.home.contact.error'))
            ->assertDontSee(config('portfolio.locales.en.home.contact.success'));
        Mail::assertNothingSent();
    }

    private function home(string $lang): string
    {
        return $lang === 'en' ? '/?lang=en' : '/';
    }

    private function payload(string $lang): array
    {
        // Mail is faked/mocked; a domain with DNS is needed by the existing validation.
        return ['lang' => $lang, 'name' => 'Local QA', 'email' => 'portfolio-qa@gmail.com', 'message' => 'Local automated check only.'];
    }
}
