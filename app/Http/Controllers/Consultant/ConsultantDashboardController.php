<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Services\MarketplacePortalAnalyticsService;
use Illuminate\View\View;

class ConsultantDashboardController extends Controller
{
    public function dashboard(): View
    {
        $consultant = auth()->user()->consultant;
        $consultant->loadCount([
            'branches',
            'bannerSlides',
            'pageSections',
            'services',
            'services as approved_services_count' => fn ($query) => $query->where('status', 'approved'),
            'services as pending_services_count' => fn ($query) => $query->where('status', 'pending'),
        ]);

        return view('backend.consultant.dashboard', [
            'consultant' => $consultant,
            'analytics' => MarketplacePortalAnalyticsService::forConsultant($consultant),
            'stats' => [
                [
                    'label' => 'Services',
                    'value' => $consultant->services_count,
                    'detail' => sprintf(
                        '%s approved · %s pending',
                        number_format($consultant->approved_services_count),
                        number_format($consultant->pending_services_count)
                    ),
                    'url' => route('consultant.services.index'),
                    'class' => 'stat-purple',
                ],
                [
                    'label' => 'Branches',
                    'value' => $consultant->branches_count,
                    'detail' => 'Manage every active consultant location',
                    'url' => route('consultant.branches.index'),
                    'class' => 'stat-blue',
                ],
                [
                    'label' => 'Banner slides',
                    'value' => $consultant->banner_slides_count,
                    'detail' => 'Slides shown on your public consultant page',
                    'url' => route('consultant.public-page.edit'),
                    'class' => 'stat-cyan',
                ],
                [
                    'label' => 'Page sections',
                    'value' => $consultant->page_sections_count,
                    'detail' => 'Custom content blocks on your consultant profile',
                    'url' => route('consultant.public-page.edit'),
                    'class' => 'stat-orange',
                ],
            ],
        ]);
    }
}
