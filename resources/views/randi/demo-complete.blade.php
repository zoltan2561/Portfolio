@extends('layouts.randi')
@section('content')
<p class="demo-badge">Bemutató · nincs adatbázis-mentés</p><article class="randi-card" data-current-step="{{ $payload['decision'] === 'declined' ? 'declined' : 'success' }}">@include('randi.partials.cat')
@if($payload['decision'] === 'declined')<h1>Rendben, köszi, hogy jelezted.</h1><p class="lead">Semmi gond.</p>@else<h1>Hivatalos: van mit várnom. 🥰</h1>@include('randi.partials.summary')@endif
<p class="quiet-note">Ez egy bemutató volt. Nem mentettünk választ.</p><a class="text-button" href="{{ route('randi.demo') }}">Vissza a bemutatóhoz</a></article>
@endsection
