<?php

namespace App\Support;

use App\Models\Educator;
use App\Models\Institute;
use App\Models\StudyMaterial;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The school and city of a study material, for the notes page counts and filters.
 *
 * School: the name in board_university when it names a school (a question paper from a school,
 * college or university, or a name that matches a registered institute; a board like CBSE is not
 * a school). Spellings of one school ("St Joseph's Academy, Dehradun", "St. Joseph's Academy")
 * are stored as one name: the registered institute's, else the first one seen.
 *
 * City (and state, coordinates), first found of: the Google place picked in the school search on
 * the upload form (meta school_city …), the matching registered institute, the place picked for the
 * same school on another upload, a known city written after the school name ("…, Dehradun"), the
 * uploading teacher's profile, the uploader's account.
 */
final class StudyMaterialLocation
{
    /** @var Collection<int, Institute>|null */
    private static ?Collection $institutes = null;

    /** @var array<string, string>|null normalized key => stored school name */
    private static ?array $schoolNames = null;

    /** @var array<string, string>|null normalized city => city as written */
    private static ?array $knownCities = null;

    /** @var array<string, array<int, mixed>>|null school key => [city, state, lat, lng] picked on an upload */
    private static ?array $schoolPlaces = null;

    public static function fill(StudyMaterial $material): void
    {
        $meta = (array) ($material->meta ?? []);
        $rawSchool = trim((string) $material->board_university) ?: null;
        $institute = $rawSchool !== null ? self::matchInstitute($rawSchool) : null;

        $isSchool = $rawSchool !== null && ($institute !== null || ($material->material_type === 'question_papers'
            && ! filter_var(data_get($meta, 'is_board'), FILTER_VALIDATE_BOOLEAN)));

        $material->school_name = $isSchool ? self::canonicalSchoolName($rawSchool, $institute) : null;

        $educator = $material->educator_id ? $material->educator : null;
        $user = $material->user_id ? $material->user : null;
        $suffixCity = $isSchool ? self::cityAfterComma($rawSchool) : null;
        $picked = [data_get($meta, 'school_city'), data_get($meta, 'school_state'), data_get($meta, 'school_lat'), data_get($meta, 'school_lng')];
        $schoolKey = $isSchool ? self::key((string) $material->school_name) : '';

        if ($schoolKey !== '' && self::cleanCity($picked[0]) !== null) {
            self::schoolPlaces();
            self::$schoolPlaces[$schoolKey] = $picked;
        }

        $candidates = [
            $picked,
            [$institute?->city, $institute?->state, $institute?->latitude, $institute?->longitude],
            $schoolKey !== '' ? (self::schoolPlaces()[$schoolKey] ?? [null, null, null, null]) : [null, null, null, null],
            [$suffixCity, null, null, null],
            [$educator?->city, $educator?->state, $educator?->latitude, $educator?->longitude],
            [$user?->city, $user?->state ?? null, null, null],
        ];

        $material->city = null;
        $material->state = null;
        $material->latitude = null;
        $material->longitude = null;

        foreach ($candidates as [$city, $state, $lat, $lng]) {
            $city = self::cleanCity($city);
            if ($city === null) {
                continue;
            }

            $material->city = $city;
            $material->state = filled($state) ? Str::limit(trim((string) $state), 120, '') : null;
            $material->latitude = is_numeric($lat) ? (float) $lat : null;
            $material->longitude = is_numeric($lng) ? (float) $lng : null;
            break;
        }
    }

    /**
     * Forget the cached institutes, names and cities (after bulk changes, and between tests).
     */
    public static function flush(): void
    {
        self::$institutes = null;
        self::$schoolNames = null;
        self::$knownCities = null;
        self::$schoolPlaces = null;
    }

