@extends('layouts.portfolio')

@section('content')
    @php
    $heroNote = $lang === 'hu'
        ? 'Kevesebb manuális munka, gyorsabb ügyfélút, tisztább admin folyamat az első cél minden projektben.'
        : 'Less manual work, faster client flow, cleaner admin process in every project.';
    $ctaPrimary = $lang === 'hu'
        ? 'Beszéljünk a vállalkozásodról'
        : "Let's align your next project";
    $ctaSecondary = $lang === 'hu' ? 'Mutasd a szakmai hátteret' : 'See technical background';
    $ctaTertiary = $lang === 'hu' ? 'Nézd meg az eddigi munkáimat' : 'See selected projects';
    $contactLead = $lang === 'hu'
        ? 'Írd le, mivel foglalkozol és hol akadtál el. Keress bátran bármelyik felületen: egy ingyenes felmérés keretein belül átnézem az igényeidet, és segítek megtalálni a következő jó lépést.'
        : 'Tell me what you do and where you are stuck. Reach out on any channel: in a free discovery chat I will review your needs and help you find the right next step.';
@endphp
    <div class="foreground" id="rolam">
        <picture>
            <source
                type="image/webp"
                srcset="{{ $assets['profileWebp320'] }} 320w, {{ $assets['profileWebp480'] }} 480w, {{ $assets['profileWebp720'] }} 720w"
                sizes="220px">
            <img
                src="{{ $assets['profileFallback'] }}"
                alt="{{ $page['profile_alt'] }}"
                loading="lazy"
                width="150"
                height="150">
        </picture>
        <h1>{{ $page['hero_title'] }}</h1>
        <p class="hero-intro">{{ $page['hero_text'] }}</p>
        <p class="hero-note">{{ $heroNote }}</p>

        <div class="hero-highlights fade-in">
            @foreach ($page['highlights'] as $highlight)
                <span class="hero-pill">{{ $highlight }}</span>
            @endforeach
        </div>

        <div class="hero-proof-panel fade-in" aria-label="{{ $lang === 'hu' ? 'Miért dolgozz velem' : 'Why work with me' }}">
            <div class="hero-proof-copy">
                <span class="section-chip">{{ $page['proof_chip'] }}</span>
                <h2>{{ $page['proof_title'] }}</h2>
                <p>{{ $page['proof_text'] }}</p>
            </div>

            <div class="hero-proof-grid">
                @foreach ($page['proof_cards'] as $proofCard)
                    <article class="hero-proof-card">
                        <h3>{{ $proofCard['title'] }}</h3>
                        <p>{{ $proofCard['text'] }}</p>
                    </article>
                @endforeach
            </div>

            <p class="hero-proof-footer">{{ $page['proof_footer'] }}</p>
        </div>

        <div class="hero-actions">
            <a href="{{ $links['contact'] }}" class="cta-button">{{ $ctaPrimary }}</a>
            <a href="{{ $skillsUrl }}" class="cta-button">{{ $ctaSecondary }}</a>
            <a href="{{ $links['projects'] }}" class="cta-button">{{ $ctaTertiary }}</a>
        </div>
    </div>

    <section id="szolgaltatasok">
        <h2>{{ $page['services']['title'] }}</h2>
        <p class="section-lead">{{ $page['services']['lead'] }}</p>

        <div class="services">
            @foreach ($page['services']['items'] as $service)
                <div class="service">
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="workflow-section fade-in" aria-labelledby="workflow-title">
        <span class="section-chip">{{ $lang === 'hu' ? 'FOLYAMAT' : 'PROCESS' }}</span>
        <h2 id="workflow-title">{{ $page['workflow']['title'] }}</h2>
        <p class="section-lead">{{ $page['workflow']['lead'] }}</p>

        <div class="workflow-steps">
            @foreach ($page['workflow']['steps'] as $step)
                <article class="workflow-step">
                    <span class="workflow-step-number">{{ $loop->iteration }}</span>
                    <div>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['text'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="audience-section" aria-labelledby="audience-title">
        <span class="section-chip">{{ $lang === 'hu' ? 'KINEK' : 'FOR WHO' }}</span>
        <h2 id="audience-title">{{ $page['audience']['title'] }}</h2>
        <p class="section-lead">{{ $page['audience']['lead'] }}</p>

        <div class="audience-grid">
            @foreach ($page['audience']['cards'] as $audienceCard)
                <article class="audience-card fade-in">
                    <h3>{{ $audienceCard['title'] }}</h3>
                    <p>{{ $audienceCard['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section id="projektek">
        <h2>{{ $page['projects']['title'] }}</h2>
        <p class="section-lead">{{ $page['projects']['lead'] }}</p>

        <div class="projects-shell">
            <div class="projects-toolbar">
                <p class="projects-hint">{{ $page['projects']['hint'] }}</p>
                <div class="project-controls" aria-label="{{ $page['projects']['controls_label'] }}">
                    <button type="button" class="project-control" data-projects-nav="prev" aria-label="{{ $page['projects']['prev_label'] }}">&lsaquo;</button>
                    <button type="button" class="project-control" data-projects-nav="next" aria-label="{{ $page['projects']['next_label'] }}">&rsaquo;</button>
                </div>
            </div>

            <div class="projects" data-projects-track>
                @foreach ($page['projects']['items'] as $project)
                    <article class="project">
                        <span class="project-tag">{{ $project['tag'] }}</span>
                        <h3>{{ $project['title'] }}</h3>
                        <p>{{ $project['text'] }}</p>

                        @if ($project['url'] && $project['link'])
                            <a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer">{{ $project['link'] }}</a>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('portfolio.partials.faq')

    <section id="kapcsolat">
        <h2>{{ $page['contact']['title'] }}</h2>
        <p class="contact-lead">{{ $contactLead }}</p>

        <div class="contact-info">
            <p><strong>{{ $page['contact']['email_label'] }}:</strong> <a href="mailto:{{ $person['contact']['email'] }}">{{ $person['contact']['email'] }}</a></p>
            <p><strong>{{ $page['contact']['linkedin_label'] }}:</strong> <a href="{{ $person['contact']['linkedin'] }}" target="_blank" rel="noopener noreferrer">{{ $page['contact']['linkedin_link'] }}</a></p>
            <p><strong>{{ $page['contact']['github_label'] }}:</strong> <a href="{{ $person['contact']['github'] }}" target="_blank" rel="noopener noreferrer">{{ $page['contact']['github_link'] }}</a></p>
            <p><strong>{{ $page['contact']['instagram_label'] }}:</strong> <a href="{{ $person['contact']['instagram'] }}" target="_blank" rel="noopener noreferrer">@zoltan.ppp</a></p>
            <p><strong>{{ $page['contact']['facebook_label'] }}:</strong> <a href="{{ $person['contact']['facebook'] }}" target="_blank" rel="noopener noreferrer">facebook.com/ztech20</a></p>
            <p><strong>{{ $page['contact']['facebook_label'] }}:</strong> <a href="{{ $person['contact']['facebook_business'] }}" target="_blank" rel="noopener noreferrer">facebook.com/pzinformatika</a></p>

            @if (session('contact_status') === 'success')
                <p class="form-success">{{ $page['contact']['success'] }}</p>
            @elseif (session('contact_status') === 'error' || $errors->any())
                <p class="form-error">{{ $page['contact']['error'] }}</p>
            @endif
        </div>

        <form class="contact-form" method="POST" action="{{ route('contact.send') }}">
            @csrf
            <h3>{{ $page['contact']['form_title'] }}</h3>

            <input type="hidden" name="lang" value="{{ $lang }}">
            <label class="sr-only" for="contact-website">Website</label>
            <input id="contact-website" type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
            <label class="sr-only" for="contact-name">{{ $page['contact']['name'] }}</label>
            <input id="contact-name" type="text" name="name" placeholder="{{ $page['contact']['name'] }}" value="{{ old('name') }}" required>
            <label class="sr-only" for="contact-email">E-mail</label>
            <input id="contact-email" type="email" name="email" placeholder="E-mail" value="{{ old('email') }}" required>
            <label class="sr-only" for="contact-message">{{ $page['contact']['message'] }}</label>
            <textarea id="contact-message" name="message" rows="5" placeholder="{{ $page['contact']['message'] }}" required>{{ old('message') }}</textarea>
            <button type="submit">{{ $page['contact']['submit'] }}</button>
        </form>
    </section>
@endsection
