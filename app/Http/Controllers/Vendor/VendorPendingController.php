<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorPendingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user?->isVendor()) {
            return redirect()->route('login');
        }

        if ($user->vendor?->isApproved()) {
            return redirect()->route('vendor.dashboard');
        }

        return view('backend.vendor.pending', [
            'vendor' => $user->vendor,
        ]);
    }
}
