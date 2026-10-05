<?php

namespace App\Services\Randi;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExceptionResponder
{
    public function render(Throwable $error, Request $request)
    {
        if (! $request->is('randi', 'randi/*')) {
            return null;
        }
        $headers = ['Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow, noarchive'];
        if ($error instanceof HttpResponseException) {
            $response = $error->getResponse();
            $response->headers->add($headers);

            return $response;
        }
        if ($error instanceof ValidationException) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Egy apróság még hiányzik.', 'errors' => $error->errors()], 422, $headers);
            }
            if ($request->is('randi/admin/*')) {
                $target = $request->is('randi/admin/login') ? route('randi.admin.login') : route('randi.admin');

                return redirect()->to($target)->withErrors($error->errors())->withInput($request->except('password', '_token', 'submission_key'))->withHeaders($headers);
            }
            $dateError = array_intersect(array_keys($error->errors()), ['date_mode', 'preferred_date', 'time_window']);
            $identity = $this->identity($request);
            $this->saveDraft($request, $dateError ? 'date' : 'activity');

            return redirect()->to($this->inviteUrl($request))->withErrors($error->errors())->with('randi.error_identity', $identity)->withHeaders($headers);
        }
        $status = $error instanceof RandiConflict ? $error->status : ($error instanceof HttpExceptionInterface ? $error->getStatusCode() : 503);
        $message = $error instanceof RandiConflict ? $error->reason : match ($status) {
            419 => 'A munkamenet lejárt. Frissítsd az oldalt, és próbáld újra.',
            404, 410 => 'Ez a meghívó most nem elérhető. Kérj Zolitól egy új linket. 💌',
            429 => 'Várj egy percet, és próbáld újra.',
            default => 'Most nem sikerült elmenteni. A választásaid megvannak, próbáld újra.',
        };
        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $message], $status, $headers);
        }
        // Keep a failed HTML write reviewable, with data scoped to this invite.
        if ($status === 503 && $request->isMethod('POST') && ! $request->is('randi/admin/*')) {
            $this->saveDraft($request, $request->input('decision') === 'declined' ? 'invite' : 'review');
        }

        return response()->view('randi.status', ['message' => $message, 'retry' => $status === 503 ? $this->inviteUrl($request) : null], $status, $headers);
    }

    private function saveDraft(Request $request, string $step): void
    {
        $key = 'randi.drafts.'.$this->identity($request);
        $data = [];
        foreach ($request->only('date_mode', 'preferred_date', 'time_window', 'activity', 'custom_activity', 'note') as $field => $value) {
            if (is_string($value) || $value === null) {
                // Preserve ordinary validation mistakes without putting an
                // arbitrarily large forged request into the session file.
                $data[$field] = is_string($value) ? mb_substr($value, 0, 500) : null;
            }
        }
        $request->session()->put($key, array_merge($request->session()->get($key, []), $data, ['step' => $step]));
    }

    private function identity(Request $request): string
    {
        return $request->route('token') ? hash('sha256', $request->route('token')) : 'demo';
    }

    private function inviteUrl(Request $request): string
    {
        return $request->route('token') ? route('randi.show', $request->route('token')) : route('randi.demo');
    }
}
