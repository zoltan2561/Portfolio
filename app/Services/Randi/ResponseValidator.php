<?php

namespace App\Services\Randi;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ResponseValidator
{
    public function calendar(): array
    {
        $now = CarbonImmutable::now('Europe/Budapest');

        return ['today' => $now->toDateString(), 'max' => $now->addDays(60)->toDateString(), 'hour' => $now->hour];
    }

    public function date(array $input): array
    {
        $calendar = $this->calendar();
        $data = Validator::make($input, [
            'date_mode' => ['required', Rule::in(['specific_date', 'discuss_later'])],
            'preferred_date' => ['exclude_unless:date_mode,specific_date', 'required', 'date_format:Y-m-d', 'after_or_equal:'.$calendar['today'], 'before_or_equal:'.$calendar['max']],
            'time_window' => ['exclude_unless:date_mode,specific_date', 'required', Rule::in(array_keys(config('randi.time_windows')))],
        ], [
            'date_mode.required' => 'Válassz egy napot, vagy jelöld, hogy még egyeztessük.',
            'date_mode.in' => 'Válassz az időpont megadásának két módja közül.',
            'preferred_date.required' => 'Válassz egy napot.',
            'preferred_date.date_format' => 'Adj meg egy valóban létező napot.',
            'preferred_date.after_or_equal' => 'Múltbeli napot már nem tudunk választani.',
            'preferred_date.before_or_equal' => 'A következő 60 napból válassz egyet.',
            'time_window.required' => 'Válassz egy idősávot.',
            'time_window.in' => 'Válassz a megadott idősávok közül.',
        ])->validate();

        if ($data['date_mode'] === 'discuss_later') {
            return ['date_mode' => 'discuss_later', 'preferred_date' => null, 'time_window' => null];
        }

        $end = config('randi.time_windows')[$data['time_window']][2];
        if ($data['preferred_date'] === $calendar['today'] && $end !== null && $calendar['hour'] >= $end) {
            throw ValidationException::withMessages(['time_window' => 'Ez az idősáv mára már elmúlt. Válassz egy későbbit.']);
        }

        return $data;
    }

    public function activity(array $input): array
    {
        $data = Validator::make($input, [
            'activity' => ['required', Rule::in(array_keys(config('randi.activities')))],
            'custom_activity' => ['exclude_unless:activity,custom', 'required', 'string', 'max:200'],
            'note' => ['nullable', 'string', 'max:280'],
        ], [
            'activity.required' => 'Válassz egy programot, vagy írd le a saját ötleted.',
            'activity.in' => 'Válassz a megadott programok közül.',
            'custom_activity.required' => 'Meséld el a saját programötleted.',
            'custom_activity.string' => 'A saját ötlet szöveg legyen.',
            'custom_activity.max' => 'A saját ötlet legfeljebb 200 karakter lehet.',
            'note.string' => 'Az üzenet szöveg legyen.',
            'note.max' => 'Az üzenet legfeljebb 280 karakter lehet.',
        ])->validate();

        return ['activity' => $data['activity'], 'custom_activity' => $data['activity'] === 'custom' ? trim($data['custom_activity']) : null, 'note' => isset($data['note']) ? trim($data['note']) : null];
    }

    public function normalize(array $input): array
    {
        $decision = Validator::make($input, ['decision' => ['required', Rule::in(['accepted', 'declined'])]], [
            'decision.required' => 'Jelezd, hogy elfogadod-e a meghívást.', 'decision.in' => 'Érvénytelen válasz.',
        ])->validate()['decision'];
        $empty = array_fill_keys(['date_mode', 'preferred_date', 'time_window', 'activity', 'custom_activity', 'note'], null);
        if ($decision === 'declined') {
            return ['decision' => $decision] + $empty;
        }

        return ['decision' => $decision] + $this->date($input) + $this->activity($input);
    }

    // Used only to recognize an exact retry of an already validated, immutable response.
    public function retryPayload(array $input): array
    {
        $payload = ['decision' => $input['decision'] ?? null];
        foreach (['date_mode', 'preferred_date', 'time_window', 'activity', 'custom_activity', 'note'] as $field) {
            $value = $input[$field] ?? null;
            $payload[$field] = is_string($value) ? (trim($value) === '' ? null : trim($value)) : $value;
        }
        if ($payload['decision'] === 'declined') {
            return ['decision' => 'declined'] + array_fill_keys(array_keys(array_slice($payload, 1)), null);
        }
        if ($payload['date_mode'] === 'discuss_later') {
            $payload['preferred_date'] = $payload['time_window'] = null;
        }
        if ($payload['activity'] !== 'custom') {
            $payload['custom_activity'] = null;
        }

        return $payload;
    }
}
