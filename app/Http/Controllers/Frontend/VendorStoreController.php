<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\VendorPublicPageController;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorStoreController extends Controller
{
    public function show(string $slug): View
    {
        $vendor = Vendor::query()
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->with(['bannerSlides', 'pageSections'])
            ->firstOrFail();

        return view('frontend.store.show', [
            'vendor' => $vendor,
            'preview' => false,
            'dummyProducts' => VendorPublicPageController::dummyProducts(),
        ]);
    }
}
