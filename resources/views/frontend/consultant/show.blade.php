@extends('frontend.consultant.layout')

@section('title', $consultant->publicDisplayName().' – Consultant')

@section('consultant_content')
@php
    $consultantRecentAds = $consultantRecentAds ?? collect();
    $selectedCategoryNamesByConsultantAdId = $selectedCategoryNamesByConsultantAdId ?? [];
    $randomFullPagePlacements = $randomFullPagePlacements ?? [];
    $sponsoredFillers = $sponsoredFillers ?? [];
    $recentAdsShown = false;
@endphp

<section class="vendor-store-hero">
    @if($consultant->bannerSlides->count())
        <div id="consultantHeroCarousel" class="carousel slide h-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                @foreach($consultant->bannerSlides as $i => $slide)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <img src="{{ asset($slide->image_path) }}" alt="{{ $consultant->publicDisplayName() }} banner {{ $i + 1 }}" class="vendor-store-hero__image">
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>


<section class="vendor-hero-text-section">
    <div class="container">
        <h1 style="@if(!empty($consultant->hero_main_style)){{ collect($consultant->hero_main_style)->filter(fn($v) => filled($v))->map(fn($v, $k) => \Illuminate\Support\Str::kebab($k).':'.$v)->implode(';') }}@endif">{{ $consultant->hero_main_heading ?: $consultant->publicDisplayName() }}</h1>
        @if($consultant->hero_sub_heading)
            <div class="lead mb-0 opacity-90" style="white-space: pre-line;@if(!empty($consultant->hero_sub_style)){{ collect($consultant->hero_sub_style)->filter(fn($v) => filled($v))->map(fn($v, $k) => \Illuminate\Support\Str::kebab($k).':'.$v)->implode(';') }}@endif">{!! html_entity_decode($consultant->hero_sub_heading) !!}</div>
        @endif
    </div>
</section>

@if(($approvedServices ?? collect())->isNotEmpty())
<section class="vendor-store-section consultant-services-section">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
            <div>
                <p class="text-uppercase text-primary fw-semibold mb-1">Consultation Services</p>
                <h2 class="vendor-section-title-display mb-0">Available consultations</h2>
            </div>
        </div>
        <div class="row g-4">
            @foreach($approvedServices as $service)
                @php($image = $service->image_path)
                <div class="col-sm-6 col-lg-3">
                    <article class="consultant-service-card h-100">
                        <div class="consultant-service-card__image-wrap">
                            @if($image)
                                <img src="{{ asset($image) }}" alt="{{ $service->name }}" class="consultant-service-card__image">
                            @else
                                <div class="consultant-service-card__placeholder"><i class="fa-solid fa-briefcase"></i></div>
                            @endif
                        </div>
                        <div class="consultant-service-card__body">
                            <p class="consultant-service-card__category">{{ $service->categoryModel?->name ?? $service->category ?? 'Consultation' }}</p>
                            <h3>{{ $service->name }}</h3>
                            <p class="consultant-service-card__category mb-2">{{ ucfirst($service->consultation_type ?: ($service->is_online ? 'online' : 'offline')) }}@if($service->business_type) · {{ $service->business_type }}@endif</p>
                            @if($service->short_description)
                                <p class="consultant-service-card__description">{{ $service->short_description }}</p>
                            @endif
                            <div class="consultant-service-card__meta">
                                <span>{{ $service->formattedConsultationCharges() }}</span>
                                @if($service->duration)<span>{{ $service->duration }}</span>@endif
                            </div>
                            @if($service->service_area)<p class="consultant-service-card__description mt-2"><strong>Service area:</strong> {{ $service->service_area }}</p>@endif
                            <a href="{{ route('consultant.contact', $consultant->slug) }}" class="consultant-service-card__btn">Enquire Now</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@foreach($consultant->pageSections as $section)
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
    @if(str_contains((string) $section->content, 'data-card-image-slot') && ! $recentAdsShown)
        @include('frontend.consultant.partials.recent-ads-slider', ['ads' => $consultantRecentAds, 'selectedCategoryNamesByAdId' => $selectedCategoryNamesByConsultantAdId])
        @php($recentAdsShown = true)
    @else
        @include('frontend.consultant.partials.ads-zone', ['placement' => $randomFullPagePlacements['after_section_'.$loop->index] ?? null, 'sponsoredFillers' => $sponsoredFillers])
    @endif
@endforeach

@if(! $recentAdsShown)
    @include('frontend.consultant.partials.recent-ads-slider', ['ads' => $consultantRecentAds, 'selectedCategoryNamesByAdId' => $selectedCategoryNamesByConsultantAdId])
@endif


<div class="modal fade" id="consultantSectionImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">Image preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0 text-center">
                <img id="consultantSectionImageModalImg" src="" alt="Store section image" class="img-fluid rounded" style="max-height:80vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('consultant_scripts')
<style>
    .content-body .js-remove-brochure-image,
    .content-body .js-remove-brochure-pdf {
        display: none !important;
    }
    .content-body [data-brochure-image-slot] {
        cursor: zoom-in;
    }

    .consultant-service-card {
        border: 1px solid rgba(15, 43, 77, 0.12);
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 43, 77, 0.08);
        display: flex;
        flex-direction: column;
    }
    .consultant-service-card__image-wrap {
        height: 180px;
        background: #eef4fb;
    }
    .consultant-service-card__image,
    .consultant-service-card__placeholder {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .consultant-service-card__placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2276d2;
        font-size: 42px;
    }
    .consultant-service-card__body {
        padding: 18px;
        display: flex;
        flex: 1;
        flex-direction: column;
    }
    .consultant-service-card__category {
        color: #2276d2;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .consultant-service-card h3 {
        color: #0f2b4d;
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 8px;
    }
    .consultant-service-card__description {
        color: #607188;
        font-size: 14px;
        line-height: 1.5;
    }
    .consultant-service-card__meta {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        color: #0f2b4d;
        font-weight: 700;
        margin-top: auto;
        padding-top: 12px;
    }
    .consultant-service-card__btn {
        margin-top: 14px;
        border-radius: 999px;
        background: linear-gradient(90deg, #2276d2, #21833b);
        color: #fff;
        display: inline-flex;
        justify-content: center;
        padding: 10px 14px;
        font-weight: 700;
        text-decoration: none;
    }

</style>

@if($consultant->bannerSlides->count() > 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('consultantHeroCarousel');
    if (el) new bootstrap.Carousel(el, { interval: 5000 });
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('consultantSectionImageModal');
    const modalImg = document.getElementById('consultantSectionImageModalImg');
    if (!modalEl || !modalImg || typeof bootstrap === 'undefined') return;
    const previewModal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.content-body [data-brochure-image-slot], .content-body [data-brochure-image-list] img').forEach(function (img) {
        img.addEventListener('click', function () {
            if (!img.src) return;
            modalImg.src = img.src;
            modalImg.alt = img.alt || 'Store section image';
            previewModal.show();
        });
    });
});
</script>
@endpush
