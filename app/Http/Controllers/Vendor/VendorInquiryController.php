<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProductInquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $vendorId = $request->user()->vendor?->id;
        $inquiries = VendorProductInquiry::query()
            ->with('product:id,name')
            ->where('vendor_id', $vendorId)
            ->latest()
            ->paginate(15);

        return view('backend.vendor.inquiries.index', compact('inquiries'));
    }
}
