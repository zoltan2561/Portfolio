@extends('layouts.randi')
@section('content')
@php
    $value = fn($name, $default = '') => $draft[$name] ?? $default;
    $data = array_intersect_key($draft, array_flip(['date_mode', 'preferred_date', 'time_window', 'activity', 'custom_activity', 'note']));
    $config = ['demo' => $demo, 'identity' => $identity, 'key' => $key, 'step' => $step, 'draft' => $data, 'calendar' => $calendar, 'responseUrl' => $responseUrl, 'activities' => config('randi.activities'), 'timeWindows' => config('randi.time_windows')];
@endphp
@if($demo)<p class="demo-badge"><span aria-hidden="true">✧</span> Bemutató · a válaszodat nem mentjük el</p>@endif
<article class="randi-card" id="randi-app" data-initial-step="{{ $step }}" data-current-step="{{ $step }}">
    <div class="card-stamp" aria-hidden="true">csak neked <span>♡</span></div>
    <nav class="step-indicator" aria-label="A randiterv lépései" @if(!in_array($step, ['date', 'activity', 'review'])) hidden @endif>
        <span data-progress="date"><b>1</b> Időpont</span><span data-progress="activity"><b>2</b> Program</span><span data-progress="review"><b>3</b> Terv</span>
    </nav>
    <div id="randi-errors" class="form-errors" role="alert" @if(!$errors->any()) hidden @endif>
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    <form id="randi-form" action="{{ $formUrl }}" method="post" novalidate>
        @csrf
        <input type="hidden" name="submission_key" value="{{ $key }}">
        <section data-step="invite" @if($step !== 'invite') hidden @endif>
            @include('randi.partials.cat')
            <p class="eyebrow">{{ $invite->recipient_name ? 'Szia, '.$invite->recipient_name.'!' : 'Szia!' }} Van egy fontos kérdésem… 👀</p>
            <h1 tabindex="-1">Eljössz velem<br>egy randira? <span class="title-heart" aria-hidden="true">♡</span></h1>
            <p class="lead">Ígérem, élőben kevesebb gombot<br class="desktop-break"> kell megnyomnod.</p>
            @if($invite->intro_message)<p class="personal-note">{{ $invite->intro_message }}</p>@endif
            <div class="answer-arena" id="answer-arena">
                <button class="button primary yes-button" type="submit" name="action" value="start" data-go="joy">Igen, menjünk 🥰 <span aria-hidden="true">→</span></button>
                <button class="button secondary no-button" id="playful-no" type="submit" formaction="{{ $responseUrl }}" name="decision" value="declined" data-decline>Nem 🙈</button>
            </div>
            <p class="playful-message" id="playful-message" aria-live="polite"></p>
            <button class="text-button decline-button" type="submit" formaction="{{ $responseUrl }}" name="decision" value="declined" data-decline>Most inkább kihagyom</button>
            <p class="signature">egy kis bátorsággal, <span>{{ $invite->sender_name }}</span></p>
        </section>
        <section data-step="joy" @if($step !== 'joy') hidden @endif>
            @include('randi.partials.cat')
            <p class="eyebrow">Ezt most elteszem a jó pillanatok közé.</p>
            <h1 tabindex="-1">Na jó, most mosolygok a telefonomra. 🥹</h1>
            <p class="lead">Ez határozottan jó fordulat a napomban.</p>
            <button class="button primary full-width" type="submit" name="action" value="date" data-go="date">Akkor találjunk egy időpontot <span aria-hidden="true">→</span></button>
        </section>
        <section data-step="date" @if($step !== 'date') hidden @endif>
            <p class="eyebrow">01 / a mikor</p><h1 tabindex="-1">Mikor lenne<br>jó neked? 📅</h1>
            <p class="lead">Válassz egy napot. A többit kitaláljuk.</p>
            <fieldset class="date-modes"><legend class="sr-only">Az időpont megadása</legend>
                <label class="choice mode-choice"><input type="radio" name="date_mode" value="specific_date" @checked($value('date_mode', 'specific_date') === 'specific_date')><span>Egy nap már eszembe jutott</span></label>
                <label class="choice mode-choice"><input type="radio" name="date_mode" value="discuss_later" @checked($value('date_mode') === 'discuss_later')><span>Még egyeztessük</span></label>
            </fieldset>
            <div id="specific-date-fields">
                <label class="field-label" for="preferred-date">Melyik nap?</label>
                <input id="preferred-date" class="field" type="date" name="preferred_date" value="{{ $value('preferred_date') }}" min="{{ $calendar['today'] }}" max="{{ $calendar['max'] }}" aria-describedby="error-preferred_date">
                <p class="field-error" id="error-preferred_date" data-error="preferred_date">{{ $errors->first('preferred_date') }}</p>
                <div class="quick-dates" id="quick-dates" hidden><button type="button" data-weekday="5">Péntek</button><button type="button" data-weekday="6">Szombat</button><button type="button" data-weekday="0">Vasárnap</button></div>
                <fieldset class="time-choices"><legend>Mikor érnél rá?</legend>
                    @foreach(config('randi.time_windows') as $id => $window)
                    <label class="choice"><input type="radio" name="time_window" value="{{ $id }}" @checked($value('time_window') === $id)><span>{{ $window[0] }} @if($window[1])<small>{{ $window[1] }}</small>@endif</span></label>
                    @endforeach
                </fieldset>
                <p class="field-error" id="error-time_window" data-error="time_window">{{ $errors->first('time_window') }}</p>
            </div>
            <p class="quiet-note">Ez egy időpontötlet. A részleteket még együtt egyeztetjük.</p>
            <div class="step-actions"><button class="text-button" type="submit" name="action" value="start" data-go="joy">← Vissza</button><button class="button primary" type="submit" name="action" value="activity" data-go="activity">Jöhet a program <span aria-hidden="true">→</span></button></div>
        </section>
        <section data-step="activity" @if($step !== 'activity') hidden @endif>
            <p class="eyebrow">02 / a mit</p><h1 tabindex="-1">És mihez lenne<br>kedved? ✨</h1>
            <p class="lead">A lényeg a társaság,<br>de a programot rád bízom.</p>
            <fieldset class="activity-choices"><legend class="sr-only">Egy programot válassz</legend>
                @foreach(config('randi.activities') as $id => $activity)
                <label class="activity-choice"><input type="radio" name="activity" value="{{ $id }}" @checked($value('activity') === $id)><span class="activity-emoji" aria-hidden="true">{{ $activity[0] }}</span><span class="activity-copy"><strong>{{ $activity[1] }}</strong><small>{{ $activity[2] }}</small></span><span class="choice-check" aria-hidden="true">✓</span></label>
                @endforeach
            </fieldset>
            <p class="field-error" data-error="activity">{{ $errors->first('activity') }}</p>
            <div id="custom-activity-field"><label class="field-label" for="custom-activity">A saját ötleted</label><textarea class="field" id="custom-activity" name="custom_activity" rows="2" maxlength="200" aria-describedby="error-custom_activity">{{ $value('custom_activity') }}</textarea><p class="field-error" id="error-custom_activity" data-error="custom_activity">{{ $errors->first('custom_activity') }}</p></div>
            <label class="field-label" for="note">Valamit még üzennél? 💬 <span>(nem kötelező)</span></label><textarea class="field" name="note" id="note" maxlength="280" rows="3" placeholder="Például: a kávémat sok tejjel szeretem." aria-describedby="error-note">{{ $value('note') }}</textarea><p class="field-error" id="error-note" data-error="note">{{ $errors->first('note') }}</p>
            <div class="step-actions"><button class="text-button" type="submit" name="action" value="edit_date" data-go="date">← Vissza</button><button class="button primary" type="submit" name="action" value="review" data-go="review">Nézzük a tervet <span aria-hidden="true">→</span></button></div>
        </section>
        <section data-step="review" @if($step !== 'review') hidden @endif>
            <p class="eyebrow">03 / a közös terv</p><h1 tabindex="-1">Ezt a tervet nehéz<br>lesz nem várni. 💌</h1>
            <p class="lead">Még egy pillantás, és mehet.</p>
            @include('randi.partials.summary', ['payload' => $data])
            <div class="edit-actions"><button type="submit" class="text-button" name="action" value="edit_date" data-go="date">Időpont módosítása</button><button type="submit" class="text-button" name="action" value="edit_activity" data-go="activity">Program módosítása</button></div>
            <p class="privacy-note">{{ $demo ? 'Ez csak egy bemutató. A választásaid nem kerülnek adatbázisba.' : 'A választásaidat elmentem, hogy meg tudjuk szervezni a randit. '.$invite->sender_name.' a saját kezelőfelületén látja őket.' }}</p>
            <button class="button primary full-width" type="submit" formaction="{{ $responseUrl }}" name="decision" value="accepted" data-send>Mehet a randiterv 💌</button>
            <button class="text-button decline-button" type="submit" formaction="{{ $responseUrl }}" name="decision" value="declined" data-decline>Most inkább kihagyom</button>
        </section>
        <section data-step="success" @if($step !== 'success') hidden @endif>
            @include('randi.partials.cat')
            <p class="eyebrow" data-success-label>{{ $demo ? 'A demó végére értél · nincs mentés' : 'A kis tervünk, elmentve.' }}</p>
            <h1 tabindex="-1">Hivatalos:<br>van mit várnom. 🥰</h1>
            <p class="lead" data-success-copy>{{ $demo ? 'Éles meghívóban itt lenne a mentett válaszod. Ebben a bemutatóban nem mentettünk semmit.' : 'Elmentettem, amit választottál. A részleteket még megbeszéljük.' }}</p>
            @include('randi.partials.summary', ['payload' => $data])
            <p class="closing-note">Addig is gyakorlom, hogyan legyek lazának tűnően izgatott.</p>
            <p class="signature">{{ $invite->sender_name }} <span aria-hidden="true">♡</span></p>
        </section>
        <section data-step="declined" @if($step !== 'declined') hidden @endif>
            @include('randi.partials.cat')
            <h1 tabindex="-1">Rendben, köszi,<br>hogy jelezted. 🙂</h1><p class="lead">Semmi gond.</p>
            <p class="quiet-note">{{ $demo ? 'Bemutató: ezt a választ nem mentettük el.' : 'A válaszodat elmentettem.' }}</p>
        </section>
    </form>
    <div id="confetti" aria-hidden="true"></div>
</article>
<script type="application/json" id="randi-config">{!! json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
