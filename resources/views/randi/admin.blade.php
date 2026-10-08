@extends('layouts.randi')
@section('body-class', 'randi-admin')
@section('content')
<header class="admin-header"><div><p class="eyebrow">Zoli kis szervezősarka</p><h1>Meghívók &amp; randitervek</h1></div><div class="admin-nav"><a class="button secondary" href="{{ route('randi.generator') }}">Gyors meghívó</a><a class="button secondary" href="{{ route('randi.demo') }}">Demó megnyitása</a><form method="post" action="{{ route('randi.admin.logout') }}">@csrf<button class="text-button">Kijelentkezés</button></form></div></header>
@if(session('status'))<p class="notice" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="form-errors" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="admin-grid">
    <section class="randi-card admin-create"><p class="eyebrow">egy új kezdet</p><h2>Új meghívó 💌</h2><p class="quiet-note">Személyes, egyszerű, csak neki.</p><form method="post" action="{{ route('randi.admin.create') }}">@csrf
    <label class="field-label" for="recipient-name">Keresztnév <span>(nem kötelező)</span></label><input class="field" name="recipient_name" id="recipient-name" maxlength="80" value="{{ old('recipient_name') }}">
    <label class="field-label" for="sender-name">Feladó neve</label><input class="field" name="sender_name" id="sender-name" maxlength="80" value="{{ old('sender_name', 'Zoli') }}" required>
    <label class="field-label" for="intro-message">Személyes bevezető <span>(nem kötelező)</span></label><textarea class="field" name="intro_message" id="intro-message" maxlength="240" rows="3" placeholder="Ezt most egy kicsit bátrabban küldöm, mint ahogy írtam…">{{ old('intro_message') }}</textarea>
    <label class="field-label" for="expires-days">Hány napig éljen a link?</label><input class="field" type="number" name="expires_days" id="expires-days" min="1" max="90" value="{{ old('expires_days', 30) }}" required>
    <button class="button primary full-width">Személyes link készítése →</button></form><p class="privacy-note">A titkos link csak létrehozáskor másolható. A címzettnek a személyes linket küldd, a demó nem ment választ.</p></section>
    <section class="admin-invites" aria-label="Korábbi meghívók">
    <div class="list-heading"><h2>A meghívóid</h2><span>{{ $invites->total() }} meghívó</span></div>
    @forelse($invites as $invite)
    @php
        $response = $responses[$invite->id] ?? null;
        $expired = $invite->expires_at <= \Carbon\CarbonImmutable::now('UTC')->format('Y-m-d H:i:s');
        $status = $response ? ($response->decision === 'accepted' ? 'Van randiterv 🥰' : 'Most kihagyja') : ($invite->revoked_at ? 'Visszavonva' : ($expired ? 'Lejárt' : 'Válaszra vár'));
    @endphp
    <article class="invite-row"><div class="invite-row-heading"><h3>{{ $invite->recipient_name ?: 'Név nélküli meghívó' }}</h3><span class="status-pill {{ $response && $response->decision === 'accepted' ? 'accepted' : '' }}">{{ $status }}</span></div>
    <p class="quiet-note">{{ $invite->sender_name }} meghívója · lejár: {{ \Carbon\CarbonImmutable::parse($invite->expires_at, 'UTC')->setTimezone('Europe/Budapest')->format('Y. m. d. H:i') }}</p>
    @if($invite->revoked_at)<p class="field-error">A link visszavonva.</p>@endif
    @if($invite->intro_message)<p class="admin-intro">{{ $invite->intro_message }}</p>@endif
    @if($response)<details><summary>Válasz és részletek</summary><p class="quiet-note">Beküldve: {{ \Carbon\CarbonImmutable::parse($response->submitted_at, 'UTC')->setTimezone('Europe/Budapest')->format('Y. m. d. H:i') }} (magyar idő)</p>@if($response->decision === 'accepted')@include('randi.partials.summary', ['payload' => (array) $response])@else<p>Elutasította a meghívást.</p>@endif</details>@endif
    <div class="invite-tools">@if(!$invite->revoked_at && !$expired && !$response)<form method="post" action="{{ route('randi.admin.revoke', $invite->id) }}">@csrf<button class="text-button">Link visszavonása</button></form>@endif
    <details class="delete-confirm"><summary>Végleges törlés</summary><p>A meghívó és a válasza is végleg törlődik. Ez nem vonható vissza.</p><form method="post" action="{{ route('randi.admin.delete', $invite->id) }}">@csrf<label class="field-label" for="delete-{{ $invite->id }}">Megerősítés: írd be, hogy TORLES</label><input class="field" name="confirmation" id="delete-{{ $invite->id }}" required autocomplete="off"><button class="button danger">Végleg törlöm</button></form></details></div></article>
    @empty<div class="empty-state"><span aria-hidden="true">✉</span><h3>Innen indul az első meghívó.</h3><p>Készíts egy személyes linket. A válasz majd itt vár.</p></div>@endforelse
    @if($invites->hasPages())<nav class="pagination" aria-label="Meghívók lapozása">@if($invites->previousPageUrl())<a class="text-button" href="{{ $invites->previousPageUrl() }}">← Előző</a>@endif<span>{{ $invites->currentPage() }} / {{ $invites->lastPage() }}</span>@if($invites->nextPageUrl())<a class="text-button" href="{{ $invites->nextPageUrl() }}">Következő →</a>@endif</nav>@endif
    </section>
</div>
@endsection
