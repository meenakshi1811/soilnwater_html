<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomepageSettingController extends Controller
{
    public function edit(): View
    {
        return view('backend.homepage-settings.edit', [
            'setting' => HomepageSetting::query()->firstOrCreate(['id' => 1]),
            'sections' => $this->sections(),
        ]);
    }

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $setting = HomepageSetting::query()->firstOrCreate(['id' => 1]);
        $settingType = $request->string('setting_type')->toString() ?: 'homepage';

        $validated = match ($settingType) {
            'offers' => $request->validate([
                'offers_market_banner_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]),
            'ads' => $request->validate([
                'ads_market_banner_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]),
            default => $request->validate([
                'hero_banner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
                'hero_button_text' => 'required|string|max:120',
                'hero_button_link' => 'required|string|max:255',
                'sections' => 'nullable|array',
                'vendor_enquiry_send_to' => 'required|in:all,non_premium,premium',
                'consultant_enquiry_send_to' => 'required|in:all,non_premium,premium',
                'service_provider_enquiry_send_to' => 'required|in:all,non_premium,premium',
            ]),
        };

        if ($request->hasFile('hero_banner_image')) {
            if ($setting->hero_banner_image && File::exists(public_path($setting->hero_banner_image))) {
                File::delete(public_path($setting->hero_banner_image));
            }

            $file = $request->file('hero_banner_image');
            $path = 'uploads/homepage/hero-'.Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/homepage'), basename($path));
            $validated['hero_banner_image'] = $path;
        }

        if ($request->hasFile('offers_market_banner_image')) {
            if ($setting->offers_market_banner_image && File::exists(public_path($setting->offers_market_banner_image))) {
                File::delete(public_path($setting->offers_market_banner_image));
            }

            $file = $request->file('offers_market_banner_image');
            
            $path = 'uploads/homepage/offers-market-'.Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/homepage'), basename($path));
            $validated['offers_market_banner_image'] = $path;
        }

        if ($request->hasFile('ads_market_banner_image')) {
            if ($setting->ads_market_banner_image && File::exists(public_path($setting->ads_market_banner_image))) {
                File::delete(public_path($setting->ads_market_banner_image));
            }

            $file = $request->file('ads_market_banner_image');
            $path = 'uploads/homepage/ads-market-'.Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/homepage'), basename($path));
            $validated['ads_market_banner_image'] = $path;
        }

        if ($settingType === 'homepage') {
            $sections = [];
            foreach (array_keys($this->sections()) as $key) {
                $sections[$key] = in_array($key, $request->input('sections', []), true);
            }

            $validated['section_toggles'] = $sections;
            unset($validated['sections']);
        }
       

        $setting->fill($validated)->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Page settings updated successfully.',
            ]);
        }

        return back()->with('success', 'Page settings updated successfully.');
    }

    private function sections(): array
    {
        return [
            'top_categories' => 'Top Categories',
            'sponsored_listings' => 'Sponsored Listings',
            'ecommerce' => 'E-Commerce',
            'recent_ads' => 'Recent Ads',
            'offer_discount' => 'Offer & Discount',
            'explore_products' => 'Explore Products Near You',
            'top_vendors' => 'Top Vendors',
            'popular_properties_near_greenwood' => 'Popular Properties Near Greenwood',
            'popular_properties' => 'Popular Properties',
            'builders_developers' => 'Builders & Developers',
            'popular_services' => 'Popular Services',
            'consultants_enquiry' => 'Consultants & Enquiry',
            'vendor_enquiry' => 'Vendor Enquiry',
            'premium_options' => 'Premium Vendor / Consultant / Service Options',
        ];
    }
}
