<?php

namespace App\Http\Controllers;

use App\Services\Randi\InviteStore;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RandiAdminController extends Controller
{
    public function login()
    {
        return view('randi.login', ['configured' => (bool) config('randi.admin_password_hash')]);
    }

    public function authenticate(Request $request)
    {
        $request->validate(['password' => ['required', 'string', 'max:1024']], ['password.required' => 'Add meg a jelszót.']);
        $hash = (string) config('randi.admin_password_hash');
        if (! $hash || ! password_verify($request->input('password'), $hash)) {
            return redirect()->route('randi.admin.login')->withErrors(['password' => 'A belépés nem sikerült. Ellenőrizd a jelszót.']);
        }
        $request->session()->regenerate();
        $request->session()->put('randi.admin', hash('sha256', $hash));

        return redirect()->route('randi.admin');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('randi.admin');
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();

        return redirect()->route('randi.admin.login');
    }

    public function index(InviteStore $store)
    {
        $invites = $store->listing();
        $responses = $store->connection()->table('date_responses')->whereIn('invite_id', $invites->pluck('id'))->get()->keyBy('invite_id');

        return view('randi.admin', compact('invites', 'responses'));
    }

    public function create(Request $request, InviteStore $store)
    {
        $data = $request->validate([
            'recipient_name' => ['nullable', 'string', 'max:80'],
            'sender_name' => ['required', 'string', 'max:80'],
            'intro_message' => ['nullable', 'string', 'max:240'],
            'expires_days' => ['required', 'integer', 'min:1', 'max:90'],
        ], [
            'sender_name.required' => 'Add meg a feladó nevét.',
            'recipient_name.max' => 'A keresztnév legfeljebb 80 karakter lehet.',
            'sender_name.max' => 'A feladó neve legfeljebb 80 karakter lehet.',
            'intro_message.max' => 'A bevezető legfeljebb 240 karakter lehet.',
            'expires_days.required' => 'Add meg a link élettartamát.',
            'expires_days.integer' => 'Az élettartam egész szám legyen.',
            'expires_days.min' => 'Az élettartam legalább 1 nap.',
            'expires_days.max' => 'Az élettartam legfeljebb 90 nap.',
            '*.string' => 'Szöveges értéket adj meg.',
        ]);
        $created = $store->create($data);

        // No flash/session persistence of the raw link: returned exactly once.
        return response()->view('randi.created', ['link' => $created['link']], 201);
    }

    public function revoke(Request $request, InviteStore $store, int $id)
    {
        $store->locked(function ($db) use ($id) {
            $invite = $db->table('date_invites')->where('id', $id)->first();
            abort_unless($invite, 404);
            $db->table('date_invites')->where('id', $id)->update(['revoked_at' => CarbonImmutable::now('UTC')->format('Y-m-d H:i:s'), 'updated_at' => CarbonImmutable::now('UTC')->format('Y-m-d H:i:s')]);
        });

        return redirect()->route('randi.admin')->with('status', 'A meghívót visszavontad. A régi link már nem használható.');
    }

    public function delete(Request $request, InviteStore $store, int $id)
    {
        $request->validate(['confirmation' => ['required', Rule::in(['TORLES'])]], ['confirmation.required' => 'A törléshez írd be: TORLES.', 'confirmation.in' => 'A megerősítéshez pontosan ezt írd be: TORLES.']);
        $store->locked(fn ($db) => $db->table('date_invites')->where('id', $id)->delete());

        return redirect()->route('randi.admin')->with('status', 'A meghívót és a hozzá tartozó választ végleg törölted.');
    }
}
