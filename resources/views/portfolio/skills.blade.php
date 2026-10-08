@extends('layouts.portfolio')

@section('content')
    <div class="v2-wrap v2-skills-intro">
        <a href="{{ $homeUrl }}" class="v2-text-link"><span aria-hidden="true">←</span> {{ $page['back_home'] }}</a>
        <h1>{{ $page['hero_title'] }}</h1>
        <p>{{ $page['hero_text'] }}</p>
        <p class="v2-muted">{{ $page['note'] }}</p>
        <nav class="v2-category-nav" aria-label="{{ $page['categories_label'] }}">
            @foreach ($page['groups'] as $group)
                <a href="#{{ $group['id'] }}"><x-portfolio.icon :name="$group['icon']" />{{ $group['nav'] }}</a>
            @endforeach
        </nav>
    </div>
    <div class="v2-wrap v2-skill-grid">
        @foreach ($page['groups'] as $group)
            <section id="{{ $group['id'] }}" class="v2-skill-group {{ isset($group['level']) ? 'v2-learning' : '' }}" aria-labelledby="{{ $group['id'] }}-title">
                <div class="v2-category-title"><x-portfolio.icon :name="$group['icon']" /><h2 id="{{ $group['id'] }}-title">{{ $group['title'] }}</h2></div>
                @isset($group['level'])<span class="v2-level">{{ $group['level'] }}</span>@endisset
                @if ($group['text'])<p>{{ $group['text'] }}</p>@endif
                <ul class="v2-technology-list">
                    @foreach ($group['items'] as $technology)
                        <li><span class="v2-tech-mark" aria-hidden="true">{{ $technology['mark'] }}</span><span>{{ $technology['name'] }}@isset($technology['level'])<small>{{ $technology['level'] }}</small>@endisset</span></li>
                    @endforeach
                </ul>
                @isset($group['note'])<p class="v2-skill-note">{{ $group['note'] }}</p>@endisset
                @isset($group['details'])
                    <details><summary>{{ $page['more_details'] }}</summary><ul>@foreach ($group['details'] as $detail)<li>{{ $detail }}</li>@endforeach</ul></details>
                @endisset
            </section>
        @endforeach
    </div>
@endsection
