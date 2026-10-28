@extends('frontend.business-profile.layout')

@php
    use App\Support\BusinessProfilePage;

    $approvedServices = collect($approvedServices ?? []);

    $biz = BusinessProfilePage::for('consultant', $consultant);
    $bizPremiumType = 'consultant';
    $bizSections = $consultant->pageSections;
    $bizOffer = [
        'id' => 'biz-services',
        'navLabel' => 'Services',
        'title' => 'Consultation services',
        'icon' => 'fa-briefcase',
        'lead' => 'Consultations offered by '.$biz['name'],
        'count' => $approvedServices->count(),
        'chips' => BusinessProfilePage::categoryChips($approvedServices, fn ($service) => [$service->category_id, $service->categoryModel?->name ?? $service->category]),
        'partial' => 'frontend.business-profile.partials.service-cards',
        'data' => ['services' => $approvedServices, 'kind' => 'consultant', 'slug' => $consultant->slug],
        'empty' => 'No consultation services listed yet.',
    ];
    $bizEnquiry = [
        'action' => route('consultant.enquiry', $consultant->slug),
        'serviceField' => 'consultant_service_id',
        'services' => $approvedServices,
    ];
    $bizAds = [
        'fullPage' => $fullPageAds ?? collect(),
        'supporting' => $supportingAds ?? collect(),
        'compactCard' => 'frontend.consultant.partials.compact-ad-card',
        'recent' => $consultantRecentAds ?? collect(),
        'recentCategoryNames' => $selectedCategoryNamesByConsultantAdId ?? [],
        'recentLabel' => 'Consultants',
    ];
@endphp
