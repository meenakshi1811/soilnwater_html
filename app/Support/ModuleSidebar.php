<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Permission-aware sidebar sections for admin module workspaces.
 */
final class ModuleSidebar
{
    /**
     * @return array<string, array{label: string, icon: string, active_routes: list<string>, items: list<array{label: string, route: string, icon: string, module: string, action: string, active: string}>}>
     */
    public static function sections(): array
    {
        return [
            'users' => [
                'label' => 'Users',
                'icon' => 'fa-solid fa-users',
                'active_routes' => ['admin.users.*'],
                'items' => [
                    ['label' => 'All Users', 'route' => 'admin.users.index', 'icon' => 'fa-solid fa-list', 'module' => 'users', 'action' => 'read', 'active' => 'admin.users.*'],
                ],
            ],
            'offers' => [
                'label' => 'Offers',
                'icon' => 'fa-solid fa-tags',
                'active_routes' => ['offers.*', 'admin.offers.*', 'admin.offer-prices.*'],
                'items' => [
                    ['label' => 'All Offers', 'route' => 'offers.index', 'icon' => 'fa-solid fa-list', 'module' => 'offers', 'action' => 'read', 'active' => 'offers.*'],
                    ['label' => 'Report Offers', 'route' => 'admin.offers.reports.index', 'icon' => 'fa-regular fa-flag', 'module' => 'offers', 'action' => 'read', 'active' => 'admin.offers.reports.*'],
                    ['label' => 'Offer Prices', 'route' => 'admin.offer-prices.index', 'icon' => 'fa-solid fa-indian-rupee-sign', 'module' => 'offers', 'action' => 'write', 'active' => 'admin.offer-prices.*'],
                ],
            ],
            'ads' => [
                'label' => 'Ads',
                'icon' => 'fa-solid fa-rectangle-ad',
                'active_routes' => ['ads.*', 'admin.ads.*'],
                'items' => [
                    ['label' => 'All Ads', 'route' => 'ads.index', 'icon' => 'fa-solid fa-rectangle-list', 'module' => 'ads', 'action' => 'read', 'active' => 'ads.*'],
                    ['label' => 'Ad Sizes', 'route' => 'admin.ads.sizes.index', 'icon' => 'fa-solid fa-ruler-combined', 'module' => 'ads', 'action' => 'write', 'active' => 'admin.ads.sizes.*'],
                    ['label' => 'Ad Submissions', 'route' => 'admin.ads.submissions.index', 'icon' => 'fa-solid fa-inbox', 'module' => 'ads', 'action' => 'approve', 'active' => 'admin.ads.submissions.*'],
                    ['label' => 'Report Ads', 'route' => 'admin.ads.reports.index', 'icon' => 'fa-regular fa-flag', 'module' => 'ads', 'action' => 'read', 'active' => 'admin.ads.reports.*'],
                    ['label' => 'Contact Support', 'route' => 'admin.ads.contact-support.index', 'icon' => 'fa-regular fa-envelope', 'module' => 'ads', 'action' => 'read', 'active' => 'admin.ads.contact-support.*'],
                ],
            ],
            'vendors' => [
                'label' => 'Vendor',
                'icon' => 'fa-solid fa-store',
                'active_routes' => ['admin.vendors.*', 'admin.vendor-products.*'],
                'items' => [
                    ['label' => 'All Vendors', 'route' => 'admin.vendors.index', 'icon' => 'fa-solid fa-list', 'module' => 'vendors', 'action' => 'read', 'active' => 'admin.vendors.*'],
                    ['label' => 'Create Product', 'route' => 'admin.vendor-products.create', 'icon' => 'fa-solid fa-plus', 'module' => 'products', 'action' => 'add', 'active' => 'admin.vendor-products.create'],
                    ['label' => 'Products Approval', 'route' => 'admin.vendor-products.index', 'icon' => 'fa-solid fa-boxes-stacked', 'module' => 'products', 'action' => 'approve', 'active' => 'admin.vendor-products.*', 'active_except' => ['admin.vendor-products.all.*', 'admin.vendor-products.create']],
                    ['label' => 'All Products', 'route' => 'admin.vendor-products.all.index', 'icon' => 'fa-solid fa-rectangle-list', 'module' => 'products', 'action' => 'read', 'active' => 'admin.vendor-products.all.*'],
                ],
            ],
            'consultants' => [
                'label' => 'Consultants',
                'icon' => 'fa-solid fa-user-tie',
                'active_routes' => ['admin.consultants.*', 'admin.consultant-services.*'],
                'items' => [
                    ['label' => 'All Consultants', 'route' => 'admin.consultants.index', 'icon' => 'fa-solid fa-list', 'module' => 'consultants', 'action' => 'read', 'active' => 'admin.consultants.*', 'active_except' => ['admin.consultants.reports.*']],
                    ['label' => 'Create Service', 'route' => 'admin.consultant-services.create', 'icon' => 'fa-solid fa-plus', 'module' => 'consultants', 'action' => 'add', 'active' => 'admin.consultant-services.create'],
                    ['label' => 'Services Approval', 'route' => 'admin.consultant-services.index', 'icon' => 'fa-solid fa-clipboard-check', 'module' => 'consultants', 'action' => 'approve', 'active' => 'admin.consultant-services.*', 'active_except' => ['admin.consultant-services.all.*', 'admin.consultant-services.create']],
                    ['label' => 'All Services', 'route' => 'admin.consultant-services.all.index', 'icon' => 'fa-solid fa-rectangle-list', 'module' => 'consultants', 'action' => 'read', 'active' => 'admin.consultant-services.all.*'],
                    ['label' => 'Report Consultants', 'route' => 'admin.consultants.reports.index', 'icon' => 'fa-regular fa-flag', 'module' => 'consultants', 'action' => 'read', 'active' => 'admin.consultants.reports.*'],
                ],
            ],
            'service_providers' => [
                'label' => 'Services',
                'icon' => 'fa-solid fa-screwdriver-wrench',
                'active_routes' => ['admin.service_providers.*', 'admin.service-provider-services.*'],
                'items' => [
                    ['label' => 'All Service Providers', 'route' => 'admin.service_providers.index', 'icon' => 'fa-solid fa-list', 'module' => 'service_providers', 'action' => 'read', 'active' => 'admin.service_providers.*', 'active_except' => ['admin.service_providers.reports.*']],
                    ['label' => 'Create Service', 'route' => 'admin.service-provider-services.create', 'icon' => 'fa-solid fa-plus', 'module' => 'service_providers', 'action' => 'add', 'active' => 'admin.service-provider-services.create'],
                    ['label' => 'Services Approval', 'route' => 'admin.service-provider-services.index', 'icon' => 'fa-solid fa-clipboard-check', 'module' => 'service_providers', 'action' => 'approve', 'active' => 'admin.service-provider-services.*', 'active_except' => ['admin.service-provider-services.all.*', 'admin.service-provider-services.create']],
                    ['label' => 'All Services', 'route' => 'admin.service-provider-services.all.index', 'icon' => 'fa-solid fa-rectangle-list', 'module' => 'service_providers', 'action' => 'read', 'active' => 'admin.service-provider-services.all.*'],
                    ['label' => 'Report Services', 'route' => 'admin.service_providers.reports.index', 'icon' => 'fa-regular fa-flag', 'module' => 'service_providers', 'action' => 'read', 'active' => 'admin.service_providers.reports.*'],
                ],
            ],
        ];
    }

