<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pages shown to someone signed in (a user, or an employee) are never kept by the browser or the
 * app's WebView, so after logging out the Back button can't show a profile, dashboard or other
 * personal page again: the browser has to ask the server, which sends a signed-out visitor to the
 * login page.
 *
 * Two parts:
 *  - Cache-Control: no-store (and the older Pragma/Expires) on those responses;
 *  - on those HTML pages, a small script that reloads the page when the browser restores it from
 *    its back/forward memory (some browsers keep the page in memory despite no-store).
 *
 * Guest pages, downloads and responses that chose to be cached publicly are left alone.
 */
class PreventAuthenticatedPageCaching
{
    private const RELOAD_ON_RESTORE_SCRIPT = '<script>window.addEventListener("pageshow",function(e){if(e.persisted){window.location.reload();}});</script>';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->signedIn()) {
            return $response;
        }

        // (files and streams keep their own headers)
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        if (str_contains(strtolower((string) $response->headers->get('Cache-Control')), 'public')) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        $this->addReloadOnRestore($response);

        return $response;
    }

    private function signedIn(): bool
    {
        return Auth::guard('web')->check() || Auth::guard('employee')->check();
    }

    private function addReloadOnRestore(Response $response): void
    {
        $type = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($type !== '' && ! str_contains($type, 'text/html')) {
            return;
        }

        $content = $response->getContent();
        if (! is_string($content) || stripos($content, '</head>') === false) {
            return;
        }

        $response->setContent((string) preg_replace('#</head>#i', self::RELOAD_ON_RESTORE_SCRIPT.'</head>', $content, 1));
    }
}
