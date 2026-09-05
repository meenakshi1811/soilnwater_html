<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

final class AccountCreation
{
    /**
     * @return array<string, string> account role => RBAC module slug
     */
    public static function roleModuleMap(): array
    {
        return [
            'user' => 'users',
            'vendor' => 'vendors',
            'consultant' => 'consultants',
            'service_provider' => 'service_providers',
            'teacher' => 'educators',
            'tutor' => 'educators',
        ];
    }

    public static function moduleForRole(?string $role): ?string
    {
        if (! is_string($role) || $role === '') {
            return null;
        }

        return self::roleModuleMap()[$role] ?? null;
    }

    public static function canCreateRole(?Authenticatable $actor, bool $isAdmin, ?string $role): bool
    {
        if ($isAdmin) {
            return is_string($role) && $role !== '' && array_key_exists($role, self::roleModuleMap());
        }

        if (! $actor || ! method_exists($actor, 'canModule')) {
            return false;
        }

        $module = self::moduleForRole($role);

        return $module !== null && $actor->canModule($module, 'add');
    }

    public static function canCreateAny(?Authenticatable $actor, bool $isAdmin): bool
    {
        if ($isAdmin) {
            return true;
        }

        foreach (array_keys(self::roleModuleMap()) as $role) {
            if (self::canCreateRole($actor, $isAdmin, $role)) {
                return true;
            }
        }

        return false;
    }

    public static function portalActorIsAdmin(?Authenticatable $actor): bool
    {
        return $actor !== null && method_exists($actor, 'isAdmin') && $actor->isAdmin();
    }
}
