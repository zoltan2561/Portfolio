@extends('layouts.portfolio')

@section('content')
    <div class="foreground" id="rolam">
        <img src="{{ $assets['profile'] }}" alt="{{ $page['profile_alt'] }}" loading="lazy" width="150" height="150">
        <h1>{{ $page['hero_title'] }}</h1>
        <p class="hero-intro">{{ $page['hero_text'] }}</p>
        <p class="hero-note">{{ $page['hero_note'] }}</p>

        <div class="hero-highlights fade-in">
            @foreach ($page['highlights'] as $highlight)
                <span class="hero-pill">{{ $highlight }}</span>
            @endforeach
        </div>

        <div class="hero-proof-panel fade-in" aria-label="{{ $lang === 'hu' ? 'Miért dolgozz velem' : 'Why work with me' }}">
            <div class="hero-proof-copy">
                <span class="section-chip">{{ $page['proof_chip'] }}</span>
                <h3>{{ $page['proof_title'] }}</h3>
                <p>{{ $page['proof_text'] }}</p>
            </div>

            <div class="hero-proof-grid">
                @foreach ($page['proof_cards'] as $proofCard)
                    <article class="hero-proof-card">
                        <h4>{{ $proofCard['title'] }}</h4>
                        <p>{{ $proofCard['text'] }}</p>
                    </article>
                @endforeach
            </div>

            <p class="hero-proof-footer">{{ $page['proof_footer'] }}</p>
        </div>

        <div class="hero-actions">
            <a href="{{ $links['contact'] }}" class="cta-button">{{ $page['actions']['primary'] }}</a>
            <a href="{{ $skillsUrl }}" class="cta-button">{{ $page['actions']['secondary'] }}</a>
            <a href="{{ $links['projects'] }}" class="cta-button">{{ $page['actions']['tertiary'] }}</a>
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

    <section id="kapcsolat">
        <h2>{{ $page['contact']['title'] }}</h2>
        <p class="contact-lead">{{ $page['contact']['lead'] }}</p>

        <div class="contact-info">
            <p>📧 <strong>{{ $page['contact']['email_label'] }}:</strong> <a href="mailto:{{ $person['contact']['email'] }}">{{ $person['contact']['email'] }}</a></p>
            <p>💼 <strong>{{ $page['contact']['linkedin_label'] }}:</strong> <a href="{{ $person['contact']['linkedin'] }}" target="_blank" rel="noopener noreferrer">{{ $page['contact']['linkedin_link'] }}</a></p>
            <p>📷 <strong>{{ $page['contact']['instagram_label'] }}:</strong> <a href="{{ $person['contact']['instagram'] }}" target="_blank" rel="noopener noreferrer">@zoltan.ppp</a></p>
            <p>📘 <strong>{{ $page['contact']['facebook_label'] }}:</strong> <a href="{{ $person['contact']['facebook'] }}" target="_blank" rel="noopener noreferrer">facebook.com/ztech20</a></p>
            <p>📘 <strong>{{ $page['contact']['facebook_label'] }}:</strong> <a href="{{ $person['contact']['facebook_business'] }}" target="_blank" rel="noopener noreferrer">facebook.com/pzinformatika</a></p>

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
            <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
            <input type="text" name="name" placeholder="{{ $page['contact']['name'] }}" value="{{ old('name') }}" required>
            <input type="email" name="email" placeholder="E-mail" value="{{ old('email') }}" required>
            <textarea name="message" rows="5" placeholder="{{ $page['contact']['message'] }}" required>{{ old('message') }}</textarea>
            <button type="submit">{{ $page['contact']['submit'] }}</button>
        </form>
    </section>
@endsection