    /**
     * @return list<array{key: string, label: string, icon: string, active: bool, items: list<array{label: string, route: string, icon: string, active: bool}>}>
     */
    public static function visibleSections(?Authenticatable $user, bool $isAdmin): array
    {
        $sections = [];

        foreach (self::sections() as $key => $section) {
            $items = [];

            foreach ($section['items'] as $item) {
                if (! self::canSeeItem($user, $isAdmin, $item['module'], $item['action'])) {
                    continue;
                }

                if (! \Route::has($item['route'])) {
                    continue;
                }

                $items[] = [
                    'label' => $item['label'],
                    'route' => $item['route'],
                    'icon' => $item['icon'],
                    'active' => self::itemIsActive($item),
                ];
            }

            if ($items === []) {
                continue;
            }

            $sections[] = [
                'key' => $key,
                'label' => $section['label'],
                'icon' => $section['icon'],
                'active' => self::sectionIsActive($section),
                'items' => $items,
            ];
        }

        return $sections;
    }

    public static function canSeeItem(?Authenticatable $user, bool $isAdmin, string $module, string $action): bool
    {
        if ($isAdmin) {
            return true;
        }

        if (! $user || ! method_exists($user, 'canModule')) {
            return false;
        }

        return $user->canModule($module, $action);
    }

    /**
     * @param  array{active_routes: list<string>}  $section
     */
    public static function sectionIsActive(array $section): bool
    {
        foreach ($section['active_routes'] as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{active: string, active_except?: list<string>}  $item
     */
    public static function itemIsActive(array $item): bool
    {
        if (! request()->routeIs($item['active'])) {
            return false;
        }

        foreach ($item['active_except'] ?? [] as $except) {
            if (request()->routeIs($except)) {
                return false;
            }
        }

        return true;
    }
}
