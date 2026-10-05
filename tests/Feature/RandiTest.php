<?php

namespace Tests\Feature;

use App\Services\Randi\InviteStore;
use App\Services\Randi\ResponseValidator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use PDO;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class RandiTest extends TestCase
{
    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = tempnam(sys_get_temp_dir(), 'randi-test-');
        config(['database.connections.randi.database' => $this->database, 'randi.base_url' => 'https://pzoli.com', 'randi.admin_password_hash' => password_hash('only-a-test-password', PASSWORD_BCRYPT, ['cost' => 4])]);
        DB::purge('randi');
        (require database_path('randi-migrations/2026_10_02_000001_create_randi_tables.php'))->up();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-02 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        DB::purge('randi');
        foreach ([$this->database, $this->database.'-journal'] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    private function invite(array $extra = []): array
    {
        return app(InviteStore::class)->create($extra + ['recipient_name' => 'Anna', 'sender_name' => 'Zoli', 'intro_message' => 'Egy kis meghívó.', 'expires_days' => 30]);
    }

    private function key(array $invite): string
    {
        $this->get('/randi/'.$invite['token'])->assertOk();

        return session('randi.keys.'.hash('sha256', $invite['token']).'.key');
    }

    private function accepted(string $key, array $extra = []): array
    {
        return $extra + ['submission_key' => $key, 'decision' => 'accepted', 'date_mode' => 'specific_date', 'preferred_date' => '2026-10-03', 'time_window' => 'evening', 'activity' => 'coffee_walk', 'note' => 'A kávét tejjel.'];
    }

    private function admin(): void
    {
        $this->withSession(['randi.admin' => hash('sha256', config('randi.admin_password_hash'))]);
    }

    public function test_tokens_are_random_hashed_and_links_are_shown_only_once(): void
    {
        $this->admin();
        $response = $this->post('/randi/admin/invitations', ['recipient_name' => 'Anna', 'sender_name' => 'Zoli', 'expires_days' => '30']);
        $response->assertCreated()->assertSee('Link másolása')->assertSee('Később nem olvasható vissza');
        preg_match('~https://pzoli.com/randi/([A-Za-z0-9_-]{43})~', $response->getContent(), $matches);
        $this->assertCount(2, $matches);
        $row = DB::connection('randi')->table('date_invites')->first();
        $this->assertSame(hash('sha256', $matches[1]), $row->token_hash);
        $this->assertSame(32, strlen(base64_decode(strtr($matches[1], '-_', '+/').'=')));
        $this->assertStringNotContainsString($matches[1], json_encode(session()->all()));
        $this->get('/randi/admin')->assertOk()->assertDontSee($matches[1]);
        $this->assertNotSame($matches[1], $this->invite()['token']);
    }

    public function test_invalid_expired_revoked_and_malformed_links_are_private(): void
    {
        $this->get('/randi/'.str_repeat('a', 43))->assertStatus(410)->assertDontSee('Anna');
        $this->get('/randi/not-a-token')->assertNotFound()->assertSee('meghívó most nem elérhető');
        $invite = $this->invite();
        DB::connection('randi')->table('date_invites')->where('id', $invite['id'])->update(['expires_at' => '2026-10-01 00:00:00']);
        $this->get('/randi/'.$invite['token'])->assertStatus(410)->assertDontSee('Anna');
        $invite = $this->invite();
        $key = $this->key($invite);
        $this->admin();
        $this->post('/randi/admin/invitations/'.$invite['id'].'/revoke')->assertRedirect();
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key))->assertStatus(410);
        $this->assertDatabaseCount('date_responses', 0, 'randi');
    }

    public function test_acceptance_is_saved_in_sqlite_and_visible_only_to_the_answering_session(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        $this->assertDatabaseCount('date_responses', 0, 'randi');
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key))->assertOk()->assertJsonPath('response.preferred_date', '2026-10-03')->assertJsonMissingPath('response.submission_key_hash');
        $this->assertDatabaseHas('date_responses', ['invite_id' => $invite['id'], 'decision' => 'accepted', 'preferred_date' => '2026-10-03', 'time_window' => 'evening', 'note' => 'A kávét tejjel.', 'submitted_at' => '2026-10-02 12:00:00'], 'randi');
        $this->get('/randi/'.$invite['token'])->assertOk()->assertSee('A kávét tejjel.')->assertSee('elmentve');
        session()->flush();
        $this->get('/randi/'.$invite['token'])->assertOk()->assertSee('már érkezett válasz')->assertDontSee('A kávét tejjel.')->assertDontSee('Anna');
        $this->admin();
        $this->get('/randi/admin')->assertOk()->assertSee('A kávét tejjel.')->assertSee('Van randiterv');
    }

    public function test_discuss_later_surprise_and_custom_are_normalized(): void
    {
        foreach (['surprise', 'custom'] as $activity) {
            $invite = $this->invite();
            $key = $this->key($invite);
            $data = $this->accepted($key, ['date_mode' => 'discuss_later', 'activity' => $activity, 'custom_activity' => 'Egy kiállítás', 'preferred_date' => 'wrong', 'time_window' => 'wrong']);
            $this->postJson('/randi/'.$invite['token'].'/response', $data)->assertOk()->assertJsonPath('response.preferred_date', null)->assertJsonPath('response.time_window', null)->assertJsonPath('response.custom_activity', $activity === 'custom' ? 'Egy kiállítás' : null);
        }
    }

    public function test_decline_needs_no_other_data_and_discards_all_unrelated_data(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        $data = $this->accepted($key, ['decision' => 'declined', 'preferred_date' => 'bad', 'activity' => 'bad', 'note' => str_repeat('X', 1000)]);
        $this->postJson('/randi/'.$invite['token'].'/response', $data)->assertOk()->assertJsonPath('response.note', null)->assertJsonPath('response.activity', null);
        $this->assertDatabaseHas('date_responses', ['decision' => 'declined', 'date_mode' => null, 'note' => null, 'preferred_date' => null], 'randi');
    }

    public function test_missing_unknown_and_oversized_fields_are_rejected_in_hungarian(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        foreach ([['activity' => null], ['activity' => 'hacking'], ['activity' => 'custom', 'custom_activity' => ''], ['activity' => 'custom', 'custom_activity' => str_repeat('é', 201)], ['note' => str_repeat('é', 281)], ['date_mode' => 'other'], ['time_window' => null]] as $invalid) {
            $field = array_key_last($invalid);
            $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key, $invalid))->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertDatabaseCount('date_responses', 0, 'randi');
    }

    public function test_date_range_real_calendar_and_past_time_windows_are_enforced(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        foreach (['2026-02-30', '2026-10-01', '2026-12-02', '03/10/2026'] as $date) {
            $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key, ['preferred_date' => $date]))->assertUnprocessable()->assertJsonValidationErrors('preferred_date');
        }
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-02 17:00:00', 'Europe/Budapest'));
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key, ['preferred_date' => '2026-10-02', 'time_window' => 'afternoon']))->assertUnprocessable()->assertJsonValidationErrors('time_window');
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key, ['preferred_date' => '2026-10-02', 'time_window' => 'early_evening']))->assertOk();
    }

    public function test_budapest_midnight_year_rollover_and_sixty_day_boundary(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-12-31 23:30:00', 'UTC'));
        $calendar = app(ResponseValidator::class)->calendar();
        $this->assertSame('2027-01-01', $calendar['today']);
        $this->assertSame('2027-03-02', $calendar['max']);
        $invite = $this->invite();
        $key = $this->key($invite);
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key, ['preferred_date' => '2026-12-31']))->assertUnprocessable();
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key, ['preferred_date' => '2027-03-02']))->assertOk();
        $this->assertDatabaseHas('date_responses', ['preferred_date' => '2027-03-02', 'submitted_at' => '2026-12-31 23:30:00'], 'randi');
    }

    public function test_identical_retries_are_idempotent_and_changes_cannot_overwrite(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        $data = $this->accepted($key, ['note' => null]);
        $this->postJson('/randi/'.$invite['token'].'/response', $data)->assertOk();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-04 12:00:00', 'UTC'));
        $this->postJson('/randi/'.$invite['token'].'/response', $data)->assertOk();
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key, ['note' => 'Megváltozott']))->assertConflict();
        $this->postJson('/randi/'.$invite['token'].'/response', ['submission_key' => $key, 'decision' => 'declined'])->assertConflict();
        $this->assertDatabaseCount('date_responses', 1, 'randi');
        $this->assertDatabaseHas('date_responses', ['decision' => 'accepted', 'note' => null], 'randi');
    }

    public function test_submission_key_is_bound_to_the_server_session_and_invitation(): void
    {
        $first = $this->invite();
        $firstKey = $this->key($first);
        $second = $this->invite();
        $this->key($second);
        $this->postJson('/randi/'.$second['token'].'/response', $this->accepted($firstKey))->assertStatus(419);
        session()->flush();
        $this->postJson('/randi/'.$first['token'].'/response', $this->accepted($firstKey))->assertStatus(419);
        $this->assertDatabaseCount('date_responses', 0, 'randi');
    }

    public function test_html_validation_drafts_and_errors_do_not_bleed_into_other_invites(): void
    {
        $first = $this->invite();
        $key = $this->key($first);
        $this->post('/randi/'.$first['token'].'/response', $this->accepted($key, ['activity' => 'custom', 'custom_activity' => '', 'note' => 'Csak az első meghívóhoz tartozik.']))->assertSessionHasErrors('custom_activity');
        $second = $this->invite();
        $this->get('/randi/'.$second['token'])->assertOk()->assertDontSee('Csak az első meghívóhoz tartozik.')->assertDontSee('Meséld el a saját programötleted.');
        $this->get('/randi/'.$first['token'])->assertOk()->assertSee('Csak az első meghívóhoz tartozik.');
        $this->assertDatabaseCount('date_responses', 0, 'randi');
    }

    public function test_retry_keeps_zero_text_and_https_session_cookies_are_secure(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        $data = $this->accepted($key, ['activity' => 'custom', 'custom_activity' => '0', 'note' => '0']);
        $this->postJson('/randi/'.$invite['token'].'/response', $data)->assertOk();
        $this->postJson('/randi/'.$invite['token'].'/response', $data)->assertOk();
        $response = $this->get('https://localhost/randi');
        $cookies = array_filter($response->headers->getCookies(), fn ($cookie) => $cookie->getName() === config('session.cookie'));
        $this->assertCount(1, $cookies);
        $cookie = array_values($cookies)[0];
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    public function test_get_head_steps_and_demo_never_create_a_final_response(): void
    {
        $invite = $this->invite();
        $this->key($invite);
        $this->head('/randi/'.$invite['token'])->assertOk();
        $this->post('/randi/'.$invite['token'].'/form', ['action' => 'start'])->assertRedirect();
        $this->get('/randi')->assertOk()->assertSee('válaszodat nem mentjük');
        $key = session('randi.keys.demo.key');
        $this->postJson('/randi/response', $this->accepted($key))->assertOk()->assertJsonPath('demo', true);
        $this->assertDatabaseCount('date_invites', 1, 'randi');
        $this->assertDatabaseCount('date_responses', 0, 'randi');
    }

    public function test_admin_authentication_is_fail_closed_rotates_sessions_and_logs_out(): void
    {
        $this->get('/randi/admin')->assertRedirect('/randi/admin/login');
        $this->post('/randi/admin/invitations', ['sender_name' => 'Zoli'])->assertRedirect('/randi/admin/login');
        $this->get('/randi/admin/login')->assertOk();
        $oldId = session()->getId();
        $this->post('/randi/admin/login', ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->post('/randi/admin/login', ['password' => 'only-a-test-password'])->assertRedirect('/randi/admin');
        $this->assertNotSame($oldId, session()->getId());
        $this->get('/randi/admin')->assertOk();
        $this->post('/randi/admin/logout')->assertRedirect('/randi/admin/login');
        $this->get('/randi/admin')->assertRedirect('/randi/admin/login');
        config(['randi.admin_password_hash' => '']);
        $this->post('/randi/admin/login', ['password' => 'only-a-test-password'])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('date_invites', 0, 'randi');
    }

    public function test_csrf_is_required_for_public_and_admin_writes(): void
    {
        $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
        $invite = $this->invite();
        $key = $this->key($invite);
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key))->assertStatus(419);
        $this->post('/randi/admin/login', ['password' => 'only-a-test-password'])->assertStatus(419);
        $this->admin();
        $this->post('/randi/admin/invitations', ['sender_name' => 'Zoli'])->assertStatus(419);
        $this->assertDatabaseCount('date_responses', 0, 'randi');
    }

    public function test_user_content_is_escaped_in_public_and_admin_views(): void
    {
        $xss = '<img src=x onerror=alert(1)>';
        $invite = $this->invite(['recipient_name' => $xss, 'intro_message' => '<script>alert(1)</script>']);
        $this->get('/randi/'.$invite['token'])->assertSee($xss)->assertDontSee($xss, false)->assertDontSee('<script>alert(1)</script>', false);
        $key = session('randi.keys.'.hash('sha256', $invite['token']).'.key');
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key, ['activity' => 'custom', 'custom_activity' => $xss, 'note' => $xss]))->assertOk();
        $this->get('/randi/'.$invite['token'])->assertSee($xss)->assertDontSee($xss, false);
        $this->admin();
        $this->get('/randi/admin')->assertSee($xss)->assertDontSee($xss, false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_confirmed_deletion_cascades_and_responses_remain_readable_after_expiry(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key))->assertOk();
        $this->admin();
        DB::connection('randi')->table('date_invites')->where('id', $invite['id'])->update(['expires_at' => '2026-10-01 00:00:00']);
        $this->get('/randi/admin')->assertOk()->assertSee('A kávét tejjel.');
        $this->post('/randi/admin/invitations/'.$invite['id'].'/delete', ['confirmation' => 'no'])->assertSessionHasErrors('confirmation');
        $this->assertDatabaseCount('date_responses', 1, 'randi');
        $this->post('/randi/admin/invitations/'.$invite['id'].'/delete', ['confirmation' => 'TORLES'])->assertRedirect();
        $this->assertDatabaseCount('date_invites', 0, 'randi');
        $this->assertDatabaseCount('date_responses', 0, 'randi');
    }

    public function test_no_javascript_forms_complete_the_same_validated_flow(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        $url = '/randi/'.$invite['token'];
        $this->post($url.'/form', ['action' => 'start'])->assertRedirect($url);
        $this->get($url)->assertSee('data-initial-step="joy"', false);
        $this->post($url.'/form', ['action' => 'date'])->assertRedirect();
        $this->post($url.'/form', ['action' => 'activity', 'date_mode' => 'discuss_later'])->assertRedirect();
        $this->post($url.'/form', ['action' => 'review', 'activity' => 'custom', 'custom_activity' => 'Kiállítás', 'note' => 'Várlak.'])->assertRedirect();
        $this->get($url)->assertSee('data-initial-step="review"', false)->assertSee('Kiállítás');
        $this->post($url.'/response', ['decision' => 'accepted', 'submission_key' => $key])->assertRedirect($url);
        $this->get($url)->assertSee('data-initial-step="success"', false)->assertSee('Várlak.');
        $this->assertDatabaseHas('date_responses', ['activity' => 'custom', 'custom_activity' => 'Kiállítás', 'date_mode' => 'discuss_later'], 'randi');
    }

    public function test_storage_failure_and_read_only_database_never_claim_success(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        DB::connection('randi')->statement('PRAGMA query_only = ON');
        $response = $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key));
        $response->assertStatus(503)->assertJsonPath('ok', false)->assertJsonPath('message', 'Most nem sikerült elmenteni. A választásaid megvannak, próbáld újra.')->assertDontSee($this->database)->assertDontSee('SQLSTATE');
        DB::connection('randi')->statement('PRAGMA query_only = OFF');
        $this->assertDatabaseCount('date_responses', 0, 'randi');
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key))->assertOk();
    }

    public function test_parallel_acceptance_and_decline_have_exactly_one_winner(): void
    {
        $invite = $this->invite();
        $barrier = $this->database.'.barrier';
        $processes = [];
        foreach (['accepted', 'declined'] as $decision) {
            $process = new Process([PHP_BINARY, base_path('tests/Support/randi-submit.php'), $this->database, $invite['token'], InviteStore::secret(), $decision, $barrier], base_path(), null, null, 15);
            $process->start();
            $processes[] = $process;
        }
        touch($barrier);
        $outcomes = [];
        foreach ($processes as $process) {
            $process->wait();
            $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
            $outcomes[] = trim($process->getOutput());
        }
        unlink($barrier);
        sort($outcomes);
        $this->assertSame(['conflict', 'saved'], $outcomes);
        $this->assertDatabaseCount('date_responses', 1, 'randi');
    }

    public function test_locked_database_returns_retryable_failure(): void
    {
        $invite = $this->invite();
        $key = $this->key($invite);
        $locker = new PDO('sqlite:'.$this->database);
        $locker->exec('BEGIN IMMEDIATE');
        try {
            $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key))->assertStatus(503)->assertJsonPath('ok', false);
        } finally {
            $locker->exec('ROLLBACK');
        }
        $this->assertDatabaseCount('date_responses', 0, 'randi');
        $this->postJson('/randi/'.$invite['token'].'/response', $this->accepted($key))->assertOk();
    }

    public function test_module_privacy_headers_assets_and_neutral_metadata(): void
    {
        $invite = $this->invite();
        $response = $this->get('/randi/'.$invite['token']);
        $response->assertHeader('Referrer-Policy', 'no-referrer')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('content="Van egy kérdésem… 💌"', false)->assertDontSee('content="Anna', false)->assertDontSee('fonts.googleapis')->assertDontSee('id="main-nav"', false);
        $this->get('/randi/admin/login')->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertSame(1, (int) DB::connection('randi')->selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->assertSame(5000, (int) DB::connection('randi')->selectOne('PRAGMA busy_timeout')->timeout);
        $this->assertSame('delete', DB::connection('randi')->selectOne('PRAGMA journal_mode')->journal_mode);
    }

    public function test_rate_limits_and_admin_validation(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/randi/admin/login', ['password' => 'wrong']);
        }
        $this->post('/randi/admin/login', ['password' => 'wrong'])->assertTooManyRequests();
        $this->admin();
        $this->post('/randi/admin/invitations', ['recipient_name' => str_repeat('é', 81), 'sender_name' => '', 'intro_message' => str_repeat('é', 241), 'expires_days' => 91])->assertSessionHasErrors(['recipient_name', 'sender_name', 'intro_message', 'expires_days']);
        $this->assertDatabaseCount('date_invites', 0, 'randi');
    }

    public function test_private_database_path_is_enforced_and_backup_is_consistent(): void
    {
        $this->invite();
        $backup = $this->database.'.backup';
        $this->artisan('randi:backup', ['destination' => $backup])->assertSuccessful();
        $pdo = new PDO('sqlite:'.$backup);
        $this->assertSame('ok', $pdo->query('PRAGMA integrity_check')->fetchColumn());
        $this->assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM date_invites')->fetchColumn());
        $pdo = null;
        unlink($backup);
        $this->expectException(\RuntimeException::class);
        InviteStore::assertPrivatePath(public_path('randi/leak.sqlite'));
    }
}
