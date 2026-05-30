<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Number;
use Illuminate\View\View;

class VendorDashboardController extends Controller
{
    public function dashboard(): View
    {
        $vendor = auth()->user()->vendor;
        $vendor->loadCount([
            'products',
            'branches',
            'bannerSlides',
            'pageSections',
            'products as approved_products_count' => fn ($query) => $query->where('status', 'approved'),
            'products as pending_products_count' => fn ($query) => $query->where('status', 'pending'),
            'products as rejected_products_count' => fn ($query) => $query->where('status', 'rejected'),
        ]);

        return view('backend.vendor.dashboard', [
            'vendor' => $vendor,
            'stats' => [
                [
                    'label' => 'Products',
                    'value' => $vendor->products_count,
                    'detail' => sprintf(
                        '%s approved · %s pending · %s rejected',
                        Number::format($vendor->approved_products_count),
                        Number::format($vendor->pending_products_count),
                        Number::format($vendor->rejected_products_count)
                    ),
                    'url' => route('vendor.products.index'),
                    'class' => 'stat-purple',
                ],
                [
                    'label' => 'Branches',
                    'value' => $vendor->branches_count,
                    'detail' => 'Manage every active storefront location',
                    'url' => route('vendor.branches.index'),
                    'class' => 'stat-blue',
                ],
                [
                    'label' => 'Banner slides',
                    'value' => $vendor->banner_slides_count,
                    'detail' => 'Slides shown on your public store hero',
                    'url' => route('vendor.public-page.edit'),
                    'class' => 'stat-cyan',
                ],
                [
                    'label' => 'Page sections',
                    'value' => $vendor->page_sections_count,
                    'detail' => 'Custom content blocks on your store',
                    'url' => route('vendor.public-page.edit'),
                    'class' => 'stat-orange',
                ],
            ],
        ]);
    }
}
