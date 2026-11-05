<?php

namespace App\Support;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Support\Facades\Route;

final class SchoolInstituteHelper
{
    public const MARKETPLACE_ROLES = ['school', 'college', 'institute'];

    /**
     * Each kind of account and its names: label, plural, public listing (also its route prefix:
     * schools.show, colleges.show, ...), portal route prefix, admin route prefix and icon.
     */
    public const KINDS = [
        'school' => ['label' => 'School', 'plural' => 'Schools', 'listing' => 'schools', 'portal' => 'school', 'admin' => 'admin.schools', 'icon' => 'fa-school'],
        'college' => ['label' => 'College', 'plural' => 'Colleges', 'listing' => 'colleges', 'portal' => 'college', 'admin' => 'admin.colleges', 'icon' => 'fa-graduation-cap'],
        'institute' => ['label' => 'Institute', 'plural' => 'Institutes', 'listing' => 'institutes', 'portal' => 'institute', 'admin' => 'admin.institutes', 'icon' => 'fa-building-columns'],
    ];

    /** The kind's names for a role (school, college or institute; anything else: institute) */
    public static function kind(?string $role): array
    {
        return self::KINDS[$role] ?? self::KINDS['institute'];
    }

    /** "School", "College" or "Institute" */
    public static function label(?string $role): string
    {
        return self::kind($role)['label'];
    }

    /** "Schools", "Colleges" or "Institutes" */
    public static function plural(?string $role): string
    {
        return self::kind($role)['plural'];
    }

    /** The public listing ("schools", "colleges", "institutes"): also its route name prefix */
    public static function listingContext(?string $role): string
    {
        return self::kind($role)['listing'];
    }

    /** The role a public listing is for ("colleges" => "college"; unknown: institute) */
    public static function roleForListing(?string $listingContext): string
    {
        foreach (self::KINDS as $role => $kind) {
            if ($kind['listing'] === $listingContext) {
                return $role;
            }
        }

        return 'institute';
    }

    /** "Schools", "Colleges" or "Institutes", for a public listing */
    public static function pluralForListing(?string $listingContext): string
    {
        return self::plural(self::roleForListing($listingContext));
    }

    /** "School", "College" or "Institute", for a public listing */
    public static function labelForListing(?string $listingContext): string
    {
        return self::label(self::roleForListing($listingContext));
    }

    /** The portal route prefix ("school", "college", "institute") for a public listing */
    public static function portalForListing(?string $listingContext): string
    {
        return self::kind(self::roleForListing($listingContext))['portal'];
    }

    /** The admin route prefix ("admin.schools", "admin.colleges", "admin.institutes") */
    public static function adminPrefix(?string $role): string
    {
        return self::kind($role)['admin'];
    }

    public static function isMarketplaceRole(?string $role): bool
    {
        return in_array($role, self::MARKETPLACE_ROLES, true);
    }

    public static function accountLabel(?User $user): string
    {
        return self::label($user?->role);
    }

    public static function adminShowRoute(Institute $institute): string
    {
        $institute->loadMissing('user');

        return route(self::adminPrefix($institute->user?->role).'.show', $institute);
    }

    public static function adminIndexRoute(?User $user): string
    {
        return route(self::adminPrefix($user?->role).'.index');
    }

    /**
     * @return list<string>
     */
    public static function schoolInstitutionTypes(): array
    {
        return ['school', 'college', 'university'];
    }

    /**
     * @return list<string>
     */
    public static function collegeInstitutionTypes(): array
    {
        return ['college', 'university'];
    }

    /**
     * @return list<string>
     */
    public static function instituteInstitutionTypes(): array
    {
        return ['coaching', 'other'];
    }

    /**
     * The institution types on a role's profile form, value => label.
     *
     * @return array<string, string>
     */
    public static function institutionTypeOptions(?string $role): array
    {
        return match ($role) {
            'school' => ['school' => 'School', 'college' => 'College', 'university' => 'University'],
            'college' => ['college' => 'College', 'university' => 'University'],
            default => ['coaching' => 'Coaching Institute', 'other' => 'Other'],
        };
    }

    public static function defaultInstitutionTypeForRole(string $role): string
    {
        return match ($role) {
            'school' => 'school',
            'college' => 'college',
            default => 'coaching',
        };
    }

    public static function portalRouteName(?User $user, string $suffix): string
    {
        $prefix = $user?->portalRoutePrefix() ?? 'school';

        return $prefix.'.'.$suffix;
    }

    public static function portalRoute(?User $user, string $suffix, mixed $parameters = [], bool $absolute = true): string
    {
        return self::routeForPrefix($user?->portalRoutePrefix() ?? 'school', $suffix, $parameters, $absolute);
    }

    public static function routeForPrefix(string $prefix, string $suffix, mixed $parameters = [], bool $absolute = true): string
    {
        $routeName = $prefix.'.'.$suffix;

        if (Route::has($routeName)) {
            return route($routeName, $parameters, $absolute);
        }

        $legacyName = 'institute.'.$suffix;
        if ($prefix !== 'institute' && Route::has($legacyName)) {
            return route($legacyName, $parameters, $absolute);
        }

        return url('/');
    }
}
