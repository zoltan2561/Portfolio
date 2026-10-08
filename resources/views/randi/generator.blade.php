@extends('layouts.randi')
@section('content')
<article class="randi-card">
    @include('randi.partials.cat')
    <p class="eyebrow">egy kis bátorság, névre szólóan</p>
    <h1>Kinek küldjük<br>a meghívót? 💌</h1>
    <p class="lead">Add meg a keresztnevét, és készítek egy személyes linket, amit elküldhetsz neki.</p>
    @if($errors->any())<div class="form-errors" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <form method="post" action="{{ route('randi.create') }}">
        @csrf
        <label class="field-label" for="recipient-name">A meghívott keresztneve</label>
        <input class="field" id="recipient-name" name="recipient_name" placeholder="Például: Anna" maxlength="80" value="{{ old('recipient_name') }}" required autocomplete="off" @if($errors->has('recipient_name')) aria-invalid="true" @endif>
        <button class="button primary full-width">Személyes link készítése <span aria-hidden="true">→</span></button>
    </form>
    <p class="quiet-note">A meghívó 30 napig él. A válasz a megadott névhez tartozó meghívónál jelenik meg.</p>
    <a class="text-button" href="{{ route('randi.admin') }}">Meghívók és válaszok →</a>
</article>
@endsection
