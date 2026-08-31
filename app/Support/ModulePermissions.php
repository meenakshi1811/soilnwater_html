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
            'service_providers' => 'Services',
            'enquiry' => 'Enquiry',
            'products' => 'Products',
            'offers' => 'Offers',
            'ads' => 'Ads',
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

    /**
     * Primary backend route for a module workspace.
     */
    public static function entryRouteName(string $moduleSlug): ?string
    {
        return self::entryRoutes()[$moduleSlug] ?? null;
    }

    /**
     * @return array<string, string> module slug => route name
     */
    public static function entryRoutes(): array
    {
        return [
            'vendors' => 'admin.vendors.index',
            'products' => 'admin.vendor-products.index',
            'consultants' => 'admin.consultants.index',
            'service_providers' => 'admin.service_providers.index',
            'offers' => 'offers.index',
            'ads' => 'ads.index',
        ];
    }

    /**
     * Map an admin route name to its RBAC module slug.
     */
    public static function moduleForAdminRoute(?string $routeName): ?string
    {
        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        $prefixMap = [
            'admin.vendors.' => 'vendors',
            'admin.vendor-products.' => 'products',
            'admin.consultants.' => 'consultants',
            'admin.consultant-services.' => 'consultants',
            'admin.service_providers.' => 'service_providers',
            'admin.service-provider-services.' => 'service_providers',
            'admin.offers.' => 'offers',
            'admin.offer-prices.' => 'offers',
            'offers.' => 'offers',
            'admin.ads.' => 'ads',
            'ads.' => 'ads',
        ];

        foreach ($prefixMap as $prefix => $module) {
            if (str_starts_with($routeName, $prefix)) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Resolve the RBAC action required for an admin/workspace route from the sidebar registry.
     */
    public static function actionForRoute(?string $routeName): ?string
    {
        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        foreach (ModuleSidebar::sections() as $section) {
            foreach ($section['items'] as $item) {
                if (! self::routeMatchesPattern($routeName, $item['active'])) {
                    continue;
                }

                foreach ($item['active_except'] ?? [] as $except) {
                    if (self::routeMatchesPattern($routeName, $except)) {
                        continue 2;
                    }
                }

                return $item['action'];
            }
        }

        return null;
    }

    private static function routeMatchesPattern(string $routeName, string $pattern): bool
    {
        $regex = '/^'.str_replace(['.', '*'], ['\.', '.*'], $pattern).'$/';

        return (bool) preg_match($regex, $routeName);
    }
}
