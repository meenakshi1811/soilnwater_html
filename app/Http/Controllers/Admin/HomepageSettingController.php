<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
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
            'setting' => HomepageSetting::query()->firstOrCreate([]),
            'sections' => $this->sections(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = HomepageSetting::query()->firstOrCreate([]);

        $validated = $request->validate([
            'hero_banner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'hero_button_text' => 'nullable|string|max:120',
            'hero_button_link' => 'nullable|string|max:255',
            'sections' => 'nullable|array',
        ]);

        if ($request->hasFile('hero_banner_image')) {
            if ($setting->hero_banner_image && File::exists(public_path($setting->hero_banner_image))) {
                File::delete(public_path($setting->hero_banner_image));
            }

            $file = $request->file('hero_banner_image');
            $path = 'uploads/homepage/hero-'.Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/homepage'), basename($path));
            $validated['hero_banner_image'] = $path;
        }

        $sections = [];
        foreach (array_keys($this->sections()) as $key) {
            $sections[$key] = in_array($key, $request->input('sections', []), true);
        }

        $validated['section_toggles'] = $sections;
        unset($validated['sections']);

        $setting->fill($validated)->save();

        return back()->with('success', 'Homepage settings updated successfully.');
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
        ];
    }
}
