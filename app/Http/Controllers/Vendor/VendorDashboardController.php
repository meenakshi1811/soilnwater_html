<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class VendorDashboardController extends Controller
{
    public function dashboard(): View
    {
        $vendor = auth()->user()->vendor;
        $vendor->loadCount(['branches', 'bannerSlides', 'pageSections']);

        return view('backend.vendor.dashboard', [
            'vendor' => $vendor,
            'stats' => [
                'branches' => $vendor->branches_count,
                'banner_slides' => $vendor->banner_slides_count,
                'page_sections' => $vendor->page_sections_count,
                'products' => 12,
            ],
        ]);
    }
}
