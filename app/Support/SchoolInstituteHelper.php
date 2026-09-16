<?php

namespace App\Support;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Support\Facades\Route;

final class SchoolInstituteHelper
{
    public const MARKETPLACE_ROLES = ['school', 'institute'];

    public static function isMarketplaceRole(?string $role): bool
    {
        return in_array($role, self::MARKETPLACE_ROLES, true);
    }

    public static function accountLabel(?User $user): string
    {
        return $user?->isSchool() ? 'School' : 'Institute';
    }

    public static function adminShowRoute(Institute $institute): string
    {
        $institute->loadMissing('user');

        return $institute->user?->isSchool()
            ? route('admin.schools.show', $institute)
            : route('admin.institutes.show', $institute);
    }

    public static function adminIndexRoute(?User $user): string
    {
        return $user?->isSchool()
            ? route('admin.schools.index')
            : route('admin.institutes.index');
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
    public static function instituteInstitutionTypes(): array
    {
        return ['coaching', 'other'];
    }

    public static function defaultInstitutionTypeForRole(string $role): string
    {
        return $role === 'school' ? 'school' : 'coaching';
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
