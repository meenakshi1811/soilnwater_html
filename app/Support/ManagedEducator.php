<?php

namespace App\Support;

use App\Models\Educator;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * The teacher / tutor a portal page works on: the signed-in owner's own, or the one an admin
 * opened from the admin panel (the admin "manage" routes carry it, see ResolveManagedEducator).
 */
final class ManagedEducator
{
    public const ATTRIBUTE = 'managedEducator';

    public static function current(?Request $request = null): ?Educator
    {
        $request ??= request();

        return self::fromAdmin($request) ?? $request->user()?->educator;
    }

    /** The teacher / tutor an admin is managing (null in the owner's own portal) */
    public static function fromAdmin(?Request $request = null): ?Educator
    {
        $request ??= request();
        $educator = $request->attributes->get(self::ATTRIBUTE);

        return $educator instanceof Educator ? $educator : null;
    }

    /** The teacher's own account (in the admin panel too, rather than the admin) */
    public static function owner(?Request $request = null): ?User
    {
        $request ??= request();
        $educator = self::fromAdmin($request);

        return $educator ? $educator->user : $request->user();
    }

    public static function adminRoute(Educator $educator, string $suffix, mixed $parameters = []): string
    {
        return route(
            'admin.educators.manage.'.$suffix,
            array_merge([$educator], is_array($parameters) ? $parameters : [$parameters])
        );
    }
}
