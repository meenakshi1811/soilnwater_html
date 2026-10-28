@extends('frontend.business-profile.layout')

@php
    use App\Support\BusinessProfilePage;

    $approvedServices = collect($approvedServices ?? []);

    $biz = BusinessProfilePage::for('service', $service_provider);
    $bizPremiumType = 'service';
    $bizSections = $service_provider->pageSections;
    $bizOffer = [
        'id' => 'biz-services',
        'navLabel' => 'Services',
        'title' => 'Services',
        'icon' => 'fa-screwdriver-wrench',
        'lead' => 'Services offered by '.$biz['name'],
        'count' => $approvedServices->count(),
        'chips' => BusinessProfilePage::categoryChips($approvedServices, fn ($service) => [$service->category_id, $service->categoryModel?->name ?? $service->category]),
        'partial' => 'frontend.business-profile.partials.service-cards',
        'data' => ['services' => $approvedServices, 'kind' => 'service', 'slug' => $service_provider->slug],
        'empty' => 'No services listed yet.',
    ];
    $bizEnquiry = [
        'action' => route('service_provider.enquiry', $service_provider->slug),
        'serviceField' => 'service_provider_service_id',
        'services' => $approvedServices,
    ];
    $bizAds = [
        'fullPage' => $fullPageAds ?? collect(),
        'supporting' => $supportingAds ?? collect(),
        'compactCard' => 'frontend.service_provider.partials.compact-ad-card',
        'recent' => $service_providerRecentAds ?? collect(),
        'recentCategoryNames' => $selectedCategoryNamesByServiceProviderAdId ?? [],
        'recentLabel' => 'Services',
    ];
@endphp
