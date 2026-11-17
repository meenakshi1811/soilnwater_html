<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Push notifications to the app through Firebase Cloud Messaging (HTTP v1 API).
 *
 * Every message carries the page to open in its data, as "url" (a full https://soilnwater.in/…
 * address), which the app opens when the notification is tapped:
 *
 *   {"message": {"token": "…",
 *                "notification": {"title": "New Teacher Listed", "body": "View the teacher's profile…"},
 *                "data": {"url": "https://soilnwater.in/teachers-tutors/…", "category": "…"}}}
 *
 * Needs the Firebase project's service account key (Firebase console → Project settings →
 * Service accounts → Generate new private key), saved on the server outside public/ with its path
 * in FIREBASE_CREDENTIALS. Without it nothing is sent (and nothing fails).
 *
 * Tokens Firebase reports as no longer valid (app uninstalled, token replaced) are deleted.
 */
class PushNotificationService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /** Whether the Firebase credentials are set up */
    public static function isConfigured(): bool
    {
        return self::credentials() !== null;
    }

    /**
     * To every device of a user (sent after the page has been returned, so the request isn't slowed).
     *
     * @param  array<string, scalar|null>  $data
     */
    public static function toUser(?User $user, string $title, string $body, ?string $url = null, array $data = []): void
    {
        if (! $user || ! self::isConfigured()) {
            return;
        }

        $tokens = DeviceToken::query()->where('user_id', $user->id)->pluck('token')->all();
        if ($tokens === []) {
            return;
        }

        dispatch(fn () => self::sendToTokens($tokens, $title, $body, $url, $data))->afterResponse();
    }

    /**
     * To every registered device, logged in or not (for site-wide announcements). Returns how many
     * were sent; runs straight away, so use it from the console or a background job.
     *
     * @param  array<string, scalar|null>  $data
     * @return array{sent: int, failed: int, removed: int}
     */
    public static function toAllDevices(string $title, string $body, ?string $url = null, array $data = []): array
    {
        $totals = ['sent' => 0, 'failed' => 0, 'removed' => 0];

        DeviceToken::query()->select('token')->orderBy('id')->chunk(200, function ($rows) use (&$totals, $title, $body, $url, $data): void {
            $result = self::sendToTokens($rows->pluck('token')->all(), $title, $body, $url, $data);
            foreach ($totals as $key => $value) {
                $totals[$key] = $value + $result[$key];
            }
        });

        return $totals;
    }

    /**
     * @param  list<string>  $tokens
     * @param  array<string, scalar|null>  $data
     * @return array{sent: int, failed: int, removed: int}
     */
    public static function sendToTokens(array $tokens, string $title, string $body, ?string $url = null, array $data = []): array
    {
        $result = ['sent' => 0, 'failed' => 0, 'removed' => 0];
        $credentials = self::credentials();
        if ($credentials === null || $tokens === []) {
            return $result;
        }

        $accessToken = self::accessToken($credentials);
        if ($accessToken === null) {
            $result['failed'] = count($tokens);

            return $result;
        }

        $endpoint = 'https://fcm.googleapis.com/v1/projects/'.$credentials['project_id'].'/messages:send';
        $payloadData = self::dataPayload($url, $data);

        foreach (array_unique($tokens) as $token) {
            try {
                $response = Http::withToken($accessToken)->acceptJson()->timeout(10)->post($endpoint, [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => mb_substr($title, 0, 200),
                            'body' => mb_substr($body, 0, 1000),
                        ],
                        'data' => $payloadData,
                        'android' => ['priority' => 'high'],
                    ],
                ]);
            } catch (Throwable $e) {
                $result['failed']++;
                Log::warning('Push notification could not be sent: '.$e->getMessage());

                continue;
            }

            if ($response->successful()) {
                $result['sent']++;

                continue;
            }

            $result['failed']++;
            $errorCode = (string) data_get($response->json(), 'error.details.0.errorCode', '');
            $status = (string) data_get($response->json(), 'error.status', '');

            // The token no longer belongs to an installed app: forget it
            if ($errorCode === 'UNREGISTERED' || $response->status() === 404
                || ($status === 'INVALID_ARGUMENT' && str_contains((string) data_get($response->json(), 'error.message', ''), 'registration token'))) {
                DeviceToken::query()->where('token', $token)->delete();
                $result['removed']++;

                continue;
            }

            Log::warning('Push notification rejected by Firebase', ['status' => $response->status(), 'error' => $response->json('error')]);
        }

        return $result;
    }

    /**
     * The message's data: "url" as a full address (FCM data values must be strings).
     *
     * @param  array<string, scalar|null>  $data
     * @return array<string, string>
     */
    public static function dataPayload(?string $url, array $data = []): array
    {
        $payload = [];
        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                $payload[(string) $key] = (string) $value;
            }
        }

        $url = trim((string) $url);
        if ($url !== '') {
            $payload['url'] = preg_match('#^https?://#i', $url) ? $url : url($url);
        } else {
            $payload['url'] = url('/');
        }

        return $payload;
    }

    /** @return array{project_id: string, client_email: string, private_key: string, token_uri: string}|null */
    private static function credentials(): ?array
    {
        // (read once per key file and process)
        static $cache = [];

        $path = trim((string) config('services.firebase.credentials'));
        if ($path === '') {
            return null;
        }
        if (array_key_exists($path, $cache)) {
            return $cache[$path];
        }
        $cache[$path] = null;
        if (! str_starts_with($path, '/') && ! preg_match('#^[A-Za-z]:[\\\\/]#', $path)) {
            $path = base_path($path);
        }
        if (! is_file($path) || ! is_readable($path)) {
            Log::warning('FIREBASE_CREDENTIALS file not found: '.$path);

            return null;
        }

        $json = json_decode((string) file_get_contents($path), true);
        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            Log::warning('FIREBASE_CREDENTIALS is not a service account key file.');

            return null;
        }

        $projectId = (string) (config('services.firebase.project_id') ?: ($json['project_id'] ?? ''));
        if ($projectId === '') {
            return null;
        }

        return $cache[$path] = [
            'project_id' => $projectId,
            'client_email' => (string) $json['client_email'],
            'private_key' => (string) $json['private_key'],
            'token_uri' => (string) ($json['token_uri'] ?? 'https://oauth2.googleapis.com/token'),
        ];
    }

    /** A Google access token for FCM (kept for 50 minutes; they last an hour) */
    private static function accessToken(array $credentials): ?string
    {
        $cacheKey = 'fcm_access_token:'.md5($credentials['client_email']);

        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        try {
            $now = time();
            $assertion = JWT::encode([
                'iss' => $credentials['client_email'],
                'scope' => self::SCOPE,
                'aud' => $credentials['token_uri'],
                'iat' => $now,
                'exp' => $now + 3600,
            ], $credentials['private_key'], 'RS256');

            $response = Http::asForm()->timeout(10)->post($credentials['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);
        } catch (Throwable $e) {
            Log::warning('Firebase access token request failed: '.$e->getMessage());

            return null;
        }

        $token = (string) $response->json('access_token', '');
        if (! $response->successful() || $token === '') {
            Log::warning('Firebase access token refused', ['status' => $response->status(), 'body' => $response->json()]);

            return null;
        }

        Cache::put($cacheKey, $token, now()->addMinutes(50));

        return $token;
    }
}
