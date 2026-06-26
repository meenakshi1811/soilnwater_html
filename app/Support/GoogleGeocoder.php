<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleGeocoder
{
    /**
     * @return array{latitude: float|null, longitude: float|null}
     */
    public static function coordinatesForAddress(string $address, ?string $city = null, ?string $pincode = null): array
    {
        $apiKey = config('services.google.maps_api_key');

        if (! filled($apiKey)) {
            return ['latitude' => null, 'longitude' => null];
        }

        $parts = array_values(array_filter([
            trim($address),
            trim((string) $city),
            trim((string) $pincode),
            'India',
        ]));

        if ($parts === []) {
            return ['latitude' => null, 'longitude' => null];
        }

        try {
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => implode(', ', $parts),
                'key' => $apiKey,
                'region' => 'in',
            ]);

            if (! $response->successful()) {
                return ['latitude' => null, 'longitude' => null];
            }

            $payload = $response->json();
            $location = data_get($payload, 'results.0.geometry.location');

            if (! is_array($location)) {
                return ['latitude' => null, 'longitude' => null];
            }

            $latitude = data_get($location, 'lat');
            $longitude = data_get($location, 'lng');

            if (! is_numeric($latitude) || ! is_numeric($longitude)) {
                return ['latitude' => null, 'longitude' => null];
            }

            return [
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
            ];
        } catch (\Throwable $exception) {
            Log::warning('Google geocoding failed', [
                'address' => $address,
                'city' => $city,
                'pincode' => $pincode,
                'error' => $exception->getMessage(),
            ]);

            return ['latitude' => null, 'longitude' => null];
        }
    }
}
