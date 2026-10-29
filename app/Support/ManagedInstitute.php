<?php

namespace App\Support;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * The school or institute a portal page works on: the signed-in owner's own, or the one an admin
 * opened from the admin panel (the admin "manage" routes carry it, see ResolveManagedInstitute).
 */
final class ManagedInstitute
{
    public const ATTRIBUTE = 'managedInstitute';

    public static function current(?Request $request = null): ?Institute
    {
        $request ??= request();

        return self::fromAdmin($request) ?? $request->user()?->institute;
    }

    /** The school or institute an admin is managing (null in the owner's own portal) */
    public static function fromAdmin(?Request $request = null): ?Institute
    {
        $request ??= request();
        $institute = $request->attributes->get(self::ATTRIBUTE);

        return $institute instanceof Institute ? $institute : null;
    }

    /** The school's own account (in the admin panel too, rather than the admin) */
    public static function owner(?Request $request = null): ?User
    {
        $request ??= request();
        $institute = self::fromAdmin($request);

        return $institute ? $institute->user : $request->user();
    }

    public static function adminRoutePrefix(Institute $institute): string
    {
        $institute->loadMissing('user');

        return ($institute->user?->isSchool() ? 'admin.schools' : 'admin.institutes').'.manage';
    }

    public static function adminRoute(Institute $institute, string $suffix, mixed $parameters = []): string
    {
        return route(
            self::adminRoutePrefix($institute).'.'.$suffix,
            array_merge([$institute], is_array($parameters) ? $parameters : [$parameters])
        );
    }
}
