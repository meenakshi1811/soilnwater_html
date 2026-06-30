<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PremiumPageController extends Controller
{
    public function show(string $type): View
    {
        $config = $this->resolveTypeConfig($type);

        if ($config === null) {
            abort(404);
        }

        return view('frontend.premium.show', [
            'type' => $type,
            'config' => $config,
            'allTypes' => $this->allTypes(),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function allTypes(): array
    {
        return [
            'vendor' => $this->typeConfig(
                label: 'Vendors',
                singular: 'Vendor',
                icon: 'fa-store',
                color: 'green',
                tagline: 'Showcase Your Products',
                listingLabel: 'Vendor Listing Page',
                profileLabel: 'vendor profile',
            ),
            'consultant' => $this->typeConfig(
                label: 'Consultants',
                singular: 'Consultant',
                icon: 'fa-user-tie',
                color: 'blue',
                tagline: 'Build Trust & Get More Clients',
                listingLabel: 'Consultant Listing Page',
                profileLabel: 'consultant profile',
            ),
            'service' => $this->typeConfig(
                label: 'Service Providers',
                singular: 'Service Provider',
                icon: 'fa-screwdriver-wrench',
                color: 'orange',
                tagline: 'Promote Your Services for More Leads',
                listingLabel: 'Service Listing Page',
                profileLabel: 'service profile',
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveTypeConfig(string $type): ?array
    {
        return $this->allTypes()[$type] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function typeConfig(
        string $label,
        string $singular,
        string $icon,
        string $color,
        string $tagline,
        string $listingLabel,
        string $profileLabel,
    ): array {
        return [
            'label' => $label,
            'singular' => $singular,
            'icon' => $icon,
            'color' => $color,
            'tagline' => $tagline,
            'listing_label' => $listingLabel,
            'profile_label' => $profileLabel,
            'meta_title' => "Get Premium – {$singular} | SoilnWater",
            'meta_description' => "Upgrade your {$profileLabel} to premium on SoilnWater for more visibility, enquiries, and business growth.",
            'free_features' => [
                'Website-like '.$profileLabel.'s',
                'Personalized Website URL & QR Code',
                'Easy editing and formatting',
                'User-friendly website designing',
                'No need for Domain Name, Hosting, or Website development',
                'Complete Website Pages (Home, Products/Services, About, Contact)',
                'Optional e-commerce selling',
                'Location-based discovery',
                'Social media promotion options',
                'Ads + Offers integration',
                'Free & Premium monetization',
                'Ads displayed on free listing pages',
            ],
            'premium_features' => [
                'Quotation Enquiry & Reply – receive and send quotations',
                'Analytics for Premium Users – profile views and customer insights',
                'Preference in Search Results – appear higher when customers search',
                'No Ads on Premium Listings – clean, professional experience',
                "Preference on {$listingLabel} – top placement on the main directory",
                'Premium Tag – trusted badge on your profile',
                'Preference in E-commerce Listing – priority product placement',
            ],
        ];
    }
}
