@props(['name'])
<svg {{ $attributes->class(['v2-icon']) }} viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @foreach (config('portfolio_icons.'.$name, config('portfolio_icons.code')) as $path)
        <path d="{{ $path }}" />
    @endforeach
</svg>
