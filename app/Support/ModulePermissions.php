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
            'users' => 'Users',
            'vendors' => 'Vendors',
            'services' => 'Services',
            'properties' => 'Properties',
            'builders' => 'Builders',
            'consultants' => 'Consultants',
            'service_providers' => 'Services',
            'educators' => 'Teachers & Tutors',
            'schools' => 'Schools',
            'institutes' => 'Institutes',
            'enquiry' => 'Enquiry',
            'products' => 'Products',
            'offers' => 'Offers',
            'ads' => 'Ads',
            'user_enquiry' => 'User Enquery',
            'community' => 'Community',
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
            'users' => 'admin.users.index',
            'vendors' => 'admin.vendors.index',
            'products' => 'admin.vendor-products.index',
            'consultants' => 'admin.consultants.index',
            'service_providers' => 'admin.service_providers.index',
            'educators' => 'admin.educators.index',
            'schools' => 'admin.schools.index',
            'institutes' => 'admin.institutes.index',
            'offers' => 'offers.index',
            'ads' => 'ads.index',
            'community' => 'admin.community-posts.index',
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
            'admin.users.' => 'users',
            'admin.vendors.' => 'vendors',
            'admin.vendor-products.' => 'products',
            'admin.consultants.' => 'consultants',
            'admin.consultant-services.' => 'consultants',
            'admin.service_providers.' => 'service_providers',
            'admin.service-provider-services.' => 'service_providers',
            'admin.educators.' => 'educators',
            'admin.study-materials.' => 'educators',
            'admin.schools.' => 'schools',
            'admin.institutes.' => 'institutes',
            'admin.offers.' => 'offers',
            'admin.offer-prices.' => 'offers',
            'offers.' => 'offers',
            'admin.ads.' => 'ads',
            'ads.' => 'ads',
            'admin.community-posts.' => 'community',
            'admin.community-chats.' => 'community',
            'admin.foul-words.' => 'community',
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

        $overrides = [
            'admin.users.index' => 'read',
            'admin.users.data' => 'read',
            'admin.users.show' => 'read',
            'admin.users.store' => 'add',
            'admin.users.update' => 'write',
            'admin.users.destroy' => 'delete',
            'admin.users.toggle-block' => 'write',
            'admin.users.toggle-status' => 'write',

            // Community: reviewing posts needs "approve", moderation changes "add"/"write"/"delete"
            'admin.community-posts.approve' => 'approve',
            'admin.community-posts.reject' => 'approve',
            'admin.community-posts.decline' => 'approve',
            'admin.community-posts.draft' => 'approve',
            'admin.community-posts.archive' => 'approve',
            'admin.community-posts.feature' => 'approve',
            'admin.community-posts.sponsor' => 'approve',
            'admin.community-posts.highlight' => 'approve',
            'admin.community-posts.quality-score' => 'approve',
            'admin.community-posts.recalculate-score' => 'approve',
            'admin.community-posts.article-badge' => 'approve',
            'admin.community-posts.reports.delete-post' => 'delete',
            'admin.community-chats.users.toggle-block' => 'write',
            'admin.foul-words.store' => 'add',
            'admin.foul-words.update' => 'write',
            'admin.foul-words.toggle-status' => 'write',
            'admin.foul-words.destroy' => 'delete',
        ];

        if (isset($overrides[$routeName])) {
            return $overrides[$routeName];
        }

        // Editing a school's or institute's profile, public page, diary or jobs
        if (preg_match('/^admin\.(schools|institutes)\.manage\./', $routeName)) {
            return 'write';
        }

        foreach (ModuleSidebar::sections() as $section) {
            // A section lists its items directly or splits them into groups (e.g. "Offers & Ads")
            $items = $section['items'] ?? [];
            foreach ($section['groups'] ?? [] as $group) {
                $items = array_merge($items, $group['items'] ?? []);
            }

            foreach ($items as $item) {
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
