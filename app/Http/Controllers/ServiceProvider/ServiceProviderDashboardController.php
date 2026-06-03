<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
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
        ]);

        return view('backend.service_provider.dashboard', [
            'service_provider' => $service_provider,
            'stats' => [
                [
                    'label' => 'Branches',
                    'value' => $service_provider->branches_count,
                    'detail' => 'Manage every active service_provider location',
                    'url' => route('service_provider.branches.index'),
                    'class' => 'stat-blue',
                ],
                [
                    'label' => 'Banner slides',
                    'value' => $service_provider->banner_slides_count,
                    'detail' => 'Slides shown on your public service_provider page',
                    'url' => route('service_provider.public-page.edit'),
                    'class' => 'stat-cyan',
                ],
                [
                    'label' => 'Page sections',
                    'value' => $service_provider->page_sections_count,
                    'detail' => 'Custom content blocks on your service_provider profile',
                    'url' => route('service_provider.public-page.edit'),
                    'class' => 'stat-orange',
                ],
            ],
        ]);
    }
}
