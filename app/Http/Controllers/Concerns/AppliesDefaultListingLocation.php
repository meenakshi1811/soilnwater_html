<?php

namespace App\Http\Controllers\Concerns;

use App\Support\GoogleGeocoder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

trait AppliesDefaultListingLocation
{
    protected function applyDefaultListingLocationToRequest(Request $request, ?object $profile): void
    {
        $defaults = $this->resolvedListingLocation($profile);

        $request->merge([
            'location' => $defaults['location'],
            'latitude' => $defaults['latitude'],
            'longitude' => $defaults['longitude'],
        ]);
    }

    /**
     * @return array{location: string, latitude: float, longitude: float}
     */
    protected function resolvedListingLocation(?object $profile): array
    {
        if (! $profile || ! method_exists($profile, 'defaultListingLocation')) {
            throw ValidationException::withMessages([
                'location' => 'Unable to resolve the account location. Update the user address first.',
            ]);
        }

        $defaults = $profile->defaultListingLocation();
        $user = $profile->user ?? null;

        if ((! filled($defaults['latitude'] ?? null) || ! filled($defaults['longitude'] ?? null)) && filled($defaults['location'] ?? null)) {
            $coordinates = GoogleGeocoder::coordinatesForAddress(
                (string) ($user?->address ?: $defaults['location']),
                isset($user?->city) ? (string) $user->city : null,
                isset($user?->pincode) ? (string) $user->pincode : null,
            );

            if ($coordinates['latitude'] !== null && $coordinates['longitude'] !== null) {
                $defaults['latitude'] = $coordinates['latitude'];
                $defaults['longitude'] = $coordinates['longitude'];
            }
        }

        if (! filled($defaults['location'] ?? null) || ! filled($defaults['latitude'] ?? null) || ! filled($defaults['longitude'] ?? null)) {
            throw ValidationException::withMessages([
                'location' => 'This account does not have a registered address with coordinates. Update the user profile first.',
            ]);
        }

        return [
            'location' => (string) $defaults['location'],
            'latitude' => (float) $defaults['latitude'],
            'longitude' => (float) $defaults['longitude'],
        ];
    }

    /**
     * @return array<int, array{location: string, latitude: ?float, longitude: ?float}>
     */
    protected function profileLocationsMap(Collection $profiles): array
    {
        return $profiles
            ->filter(fn ($profile) => method_exists($profile, 'defaultListingLocation'))
            ->mapWithKeys(fn ($profile) => [$profile->id => $profile->defaultListingLocation()])
            ->all();
    }
}
