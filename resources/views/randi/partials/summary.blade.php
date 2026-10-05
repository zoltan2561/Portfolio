@php
    $date = $payload['preferred_date'] ?? null;
    $time = config('randi.time_windows')[$payload['time_window'] ?? ''] ?? null;
    $activity = config('randi.activities')[$payload['activity'] ?? ''] ?? null;
@endphp
<dl class="plan-summary">
    <div><dt><span aria-hidden="true">◷</span> Mikor?</dt><dd data-summary="date">{{ ($payload['date_mode'] ?? '') === 'discuss_later' ? 'Még egyeztetjük' : ($date ? \Carbon\CarbonImmutable::parse($date, 'Europe/Budapest')->locale('hu')->isoFormat('YYYY. MMMM D., dddd') : 'Válassz egy napot') }}</dd><dd class="summary-detail" data-summary="time">{{ $time ? implode(' · ', array_filter([$time[0], $time[1]])) : '' }}</dd></div>
    <div><dt><span aria-hidden="true">✧</span> Mi legyen a program?</dt><dd data-summary="activity">{{ $activity ? $activity[0].' '.$activity[1] : 'Válassz egy programot' }}</dd><dd class="summary-detail" data-summary="custom">{{ $payload['custom_activity'] ?? '' }}</dd></div>
    <div data-summary-note @if(empty($payload['note'])) hidden @endif><dt><span aria-hidden="true">♡</span> Az üzeneted</dt><dd data-summary="note">{{ $payload['note'] ?? '' }}</dd></div>
</dl>
