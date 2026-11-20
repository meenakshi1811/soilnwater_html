<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends the session's current CSRF token back on every AJAX response (X-CSRF-TOKEN header).
 *
 * Laravel issues a new CSRF token whenever someone logs in, so a page that logs in (or tries to)
 * over AJAX and stays open would keep posting the old token and get "CSRF token mismatch". The
 * same happens when the session expires on a page left open. assets/js/csrf-refresh.js reads this
 * header and updates the page's token, and form.js retries a request refused with 419 once.
 *
 * Runs outside VerifyCsrfToken (prepended to the web group), so a refused (419) response carries
 * the token that works too.
 */
class ShareFreshCsrfToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (($request->ajax() || $request->expectsJson()) && $request->hasSession()) {
            $token = $request->session()->token();
            if (is_string($token) && $token !== '') {
                $response->headers->set('X-CSRF-TOKEN', $token);
            }
        }

        return $response;
    }
}
