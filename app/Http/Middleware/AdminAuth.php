<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tiny single-password gate for /admin/* routes.
 *
 * Phase 2 minimal: one shared password (env ADMIN_PASSWORD) compared in
 * constant time. No user accounts, no sessions beyond the cookie. Good
 * enough to keep the casual snooper out — when SETQ.AI gains real users,
 * swap for Laravel Auth + a users table.
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('admin.password', '');

        if ($expected === '') {
            // Misconfigured — fail closed
            abort(503, 'Admin disabled — ADMIN_PASSWORD not set');
        }

        $given = (string) $request->cookie('setq_admin', '');
        if (hash_equals($expected, $given)) {
            return $next($request);
        }

        // Login flow
        if ($request->isMethod('POST') && $request->input('password')) {
            if (hash_equals($expected, (string) $request->input('password'))) {
                return redirect($request->path() === 'admin/login' ? '/admin/analytics' : $request->fullUrl())
                    ->cookie('setq_admin', $expected, 60 * 8, '/', null, true, true, false, 'lax');
            }
        }

        return response()->view('admin.login', ['error' => $request->isMethod('POST')], 401);
    }
}
