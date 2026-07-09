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

        return self::buildPayload(
            inquiryQuery: $query,
            inquiriesUrl: route('vendor.inquiries.index'),
            metrics: [
                [
                    'label' => 'Total Enquiries',
                    'value' => (clone $query)->count(),
                    'icon' => 'fa-envelope-open-text',
                    'hint' => self::periodCount($query, 'this month').' this month',
                ],
                [
                    'label' => 'Product Enquiries',
                    'value' => (clone $query)->whereNotNull('vendor_product_id')->count(),
                    'icon' => 'fa-box',
                    'hint' => 'Linked to a product listing',
                ],
                [
                    'label' => 'General Enquiries',
                    'value' => (clone $query)->whereNull('vendor_product_id')->count(),
                    'icon' => 'fa-store',
                    'hint' => 'Store-wide requests',
                ],
                [
                    'label' => 'Approved Products',
                    'value' => (int) ($vendor->approved_products_count ?? 0),
                    'icon' => 'fa-circle-check',
                    'hint' => 'Live in your catalog',
                ],
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

        return self::buildPayload(
            inquiryQuery: $query,
            inquiriesUrl: route('consultant.inquiries.index'),
            metrics: [
                [
                    'label' => 'Total Enquiries',
                    'value' => (clone $query)->count(),
                    'icon' => 'fa-envelope-open-text',
                    'hint' => self::periodCount($query, 'this month').' this month',
                ],
                [
                    'label' => 'Service Enquiries',
                    'value' => (clone $query)->whereNotNull('consultant_service_id')->count(),
                    'icon' => 'fa-briefcase',
                    'hint' => 'Linked to a consultation service',
                ],
                [
                    'label' => 'Approved Services',
                    'value' => (int) ($consultant->approved_services_count ?? 0),
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

        return self::buildPayload(
            inquiryQuery: $query,
            inquiriesUrl: route('service_provider.inquiries.index'),
            metrics: [
                [
                    'label' => 'Total Enquiries',
                    'value' => (clone $query)->count(),
                    'icon' => 'fa-envelope-open-text',
                    'hint' => self::periodCount($query, 'this month').' this month',
                ],
                [
                    'label' => 'Service Enquiries',
                    'value' => (clone $query)->whereNotNull('service_provider_service_id')->count(),
                    'icon' => 'fa-screwdriver-wrench',
                    'hint' => 'Linked to a listed service',
                ],
                [
                    'label' => 'Approved Services',
                    'value' => (int) ($serviceProvider->approved_services_count ?? 0),
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
     * @param  callable(mixed): array{title: string, meta: string, date: string}  $recentMapper
     * @return array<string, mixed>
     */
    private static function buildPayload(
        Builder $inquiryQuery,
        string $inquiriesUrl,
        array $metrics,
        callable $recentMapper,
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
            'inquiries_url' => $inquiriesUrl,
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
