<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RandiAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $hash = (string) config('randi.admin_password_hash');
        $grant = $request->session()->get('randi.admin');
        if (! $hash || ! is_string($grant) || ! hash_equals(hash('sha256', $hash), $grant)) {
            return redirect()->route('randi.admin.login');
        }

        return $next($request);
    }
}
