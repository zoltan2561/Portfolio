# Reference Projects Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add TiszaszalkaSE.com and NapiInfo.com to the portfolio homepage references in Hungarian and English.

**Architecture:** Project cards are data-driven from `config/portfolio.php`; the Blade view already loops over the configured items. Add focused coverage in a feature test, then update the config arrays.

**Tech Stack:** Laravel 12, PHPUnit feature tests, Blade view rendering from config.

---

### Task 1: Homepage Reference Coverage

**Files:**
- Create: `tests/Feature/PortfolioProjectsTest.php`

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PortfolioProjectsTest`

Expected: FAIL because `PortfolioProjectsTest` does not exist yet and the new project content is not configured.

### Task 2: Project Config Content

**Files:**
- Modify: `config/portfolio.php`

- [ ] **Step 1: Add Hungarian cards**

Add two entries inside `locales.hu.home.projects.items`, after the existing public web projects:

```php
[
    'tag' => 'Sportoldal + automatizalas',
    'title' => 'TiszaszalkaSE.com',
    'text' => 'Sportegyesuleti weboldal, amely automatikusan lehuzza es megjeleniti az eredmenyeket az MLSZ oldalarol, igy a frissitesek kevesebb kezi munkat igenyelnek.',
    'url' => 'https://tiszaszalkase.com',
    'link' => 'Weboldal megtekintese',
],
[
    'tag' => 'AI + Laravel + automatizalas',
    'title' => 'NapiInfo.com',
    'text' => 'Teljesen automatizalt hiroldal AI- es Laravel-alapokon. A sajat monetizacios es automatizalasi tudasom koncentralt projektje: egy okos rendszer, amely onalloan dolgozik, tanul es folyamatosan epiti a tartalmat.',
    'url' => 'https://napiinfo.com',
    'link' => 'Weboldal megtekintese',
],
```

- [ ] **Step 2: Add English cards**

Add equivalent entries inside `locales.en.home.projects.items`, after the existing public web projects:

```php
[
    'tag' => 'Sports site + automation',
    'title' => 'TiszaszalkaSE.com',
    'text' => 'Sports club website that automatically pulls and displays match results from the MLSZ site, keeping updates current with less manual work.',
    'url' => 'https://tiszaszalkase.com',
    'link' => 'View Website',
],
[
    'tag' => 'AI + Laravel + automation',
    'title' => 'NapiInfo.com',
    'text' => 'A fully automated AI-powered news site built on Laravel. It brings together my current monetization and automation knowledge in a smart system that works, learns, and keeps building content on its own.',
    'url' => 'https://napiinfo.com',
    'link' => 'View Website',
],
```

- [ ] **Step 3: Run focused tests**

Run: `php artisan test --filter=PortfolioProjectsTest`

Expected: PASS.

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`

Expected: PASS.
