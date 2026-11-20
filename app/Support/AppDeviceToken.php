<?php

namespace App\Support;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

/**
 * Keeps the app's push token linked to whoever is logged in to the website in the app's WebView.
 *
 * When the app registers its token (POST /api/device-tokens) from inside the WebView, the
 * website remembers it in an encrypted, HttpOnly cookie. That cookie outlives logins and
 * logouts, so:
 *  - on login (password, OTP, social, remember-me, after registering) the token is linked to the user;
 *  - on logout the token is unlinked (kept as a guest device), so the previous user's
 *    notifications stop reaching this phone.
 * The app doesn't have to detect login or logout itself.
 */
class AppDeviceToken
{
    public const COOKIE = 'snw_push_token';

    /** One year, renewed every time the app registers its token */
    private const COOKIE_MINUTES = 60 * 24 * 365;

    public static function cookie(string $token): Cookie
    {
        return cookie(
            self::COOKIE,
            $token,
            self::COOKIE_MINUTES,
            config('session.path', '/'),
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax'),
        );
    }

    public static function forgetCookie(): Cookie
    {
        return cookie()->forget(self::COOKIE, config('session.path', '/'), config('session.domain'));
    }

    /**
     * False for a request sent by another website (a form or script on a different site posting
     * here with the visitor's cookies): such a request must not link a token to the visitor or
     * plant the cookie, or someone could receive another person's notifications.
     */
    public static function isFromThisSite(Request $request): bool
    {
        if (strtolower((string) $request->headers->get('Sec-Fetch-Site')) === 'cross-site') {
            return false;
        }

        $origin = $request->headers->get('Origin');
        if ($origin === null || $origin === '') {
            return true; // the app's own (native) request
        }

        return strcasecmp((string) parse_url($origin, PHP_URL_HOST), $request->getHost()) === 0;
    }

    public static function onLogin(Login $event): void
    {
        if ($event->guard !== 'web' || ! $event->user instanceof User) {
            return;
        }

        $token = self::tokenFromCookie();
        if ($token === null) {
            return;
        }

        try {
            $device = DeviceToken::query()->firstOrNew(['token' => $token]);
            $device->user_id = $event->user->id;
            $device->last_seen_at = now();
            $device->platform = $device->platform ?? 'android';
            $device->save();
        } catch (Throwable $e) {
            Log::warning('Could not link the app push token on login: '.$e->getMessage());
        }
    }

    public static function onLogout(Logout $event): void
    {
        if ($event->guard !== 'web' || ! $event->user instanceof User) {
            return;
        }

        $token = self::tokenFromCookie();
        if ($token === null) {
            return;
        }

        try {
            DeviceToken::query()
                ->where('token', $token)
                ->where('user_id', $event->user->id)
                ->update(['user_id' => null]);
        } catch (Throwable $e) {
            Log::warning('Could not unlink the app push token on logout: '.$e->getMessage());
        }
    }

    private static function tokenFromCookie(): ?string
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }

        $token = request()->cookie(self::COOKIE);

        return is_string($token) && strlen($token) >= 20 && strlen($token) <= 512 ? $token : null;
    }
}
