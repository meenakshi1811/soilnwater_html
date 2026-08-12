<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait AppliesDefaultListingLocation
{
    protected function applyDefaultListingLocationToRequest(Request $request, ?object $profile): void
    {
        if (! $profile || ! method_exists($profile, 'defaultListingLocation')) {
            return;
        }

        $defaults = $profile->defaultListingLocation();
        $merge = [];

        if (! filled($request->input('location')) && filled($defaults['location'])) {
            $merge['location'] = $defaults['location'];
        }

        if (! filled($request->input('latitude')) && filled($defaults['latitude'])) {
            $merge['latitude'] = $defaults['latitude'];
        }

        if (! filled($request->input('longitude')) && filled($defaults['longitude'])) {
            $merge['longitude'] = $defaults['longitude'];
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
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
