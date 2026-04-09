<?php

namespace App\Support;

/**
 * Central registry for RBAC modules and permission actions (Spatie permission names: {module}.{action}).
 */
final class ModulePermissions
{
    public const ACTIONS = ['add', 'read', 'write', 'delete', 'approve'];

    /**
     * @return array<string, string> slug => display label
     */
    public static function modules(): array
    {
        return [
            'ecommerce' => 'E-Commerce',
            'vendors' => 'Vendors',
            'services' => 'Services',
            'properties' => 'Properties',
            'builders' => 'Builders',
            'consultants' => 'Consultants',
            'enquiry' => 'Enquiry',
            'products' => 'Products',
            'user_enquiry' => 'User Enquery',
        ];
    }

    /**
     * @return list<string> all permission names, e.g. ecommerce.read
     */
    public static function allPermissionNames(): array
    {
        $names = [];
        foreach (array_keys(self::modules()) as $slug) {
            foreach (self::ACTIONS as $action) {
                $names[] = $slug.'.'.$action;
            }
        }

        return $names;
    }

    public static function permissionName(string $moduleSlug, string $action): string
    {
        return $moduleSlug.'.'.$action;
    }
}
