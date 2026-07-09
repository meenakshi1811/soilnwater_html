<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Services\MarketplacePortalAnalyticsService;
use Illuminate\View\View;

class ServiceProviderDashboardController extends Controller
{
    public function dashboard(): View
    {
        $service_provider = auth()->user()->serviceProvider;
        $service_provider->loadCount([
            'branches',
            'bannerSlides',
            'pageSections',
            'services',
            'services as approved_services_count' => fn ($query) => $query->where('status', 'approved'),
            'services as pending_services_count' => fn ($query) => $query->where('status', 'pending'),
        ]);

        return view('backend.service_provider.dashboard', [
            'service_provider' => $service_provider,
            'analytics' => MarketplacePortalAnalyticsService::forServiceProvider($service_provider),
            'stats' => [
                [
                    'label' => 'Services',
                    'value' => $service_provider->services_count,
                    'detail' => sprintf(
                        '%s approved · %s pending',
                        number_format($service_provider->approved_services_count),
                        number_format($service_provider->pending_services_count)
                    ),
                    'url' => route('service_provider.services.index'),
                    'class' => 'stat-purple',
                ],
                [
                    'label' => 'Branches',
                    'value' => $service_provider->branches_count,
                    'detail' => 'Manage every active service location',
                    'url' => route('service_provider.branches.index'),
                    'class' => 'stat-blue',
                ],
                [
                    'label' => 'Banner slides',
                    'value' => $service_provider->banner_slides_count,
                    'detail' => 'Slides shown on your public service page',
                    'url' => route('service_provider.public-page.edit'),
                    'class' => 'stat-cyan',
                ],
                [
                    'label' => 'Page sections',
                    'value' => $service_provider->page_sections_count,
                    'detail' => 'Custom content blocks on your service profile',
                    'url' => route('service_provider.public-page.edit'),
                    'class' => 'stat-orange',
                ],
            ],
        ]);
    }
}
