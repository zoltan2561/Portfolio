@extends('layouts.portfolio')

@section('content')
    <div class="v2-wrap v2-hero">
        <div class="v2-hero-copy">
            <p class="v2-eyebrow">{{ $page['eyebrow'] }}</p>
            <h1>{{ $page['hero_title'] }}</h1>
            <p class="v2-intro">{{ $page['hero_text'] }}</p>
            <div class="v2-actions">
                <a href="{{ $links['about'] }}" class="v2-button">{{ $page['actions']['primary'] }}</a>
                <a href="{{ $links['projects'] }}" class="v2-button v2-button-secondary">{{ $page['actions']['secondary'] }}</a>
            </div>
            <dl class="v2-facts">
                @foreach ($page['facts'] as $fact)
                    <div>
                        <x-portfolio.icon :name="$fact['icon']" />
                        <dt>{{ $fact['value'] }}</dt>
                        <dd>{{ $fact['label'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
        <div class="v2-portrait">
            <picture>
                <source type="image/webp" srcset="{{ $assets['profileWebp320'] }} 320w, {{ $assets['profileWebp480'] }} 480w, {{ $assets['profileWebp720'] }} 720w" sizes="(max-width: 760px) 280px, 380px">
                <img src="{{ $assets['profileFallback'] }}" alt="{{ $page['profile_alt'] }}" width="380" height="380" fetchpriority="high" loading="eager">
            </picture>
            <ul class="v2-portrait-labels">
                @foreach ($page['portrait_labels'] as $label)
                    <li><x-portfolio.icon :name="$label['icon']" /><span>{{ $label['label'] }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <section id="rolam" class="v2-section" aria-labelledby="about-title">
        <div class="v2-wrap">
            <div class="v2-about-top">
                <div>
                    <h2 id="about-title">{{ $page['about']['title'] }}</h2>
                    <x-portfolio.paragraph :paragraph="$page['about']['paragraphs'][0]" />
                    <div class="v2-about-story">
                        @foreach (array_slice($page['about']['paragraphs'], 1) as $paragraph)
                            <x-portfolio.paragraph :paragraph="$paragraph" />
                        @endforeach
                    </div>
                </div>
                <ol id="eletut" class="v2-milestones">
                    @foreach ($page['about']['milestones'] as $milestone)
                        <li>
                            <span class="v2-milestone-icon"><x-portfolio.icon :name="$milestone['icon']" /></span>
                            <div><h3>{{ $milestone['title'] }}</h3><p>{{ $milestone['text'] }}</p></div>
                        </li>
                    @endforeach
                </ol>
            </div>
            <div class="v2-experience">
                <x-portfolio.icon name="briefcase" />
                <div><h3>{{ $page['about']['experience']['title'] }}</h3><p>{{ $page['about']['experience']['areas'] }}</p></div>
            </div>
            <div id="szemlelet" class="v2-approach">
                @foreach ($page['about']['approach'] as $point)
                    <div><x-portfolio.icon :name="$point['icon']" /><h3>{{ $point['title'] }}</h3><p>{{ $point['text'] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="tudas" class="v2-section" aria-labelledby="knowledge-title">
        <div class="v2-wrap">
            <h2 id="knowledge-title">{{ $page['knowledge']['title'] }}</h2>
            <div class="v2-tech-grid">
                @foreach ($page['knowledge']['cards'] as $card)
                    <article class="v2-tech-card {{ $loop->first ? 'v2-main-focus' : '' }}">
                        <div class="v2-category-title"><x-portfolio.icon :name="$card['icon']" /><h3>{{ $card['title'] }}</h3></div>
                        @isset($card['level'])<span class="v2-level">{{ $card['level'] }}</span>@endisset
                        <p>{{ $card['text'] }}</p>
                        <ul class="v2-tags">@foreach ($card['labels'] as $label)<li>{{ $label }}</li>@endforeach</ul>
                    </article>
                @endforeach
            </div>
            <a class="v2-text-link" href="{{ $skillsUrl }}">{{ $page['knowledge']['button'] }} <span aria-hidden="true">→</span></a>
        </div>
    </section>

    <section id="projektek" class="v2-section" aria-labelledby="projects-title">
        <div class="v2-wrap">
            <h2 id="projects-title">{{ $page['projects']['title'] }}</h2>
            <div class="v2-projects">
                @foreach ($page['projects']['items'] as $project)
                    <article class="v2-project {{ $loop->first ? 'v2-project-featured' : '' }}">
                        <div class="v2-project-image">
                            @if ($project['url'])<a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer">@endif
                                <img src="{{ asset($project['image']) }}" alt="{{ $project['image_alt'] }}" width="768" height="432" loading="lazy" decoding="async">
                            @if ($project['url'])</a>@endif
                        </div>
                        <div class="v2-project-copy">
                            <h3>@if ($project['url'])<a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer">{{ $project['title'] }} <span aria-hidden="true">↗</span></a>@else{{ $project['title'] }}@endif</h3>
                            <p>{{ $project['text'] }}</p>
                            <ul class="v2-tags">@foreach ($project['labels'] as $label)<li>{{ $label }}</li>@endforeach</ul>
                        </div>
                    </article>
                @endforeach
            </div>
            <details class="v2-more-projects">
                <summary>{{ $page['projects']['more_title'] }}</summary>
                <div class="v2-reference-grid">
                    @foreach ($references as $reference)
                        <article class="v2-reference">
                            <x-portfolio.icon :name="$reference['icon']" />
                            <div>
                                <h3><a href="{{ $reference['url'] }}" target="_blank" rel="noopener noreferrer">{{ $reference['display_title'] }} <span aria-hidden="true">↗</span></a></h3>
                                <p>{{ $reference['display_text'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </details>
            <p class="v2-project-note">{{ $page['projects']['closing'] }}</p>
        </div>
    </section>

    <section id="kapcsolat" class="v2-section" aria-labelledby="contact-title">
        <div class="v2-wrap">
            <div class="v2-contact">
                <div>
                    <h2 id="contact-title">{{ $page['contact']['title'] }}</h2>
                    <p>{{ $page['contact']['lead'] }}</p>
                    <a href="mailto:{{ $person['contact']['email'] }}" class="v2-email">{{ $person['contact']['email'] }}</a>
                    <div class="v2-actions"><a href="mailto:{{ $person['contact']['email'] }}" class="v2-button">{{ $page['contact']['button'] }}</a></div>
                    <ul class="v2-profiles">
                        <li><a href="{{ $person['contact']['linkedin'] }}" target="_blank" rel="noopener noreferrer"><strong>LinkedIn</strong><span>{{ $page['contact']['profile_link'] }} ↗</span></a></li>
                        <li><a href="{{ $person['contact']['github'] }}" target="_blank" rel="noopener noreferrer"><strong>GitHub</strong><span>{{ $page['contact']['profile_link'] }} ↗</span></a></li>
                        <li><a href="{{ $person['contact']['instagram'] }}" target="_blank" rel="noopener noreferrer"><strong>Instagram</strong><span>@zoltan.ppp ↗</span></a></li>
                        <li><a href="{{ $person['contact']['facebook'] }}" target="_blank" rel="noopener noreferrer"><strong>Facebook</strong><span>facebook.com/ztech20 ↗</span></a></li>
                        <li><a href="{{ $person['contact']['facebook_business'] }}" target="_blank" rel="noopener noreferrer"><strong>Facebook</strong><span>facebook.com/szoftlab ↗</span></a></li>
                    </ul>
                </div>
                <div>
                    @if (session('contact_status') === 'success')
                        <p class="form-success" role="status">{{ $page['contact']['success'] }}</p>
                    @elseif (session('contact_status') === 'error' || $errors->any())
                        <p class="form-error" role="alert">{{ $page['contact']['error'] }}</p>
                    @endif
                    <form class="contact-form v2-form" method="POST" action="{{ route('contact.send') }}">
                        @csrf
                        <h3>{{ $page['contact']['form_title'] }}</h3>
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div hidden aria-hidden="true">
                            <label for="contact-website">Website</label>
                            <input id="contact-website" type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <label for="contact-name">{{ $page['contact']['name'] }}</label>
                        <input id="contact-name" type="text" name="name" autocomplete="name" value="{{ old('name') }}" required maxlength="255">
                        <label for="contact-email">{{ $page['contact']['email'] }}</label>
                        <input id="contact-email" type="email" name="email" autocomplete="email" value="{{ old('email') }}" required maxlength="255">
                        <label for="contact-message">{{ $page['contact']['message'] }}</label>
                        <textarea id="contact-message" name="message" rows="4" required maxlength="5000">{{ old('message') }}</textarea>
                        <button type="submit" class="v2-button">{{ $page['contact']['submit'] }}</button>
                    </form>
                </div>
            </div>
            <aside class="v2-business">
                <div><h3>{{ $page['contact']['business']['title'] }}</h3><p>{{ $page['contact']['business']['text'] }}</p></div>
                <a href="{{ config('portfolio.szoftlab_url') }}" class="v2-text-link" target="_blank" rel="noopener noreferrer">{{ $page['contact']['business']['button'] }}</a>
            </aside>
        </div>
    </section>
@endsection
