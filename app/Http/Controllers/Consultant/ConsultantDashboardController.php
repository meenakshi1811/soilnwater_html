<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
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
        ]);

        return view('backend.consultant.dashboard', [
            'consultant' => $consultant,
            'stats' => [
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
