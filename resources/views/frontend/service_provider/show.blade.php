@extends('frontend.service_provider.layout')

@section('title', $service_provider->publicDisplayName().' – Service')

@section('service_provider_content')
@php
    $service_providerRecentAds = $service_providerRecentAds ?? collect();
    $selectedCategoryNamesByServiceProviderAdId = $selectedCategoryNamesByServiceProviderAdId ?? [];
    $fullPageAds = $fullPageAds ?? collect();
    $supportingAds = collect($supportingAds ?? [])->values();
    $serviceSectionAds = $approvedServices->isNotEmpty() ? $supportingAds->take(2)->values() : collect();
    $distributedAds = $supportingAds->slice($serviceSectionAds->count())->values();
    $adsPerContentSection = 2;
    $recentAdsShown = false;
@endphp

<section class="vendor-store-hero">
    @if($service_provider->bannerSlides->count())
        <div id="service_providerHeroCarousel" class="carousel slide h-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                @foreach($service_provider->bannerSlides as $i => $slide)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <img src="{{ asset($slide->image_path) }}" alt="{{ $service_provider->publicDisplayName() }} banner {{ $i + 1 }}" class="vendor-store-hero__image">
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>


<section class="vendor-hero-text-section">
    <div class="container">
        <h1 style="@if(!empty($service_provider->hero_main_style)){{ collect($service_provider->hero_main_style)->filter(fn($v) => filled($v))->map(fn($v, $k) => \Illuminate\Support\Str::kebab($k).':'.$v)->implode(';') }}@endif">{{ $service_provider->hero_main_heading ?: $service_provider->publicDisplayName() }}</h1>
        @if($service_provider->hero_sub_heading)
            <div class="lead mb-0 opacity-90" style="white-space: pre-line;@if(!empty($service_provider->hero_sub_style)){{ collect($service_provider->hero_sub_style)->filter(fn($v) => filled($v))->map(fn($v, $k) => \Illuminate\Support\Str::kebab($k).':'.$v)->implode(';') }}@endif">{!! html_entity_decode($service_provider->hero_sub_heading) !!}</div>
        @endif
    </div>
</section>

@php($professionalBranches = $service_provider->branches->filter(fn ($branch) => filled($branch->professional_experience) || filled($branch->services_offered)))
@if($professionalBranches->isNotEmpty())
    <section class="vendor-store-professional-details">
        <div class="container">
            <div class="vendor-store-professional-details__heading">
                <span>Expertise</span>
                <h2>Professional Experience &amp; Services Offered</h2>
            </div>
            <div class="row g-4">
                @foreach($professionalBranches as $branch)
                    <div class="col-12 {{ $professionalBranches->count() > 1 ? 'col-lg-6' : '' }}">
                        <article class="vendor-store-professional-card h-100">
                            <div class="vendor-store-professional-card__header">
                                <h3>{{ $branch->branch_name }}</h3>
                                @if($branch->is_primary)
                                    <span>Primary branch</span>
                                @endif
                            </div>
                            <div class="row g-4">
                                @if(filled($branch->professional_experience))
                                    <div class="col-md-6">
                                        <h4><i class="fa-solid fa-award"></i> Professional Experience</h4>
                                        <p>{{ $branch->professional_experience }}</p>
                                    </div>
                                @endif
                                @if(filled($branch->services_offered))
                                    <div class="col-md-6">
                                        <h4><i class="fa-solid fa-list-check"></i> Services Offered</h4>
                                        <p>{{ $branch->services_offered }}</p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@include('frontend.service_provider.partials.ads-zone', ['ads' => $fullPageAds])

@include('frontend.service_provider.partials.services-section', [
    'showViewAllServicesButton' => true,
    'inlineAds' => $serviceSectionAds,
])

@foreach($service_provider->pageSections as $section)
    @php($sectionHasVideo = str_contains((string) $section->content, 'vendor-section-video'))
    <section id="section-{{ $section->id }}" class="vendor-store-section {{ $loop->even ? 'alt' : '' }} vendor-custom-section {{ $sectionHasVideo ? 'has-video-section' : '' }}">
        <div class="container">
            <div class="vendor-section-title-display">{!! $section->title !!}</div>
            <div class="row g-4 align-items-center">
                @if($section->image_path)
                    <div class="col-md-6">
                        <img src="{{ asset($section->image_path) }}" alt="{{ strip_tags($section->title) }}" class="section-img">
                    </div>
                @endif
                <div class="{{ $section->image_path ? 'col-md-6' : 'col-12' }}">
                    <div class="content-body">{!! $section->content !!}</div>
                </div>
            </div>
        </div>
    </section>

    @include('frontend.partials.profile-inline-ads', [
        'ads' => $distributedAds->slice($loop->index * $adsPerContentSection, $adsPerContentSection),
        'placementId' => 'serviceProviderContentAds'.$loop->index,
    ])

    @if(str_contains((string) $section->content, 'data-card-image-slot') && ! $recentAdsShown)
        @include('frontend.service_provider.partials.recent-ads-slider', ['ads' => $service_providerRecentAds, 'selectedCategoryNamesByAdId' => $selectedCategoryNamesByServiceProviderAdId])
        @php($recentAdsShown = true)
    @endif
@endforeach

@foreach($distributedAds->slice($service_provider->pageSections->count() * $adsPerContentSection)->chunk($adsPerContentSection) as $remainingAdGroup)
    @include('frontend.partials.profile-inline-ads', [
        'ads' => $remainingAdGroup,
        'placementId' => 'serviceProviderRemainingAds'.$loop->index,
    ])
@endforeach

@if(! $recentAdsShown)
    @include('frontend.service_provider.partials.recent-ads-slider', ['ads' => $service_providerRecentAds, 'selectedCategoryNamesByAdId' => $selectedCategoryNamesByServiceProviderAdId])
@endif

<div class="modal fade consultant-login-required-modal" id="service_providerLoginRequiredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title">You are not logged in</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="consultant-login-required-card">
                    <div class="consultant-login-required-card__icon"><i class="fa-solid fa-lock"></i></div>
                    <div>
                        <h4>You are not logged in</h4>
                        <p>Please log in to view these service details and share options.</p>
                        <a href="{{ route('login') }}" class="consultant-login-required-card__btn">Login to continue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="service_providerSectionImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">Image preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0 text-center">
                <img id="service_providerSectionImageModalImg" src="" alt="Store section image" class="img-fluid rounded" style="max-height:80vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('service_provider_scripts')


@if($service_provider->bannerSlides->count() > 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('service_providerHeroCarousel');
    if (el) new bootstrap.Carousel(el, { interval: 5000 });
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('service_providerSectionImageModal');
    const modalImg = document.getElementById('service_providerSectionImageModalImg');
    if (modalEl && modalImg && typeof bootstrap !== 'undefined') {
        const previewModal = new bootstrap.Modal(modalEl);

        document.querySelectorAll('.content-body [data-brochure-image-slot], .content-body [data-brochure-image-list] img').forEach(function (img) {
            img.addEventListener('click', function () {
                if (!img.src) return;
                modalImg.src = img.src;
                modalImg.alt = img.alt || 'Store section image';
                previewModal.show();
            });
        });
    }
});
</script>
@endpush
