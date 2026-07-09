<?php

namespace App\Services;

use App\Models\Consultant;
use App\Models\ConsultantServiceInquiry;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderServiceInquiry;
use App\Models\Vendor;
use App\Models\VendorProductInquiry;
use Illuminate\Database\Eloquent\Builder;

class MarketplacePortalAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public static function forVendor(Vendor $vendor): array
    {
        $query = VendorProductInquiry::query()->where('vendor_id', $vendor->id);
        $totalEnquiries = (clone $query)->count();
        $productEnquiries = (clone $query)->whereNotNull('vendor_product_id')->count();
        $generalEnquiries = (clone $query)->whereNull('vendor_product_id')->count();
        $monthEnquiries = self::periodCount($query, 'this month');
        $totalProducts = (int) ($vendor->products_count ?? 0);
        $approvedProducts = (int) ($vendor->approved_products_count ?? 0);

        return self::buildPayload(
            inquiryQuery: $query,
            inquiriesUrl: route('vendor.inquiries.index'),
            metrics: [
                [
                    'label' => 'Total Enquiries',
                    'value' => $totalEnquiries,
                    'icon' => 'fa-envelope-open-text',
                    'hint' => $monthEnquiries.' this month',
                ],
                [
                    'label' => 'Product Enquiries',
                    'value' => $productEnquiries,
                    'icon' => 'fa-box',
                    'hint' => 'Linked to a product listing',
                ],
                [
                    'label' => 'General Enquiries',
                    'value' => $generalEnquiries,
                    'icon' => 'fa-store',
                    'hint' => 'Store-wide requests',
                ],
                [
                    'label' => 'Approved Products',
                    'value' => $approvedProducts,
                    'icon' => 'fa-circle-check',
                    'hint' => 'Live in your catalog',
                ],
            ],
            rings: [
                self::ring('Product Enquiries', $productEnquiries, max($totalEnquiries, 1), '#2563eb', $productEnquiries.' product-led enquiries'),
                self::ring('Catalog Approved', $approvedProducts, max($totalProducts, 1), '#16a34a', $approvedProducts.' of '.$totalProducts.' products'),
                self::ring('This Month', $monthEnquiries, max($totalEnquiries, 1), '#d4af37', $monthEnquiries.' enquiries this month'),
            ],
            breakdown: [
                ['label' => 'Product', 'value' => $productEnquiries, 'color' => '#2563eb'],
                ['label' => 'General', 'value' => $generalEnquiries, 'color' => '#06b6d4'],
            ],
            recentMapper: function (VendorProductInquiry $inquiry): array {
                return [
                    'title' => $inquiry->product?->name ?? 'General vendor enquiry',
                    'meta' => trim($inquiry->email.' · '.ucfirst((string) $inquiry->preferred_contact)),
                    'date' => $inquiry->created_at?->diffForHumans() ?? 'Recently',
                ];
            },
            recentWith: ['product:id,name'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function forConsultant(Consultant $consultant): array
    {
        $query = ConsultantServiceInquiry::query()->where('consultant_id', $consultant->id);
        $totalEnquiries = (clone $query)->count();
        $serviceEnquiries = (clone $query)->whereNotNull('consultant_service_id')->count();
        $directEnquiries = max(0, $totalEnquiries - $serviceEnquiries);
        $monthEnquiries = self::periodCount($query, 'this month');
        $totalServices = (int) ($consultant->services_count ?? 0);
        $approvedServices = (int) ($consultant->approved_services_count ?? 0);

        return self::buildPayload(
            inquiryQuery: $query,
            inquiriesUrl: route('consultant.inquiries.index'),
            metrics: [
                [
                    'label' => 'Total Enquiries',
                    'value' => $totalEnquiries,
                    'icon' => 'fa-envelope-open-text',
                    'hint' => $monthEnquiries.' this month',
                ],
                [
                    'label' => 'Service Enquiries',
                    'value' => $serviceEnquiries,
                    'icon' => 'fa-briefcase',
                    'hint' => 'Linked to a consultation service',
                ],
                [
                    'label' => 'Approved Services',
                    'value' => $approvedServices,
                    'icon' => 'fa-circle-check',
                    'hint' => 'Visible on your profile',
                ],
                [
                    'label' => 'Branches',
                    'value' => (int) ($consultant->branches_count ?? 0),
                    'icon' => 'fa-code-branch',
                    'hint' => 'Active consultant locations',
                ],
            ],
            rings: [
                self::ring('Service Enquiries', $serviceEnquiries, max($totalEnquiries, 1), '#2563eb', $serviceEnquiries.' service-linked enquiries'),
                self::ring('Services Approved', $approvedServices, max($totalServices, 1), '#16a34a', $approvedServices.' of '.$totalServices.' services'),
                self::ring('This Month', $monthEnquiries, max($totalEnquiries, 1), '#d4af37', $monthEnquiries.' enquiries this month'),
            ],
            breakdown: [
                ['label' => 'Service-linked', 'value' => $serviceEnquiries, 'color' => '#2563eb'],
                ['label' => 'Direct', 'value' => $directEnquiries, 'color' => '#7c3aed'],
            ],
            recentMapper: function (ConsultantServiceInquiry $inquiry): array {
                return [
                    'title' => $inquiry->service?->name ?? ($inquiry->client_name ?: 'Consultation enquiry'),
                    'meta' => trim(($inquiry->client_name ?: 'Visitor').' · '.($inquiry->phone_number ?: $inquiry->email)),
                    'date' => $inquiry->created_at?->diffForHumans() ?? 'Recently',
                ];
            },
            recentWith: ['service:id,name'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function forServiceProvider(ServiceProvider $serviceProvider): array
    {
        $query = ServiceProviderServiceInquiry::query()->where('service_provider_id', $serviceProvider->id);
        $totalEnquiries = (clone $query)->count();
        $serviceEnquiries = (clone $query)->whereNotNull('service_provider_service_id')->count();
        $directEnquiries = max(0, $totalEnquiries - $serviceEnquiries);
        $monthEnquiries = self::periodCount($query, 'this month');
        $totalServices = (int) ($serviceProvider->services_count ?? 0);
        $approvedServices = (int) ($serviceProvider->approved_services_count ?? 0);

        return self::buildPayload(
            inquiryQuery: $query,
            inquiriesUrl: route('service_provider.inquiries.index'),
            metrics: [
                [
                    'label' => 'Total Enquiries',
                    'value' => $totalEnquiries,
                    'icon' => 'fa-envelope-open-text',
                    'hint' => $monthEnquiries.' this month',
                ],
                [
                    'label' => 'Service Enquiries',
                    'value' => $serviceEnquiries,
                    'icon' => 'fa-screwdriver-wrench',
                    'hint' => 'Linked to a listed service',
                ],
                [
                    'label' => 'Approved Services',
                    'value' => $approvedServices,
                    'icon' => 'fa-circle-check',
                    'hint' => 'Visible on your profile',
                ],
                [
                    'label' => 'Branches',
                    'value' => (int) ($serviceProvider->branches_count ?? 0),
                    'icon' => 'fa-code-branch',
                    'hint' => 'Active service locations',
                ],
            ],
            rings: [
                self::ring('Service Enquiries', $serviceEnquiries, max($totalEnquiries, 1), '#f97316', $serviceEnquiries.' service-linked enquiries'),
                self::ring('Services Approved', $approvedServices, max($totalServices, 1), '#16a34a', $approvedServices.' of '.$totalServices.' services'),
                self::ring('This Month', $monthEnquiries, max($totalEnquiries, 1), '#d4af37', $monthEnquiries.' enquiries this month'),
            ],
            breakdown: [
                ['label' => 'Service-linked', 'value' => $serviceEnquiries, 'color' => '#f97316'],
                ['label' => 'Direct', 'value' => $directEnquiries, 'color' => '#2563eb'],
            ],
            recentMapper: function (ServiceProviderServiceInquiry $inquiry): array {
                return [
                    'title' => $inquiry->service?->name ?? ($inquiry->client_name ?: 'Service enquiry'),
                    'meta' => trim(($inquiry->client_name ?: 'Visitor').' · '.($inquiry->phone_number ?: $inquiry->email)),
                    'date' => $inquiry->created_at?->diffForHumans() ?? 'Recently',
                ];
            },
            recentWith: ['service:id,name'],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $metrics
     * @param  array<int, array<string, mixed>>  $rings
     * @param  array<int, array<string, mixed>>  $breakdown
     * @param  callable(mixed): array{title: string, meta: string, date: string}  $recentMapper
     * @return array<string, mixed>
     */
    private static function buildPayload(
        Builder $inquiryQuery,
        string $inquiriesUrl,
        array $metrics,
        callable $recentMapper,
        array $rings,
        array $breakdown,
        array $recentWith = [],
    ): array {
        $recent = (clone $inquiryQuery)
            ->with($recentWith)
            ->latest()
            ->limit(5)
            ->get()
            ->map($recentMapper)
            ->values()
            ->all();

        return [
            'metrics' => $metrics,
            'recent' => $recent,
            'trend' => self::monthlyTrend($inquiryQuery, 6),
            'rings' => $rings,
            'breakdown' => $breakdown,
            'inquiries_url' => $inquiriesUrl,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function ring(string $label, int $value, int $total, string $color, ?string $hint = null): array
    {
        $percent = $total > 0 ? (int) round(($value / $total) * 100) : 0;

        return [
            'label' => $label,
            'value' => $value,
            'total' => $total,
            'percent' => $percent,
            'color' => $color,
            'hint' => $hint ?? ($value.' of '.max($total, $value)),
        ];
    }

    private static function periodCount(Builder $query, string $period): int
    {
        $start = match ($period) {
            'this month' => now()->startOfMonth(),
            'last 30 days' => now()->subDays(30),
            default => now()->startOfMonth(),
        };

        return (clone $query)->where('created_at', '>=', $start)->count();
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>, max: int}
     */
    private static function monthlyTrend(Builder $query, int $months): array
    {
        $labels = [];
        $values = [];

        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $month = now()->subMonths($offset);
            $labels[] = $month->format('M');
            $values[] = (clone $query)
                ->whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ])
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'max' => max(1, ...$values),
        ];
    }
}
