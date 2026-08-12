<?php

namespace App\Models\Concerns;

trait HasDefaultListingLocation
{
    /**
     * @return array{location: string, latitude: ?float, longitude: ?float}
     */
    public function defaultListingLocation(): array
    {
        $user = $this->user;
        $location = method_exists($this, 'formattedAddress')
            ? trim((string) $this->formattedAddress())
            : '';

        if ($location === '' && $user) {
            $location = collect([$user->address, $user->city, $user->pincode])
                ->filter(fn ($part) => filled($part))
                ->map(fn ($part) => trim((string) $part))
                ->implode(', ');
        }

        return [
            'location' => $location,
            'latitude' => filled($user?->latitude) ? (float) $user->latitude : null,
            'longitude' => filled($user?->longitude) ? (float) $user->longitude : null,
        ];
    }
}
