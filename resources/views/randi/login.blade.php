@extends('layouts.randi')
@section('content')
<article class="randi-card login-card"><p class="eyebrow">Zoli kis szervezősarka</p><h1>Meghívók<br>és jó tervek.</h1><p class="lead">Lépj be a személyes kezelőfelületre.</p>
@if(!$configured)<p class="form-errors" role="alert">Az admin még nincs beállítva. A belépés a jelszóhash megadásáig zárva marad.</p>@endif
<form method="post" action="{{ route('randi.admin.authenticate') }}">@csrf<label class="field-label" for="password">Adminjelszó</label><input class="field" type="password" name="password" id="password" required autocomplete="current-password" maxlength="1024" @disabled(!$configured)><p class="field-error" role="alert">{{ $errors->first('password') }}</p><button class="button primary full-width" @disabled(!$configured)>Belépek →</button></form></article>
@endsection
