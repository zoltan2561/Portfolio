@extends('layouts.randi')
@section('content')
<article class="randi-card status-card">@include('randi.partials.cat')<p class="eyebrow">Egy kis üzenet</p><h1>Most egy pillanatra<br>megállunk.</h1><p class="lead">{{ $message }}</p>@if(!empty($retry))<a class="button primary" href="{{ $retry }}">Vissza a választásaimhoz →</a>@endif</article>
@endsection
