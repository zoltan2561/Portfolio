<?php

namespace App\Http\Controllers;

use App\Services\Randi\InviteStore;
use App\Services\Randi\RandiConflict;
use App\Services\Randi\ResponseValidator;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;

class RandiController extends Controller
{
    public function __construct(private InviteStore $store, private ResponseValidator $validator) {}

    public function show(Request $request, ?string $token = null)
    {
        $demo = $token === null;
        $invite = $demo ? (object) ['recipient_name' => null, 'sender_name' => 'Zoli', 'intro_message' => null] : $this->store->find($token);
        if (! $demo && ! $this->store->available($invite)) {
            return response()->view('randi.status', ['message' => 'Ez a meghívó most nem elérhető. Kérj Zolitól egy új linket. 💌'], 410);
        }
        $identity = $demo ? 'demo' : $invite->token_hash;
        $entry = $request->session()->get('randi.keys.'.$identity);
        $response = $demo ? null : $this->store->response($invite->id);
        if ($response && (! $entry || ! hash_equals($response->submission_key_hash, hash('sha256', $entry['key'])))) {
            return response()->view('randi.status', ['message' => 'Erre a meghívóra már érkezett válasz.']);
        }
        if (! $entry) {
            $keys = $request->session()->get('randi.keys', []);
            if (count($keys) >= 30) {
                array_shift($keys);
            }
            $entry = ['key' => InviteStore::secret()];
            $keys[$identity] = $entry;
            $request->session()->put('randi.keys', $keys);
        }
        $draft = $request->session()->get('randi.drafts.'.$identity, []);
        if ($response) {
            $request->session()->forget('randi.drafts.'.$identity);
        }

        return view('randi.invite', [
            'demo' => $demo, 'invite' => $invite, 'saved' => $response,
            'step' => $response ? ($response->decision === 'declined' ? 'declined' : 'success') : ($draft['step'] ?? 'invite'),
            'draft' => $response ? (array) $response : $draft,
            'errors' => $request->session()->get('randi.error_identity') === $identity ? $request->session()->get('errors', new ViewErrorBag) : new ViewErrorBag,
            'identity' => $identity, 'key' => $entry['key'], 'calendar' => $this->validator->calendar(),
            'formUrl' => $demo ? route('randi.demo.form') : route('randi.form', $token),
            'responseUrl' => $demo ? route('randi.demo.response') : route('randi.response', $token),
        ]);
    }

    public function form(Request $request, ?string $token = null)
    {
        $identity = $this->identity($token);
        $draft = $request->session()->get('randi.drafts.'.$identity, []);
        switch ($request->input('action')) {
            case 'start': $draft['step'] = 'joy';
                break;
            case 'date': case 'edit_date': $draft['step'] = 'date';
                break;
            case 'activity':
                $draft = array_merge($draft, $this->validator->date($request->all()), ['step' => 'activity']);
                break;
            case 'edit_activity': $draft['step'] = 'activity';
                break;
            case 'review':
                $draft = array_merge($draft, $this->validator->activity($request->all()), ['step' => 'review']);
                break;
            default: abort(422);
        }
        $request->session()->put('randi.drafts.'.$identity, $draft);

        return redirect()->to($token ? route('randi.show', $token) : route('randi.demo'));
    }

    public function respond(Request $request, ?string $token = null)
    {
        $identity = $this->identity($token);
        $entry = $request->session()->get('randi.keys.'.$identity);
        if (! $entry || ! is_string($request->input('submission_key')) || ! hash_equals($entry['key'], $request->input('submission_key'))) {
            throw new RandiConflict('A munkamenet lejárt. Nyisd meg újra a meghívót.', 419);
        }
        $input = $request->expectsJson() ? $request->all() : array_merge($request->session()->get('randi.drafts.'.$identity, []), $request->all());
        if ($token === null) {
            $payload = $this->validator->normalize($input);
            $request->session()->forget('randi.drafts.demo');
            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'demo' => true, 'response' => $payload]);
            }

            return response()->view('randi.demo-complete', ['payload' => $payload]);
        }
        $result = $this->store->submit($token, $entry['key'], $input, $this->validator);
        $request->session()->forget('randi.drafts.'.$identity);
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'demo' => false, 'response' => array_intersect_key((array) $result, array_flip(['decision', 'date_mode', 'preferred_date', 'time_window', 'activity', 'custom_activity', 'note']))]);
        }

        return redirect()->route('randi.show', $token);
    }

    private function identity(?string $token): string
    {
        return $token === null ? 'demo' : hash('sha256', $token);
    }
}
