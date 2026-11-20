<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Support\AppDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * The app's push notification tokens.
 *
 * POST   /api/device-tokens  {token, platform?, device_id?, device_name?, app_version?}
 *        Saves the token (sending the same token again updates it). When the request carries the
 *        website's login (the app's WebView cookie), the token is linked to that user; otherwise
 *        it is kept as a guest device and linked once a logged-in request sends it again.
 *        The response also sets an HttpOnly cookie remembering the token, so a later login or
 *        logout in the WebView links or unlinks it on the server (see App\Support\AppDeviceToken).
 * DELETE /api/device-tokens  {token}
 *        Forgets the token (on logout, or when notifications are switched off in the app).
 *
 * "fcm_token" and "device_token" are accepted as other names for "token".
 */
class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->acceptOtherTokenNames($request);

        $validated = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:512'],
            'platform' => ['nullable', 'string', Rule::in(DeviceToken::PLATFORMS)],
            'device_id' => ['nullable', 'string', 'max:191'],
            'device_name' => ['nullable', 'string', 'max:191'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ]);

        // A request posted by another website must not link the token to the visitor
        $fromThisSite = AppDeviceToken::isFromThisSite($request);
        $user = $fromThisSite ? Auth::guard('web')->user() : null;
        $device = DeviceToken::query()->firstOrNew(['token' => $validated['token']]);
        $created = ! $device->exists;

        $device->fill([
            'platform' => $validated['platform'] ?? $device->platform ?? 'android',
            'device_id' => $validated['device_id'] ?? $device->device_id,
            'device_name' => $validated['device_name'] ?? $device->device_name,
            'app_version' => $validated['app_version'] ?? $device->app_version,
            'last_seen_at' => now(),
        ]);

        // A logged-in request (re)links the token to that user; a guest one keeps any link
        if ($user) {
            $device->user_id = $user->id;
        }

        $device->save();

        $response = response()->json([
            'success' => true,
            'message' => $created ? 'Device token registered.' : 'Device token updated.',
            'data' => $this->payload($device),
        ], $created ? 201 : 200);

        return $fromThisSite ? $response->withCookie(AppDeviceToken::cookie($device->token)) : $response;
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->acceptOtherTokenNames($request);

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        $deleted = DeviceToken::query()->where('token', $validated['token'])->delete();

        $response = response()->json([
            'success' => true,
            'message' => $deleted ? 'Device token removed.' : 'Device token was not registered.',
        ]);

        return $request->cookie(AppDeviceToken::COOKIE) === $validated['token']
            ? $response->withCookie(AppDeviceToken::forgetCookie())
            : $response;
    }

    private function acceptOtherTokenNames(Request $request): void
    {
        if (! $request->filled('token')) {
            $other = $request->input('fcm_token', $request->input('device_token'));
            if (is_string($other) && $other !== '') {
                $request->merge(['token' => $other]);
            }
        }
    }

    /** @return array<string, mixed> */
    private function payload(DeviceToken $device): array
    {
        return [
            'id' => $device->id,
            'token' => $device->token,
            'platform' => $device->platform,
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'app_version' => $device->app_version,
            'user_id' => $device->user_id,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
        ];
    }
}
