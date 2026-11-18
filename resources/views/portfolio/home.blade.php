@extends('layouts.portfolio')

@php
    $career = $page['career'];
    $layerIcons = ['software' => 'code', 'infra' => 'network', 'ops' => 'server', 'support' => 'chat', 'hardware' => 'cpu'];
@endphp

@section('content')
    <div class="v2-wrap pz-hero">
        <div class="pz-hero-copy">
            <h1>{{ $page['hero_title'] }}</h1>
            <p class="v2-intro">{{ $page['hero_text'] }}</p>
            <div class="v2-actions">
                <a href="{{ $links['contact'] }}" class="v2-button"><x-portfolio.icon name="mail" />{{ $page['actions']['primary'] }}</a>
                <a href="{{ $assets['cv'] }}" class="v2-button v2-button-secondary" download><x-portfolio.icon name="download" />{{ $page['actions']['secondary'] }}</a>
            </div>
            <ul class="pz-facts">
                @foreach ($page['facts'] as $fact)
                    <li><strong>{{ $fact['value'] }}</strong> {{ $fact['label'] }}</li>
                @endforeach
            </ul>
        </div>
        <figure class="pz-portrait">
            <picture>
                <source type="image/webp" srcset="{{ $assets['profileWebp320'] }} 320w, {{ $assets['profileWebp480'] }} 480w, {{ $assets['profileWebp720'] }} 720w" sizes="(max-width: 760px) 70vw, 400px">
                <img src="{{ $assets['profileFallback'] }}" alt="{{ $page['profile_alt'] }}" width="400" height="480" fetchpriority="high" loading="eager">
            </picture>
            <figcaption>{{ $page['portrait_caption'] }}</figcaption>
        </figure>
    </div>

    <section id="palya" class="v2-section pz-career" aria-labelledby="career-title">
        <div class="v2-wrap">
            <div class="pz-career-head">
                <h2 id="career-title">{{ $career['title'] }}</h2>
                <p>{{ $career['lead'] }}</p>
            </div>
            <div class="pz-career-grid">
                <aside class="pz-stack" aria-labelledby="stack-title" data-stack>
                    <p id="stack-title" class="pz-stack-title">{{ $career['stack_title'] }}</p>
                    <ul class="pz-stack-layers">
                        @foreach ($layerIcons as $layer => $icon)
                            <li class="pz-layer" data-layer="{{ $layer }}"><x-portfolio.icon :name="$icon" /><span>{{ $career['layers'][$layer] }}</span></li>
                        @endforeach
                    </ul>
                    <p class="pz-stack-status" data-stack-status aria-hidden="true"></p>
                </aside>
                <ol id="eletut" class="pz-timeline">
                    @foreach ($career['items'] as $item)
                        <li class="pz-step pz-step-{{ $item['kind'] }}" data-layers="{{ implode(' ', $item['layers']) }}" data-title="{{ $item['title'] }}">
                            <span class="pz-step-marker"><x-portfolio.icon :name="$item['icon']" /></span>
                            <div class="pz-step-body">
                                <h3>{{ $item['title'] }}</h3>
                                <p class="pz-step-place">{{ $item['place'] }}@if (! empty($item['now']))<span class="pz-badge pz-badge-now">{{ $career['now'] }}</span>@elseif ($item['kind'] === 'edu')<span class="pz-badge">{{ $career['learning'] }}</span>@endif</p>
                                <p class="pz-step-text">{{ $item['text'] }}</p>
                                @if ($item['layers'])
                                    <ul class="pz-step-layers" aria-label="{{ $career['layers_label'] }}">
                                        @foreach ($item['layers'] as $layer)
                                            <li data-layer="{{ $layer }}">{{ $career['layers'][$layer] }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
            <p class="pz-career-end">{{ $career['end_note'] }}</p>
        </div>
    </section>

    <section id="tudas" class="v2-section" aria-labelledby="knowledge-title">
        <div class="v2-wrap">
            <h2 id="knowledge-title">{{ $page['knowledge']['title'] }}</h2>
            <div class="pz-knowledge">
                @foreach ($page['knowledge']['cards'] as $card)
                    <article class="v2-tech-card {{ $loop->first ? 'v2-main-focus' : '' }}">
                        <div class="v2-category-title"><x-portfolio.icon :name="$card['icon']" /><h3>{{ $card['title'] }}</h3>@isset($card['level'])<span class="pz-badge pz-badge-now">{{ $card['level'] }}</span>@endisset</div>
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
                            @if ($project['url'])<a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer" tabindex="-1" aria-hidden="true">@endif
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

    <section id="rolam" class="v2-section pz-about" aria-labelledby="about-title">
        <div class="v2-wrap pz-about-grid">
            <div class="pz-about-copy">
                <h2 id="about-title">{{ $page['about']['title'] }}</h2>
                @foreach ($page['about']['paragraphs'] as $paragraph)
                    <x-portfolio.paragraph :paragraph="$paragraph" />
                @endforeach
            </div>
            <ul id="szemlelet" class="pz-approach">
                @foreach ($page['about']['approach'] as $point)
                    <li><x-portfolio.icon :name="$point['icon']" /><div><h3>{{ $point['title'] }}</h3><p>{{ $point['text'] }}</p></div></li>
                @endforeach
            </ul>
        </div>
    </section>

    <section id="kapcsolat" class="v2-section pz-contact-section" aria-labelledby="contact-title">
        <div class="v2-wrap">
            <div class="v2-contact">
                <div>
                    <h2 id="contact-title">{{ $page['contact']['title'] }}</h2>
                    <p>{{ $page['contact']['lead'] }}</p>
                    <a href="mailto:{{ $person['contact']['email'] }}" class="v2-email">{{ $person['contact']['email'] }}</a>
                    <ul class="v2-profiles">
                        <li><a href="{{ $person['contact']['linkedin'] }}" target="_blank" rel="noopener noreferrer"><strong>LinkedIn</strong><span>{{ $page['contact']['profile_link'] }} ↗</span></a></li>
                        <li><a href="{{ $person['contact']['github'] }}" target="_blank" rel="noopener noreferrer"><strong>GitHub</strong><span>{{ $page['contact']['profile_link'] }} ↗</span></a></li>
                        <li><a href="{{ $person['contact']['instagram'] }}" target="_blank" rel="noopener noreferrer"><strong>Instagram</strong><span>@zoltan.ppp ↗</span></a></li>
                        <li><a href="{{ $person['contact']['facebook'] }}" target="_blank" rel="noopener noreferrer"><strong>Facebook</strong><span>facebook.com/ztech20 ↗</span></a></li>
                        <li><a href="{{ $person['contact']['facebook_business'] }}" target="_blank" rel="noopener noreferrer"><strong>Facebook</strong><span>facebook.com/szoftlab ↗</span></a></li>
                    </ul>
                </div>
                <div class="pz-form-panel">
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
                        <textarea id="contact-message" name="message" rows="5" required maxlength="5000">{{ old('message') }}</textarea>
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