    /**
     * Where each school is, from uploads where it was picked from Google (meta school_city …).
     *
     * @return array<string, array<int, mixed>>
     */
    private static function schoolPlaces(): array
    {
        if (self::$schoolPlaces === null) {
            self::$schoolPlaces = [];
            StudyMaterial::query()
                ->whereNotNull('board_university')
                ->whereNotNull('meta->school_city')
                ->orderBy('id')
                ->get(['id', 'board_university', 'meta'])
                ->each(function (StudyMaterial $material): void {
                    $name = (string) $material->board_university;
                    $institute = self::matchInstitute($name);
                    $display = $institute ? (string) ($institute->display_name ?: $institute->institution_name) : self::beforeComma($name);
                    self::$schoolPlaces[self::key($display)] ??= [
                        data_get($material->meta, 'school_city'),
                        data_get($material->meta, 'school_state'),
                        data_get($material->meta, 'school_lat'),
                        data_get($material->meta, 'school_lng'),
                    ];
                });
        }

        return self::$schoolPlaces;
    }

    private static function matchInstitute(string $name): ?Institute
    {
        $key = self::key(self::beforeComma($name));
        if ($key === '') {
            return null;
        }

        self::$institutes ??= Institute::query()
            ->approved()
            ->get(['id', 'institution_name', 'display_name', 'city', 'state', 'latitude', 'longitude']);

        return self::$institutes->first(function (Institute $institute) use ($key) {
            return in_array($key, array_filter([
                self::key((string) $institute->display_name),
                self::key((string) $institute->institution_name),
            ]), true);
        });
    }

    private static function canonicalSchoolName(string $name, ?Institute $institute): string
    {
        $display = $institute
            ? trim((string) ($institute->display_name ?: $institute->institution_name))
            : trim(self::beforeComma($name));
        $key = self::key($display);

        if (self::$schoolNames === null) {
            self::$schoolNames = [];
            foreach (StudyMaterial::query()->whereNotNull('school_name')->distinct()->pluck('school_name') as $stored) {
                self::$schoolNames[self::key((string) $stored)] ??= (string) $stored;
            }
        }

        // A registered institute's name wins; otherwise reuse the spelling already stored
        if ($institute) {
            self::$schoolNames[$key] = $display;
        }

        return self::$schoolNames[$key] ??= Str::limit($display, 190, '');
    }

    /**
     * "St Joseph's Academy, Dehradun" → "Dehradun", only when that is a city already known here
     * (an institute's, teacher's or user's city), so a locality or campus name isn't taken.
     */
    private static function cityAfterComma(string $name): ?string
    {
        if (! str_contains($name, ',')) {
            return null;
        }

        $suffix = trim(Str::afterLast($name, ','));
        if ($suffix === '') {
            return null;
        }

        if (self::$knownCities === null) {
            self::$knownCities = [];
            $cities = Institute::query()->whereNotNull('city')->distinct()->pluck('city')
                ->merge(Educator::query()->whereNotNull('city')->distinct()->pluck('city'))
                ->merge(User::query()->whereNotNull('city')->distinct()->pluck('city'));
            foreach ($cities as $city) {
                $clean = self::cleanCity($city);
                if ($clean !== null) {
                    self::$knownCities[self::key($clean)] ??= $clean;
                }
            }
        }

        return self::$knownCities[self::key($suffix)] ?? null;
    }

    private static function cleanCity(mixed $city): ?string
    {
        $city = trim(preg_replace('/\s+/', ' ', (string) $city));
        if ($city === '' || mb_strlen($city) > 120 || in_array(Str::lower($city), ['your location', 'n/a', 'na', '-'], true)) {
            return null;
        }

        return $city === mb_strtoupper($city) || $city === mb_strtolower($city) ? Str::title(mb_strtolower($city)) : $city;
    }

    private static function beforeComma(string $name): string
    {
        return str_contains($name, ',') ? Str::before($name, ',') : $name;
    }

    /** Lower case letters and digits only: "St. Joseph's Academy" → "stjosephsacademy" */
    private static function key(string $text): string
    {
        return (string) preg_replace('/[^a-z0-9]+/', '', Str::lower(Str::ascii($text)));
    }
}
