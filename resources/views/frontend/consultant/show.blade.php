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
