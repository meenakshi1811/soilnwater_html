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
        <div class="consultant-services-heading mb-4">
            <div>
                <p class="consultant-services-heading__eyebrow mb-1">Consultation Services</p>
                <h2 class="vendor-section-title-display mb-0">Available consultations</h2>
            </div>
            <p class="consultant-services-heading__copy mb-0">Browse services at a glance and open the full profile for pricing, coverage, and consultation details.</p>
        </div>
        <div class="row g-4">
            @foreach($approvedServices as $service)
                @php
                    $image = $service->image_path;
                    $serviceType = ucfirst($service->consultation_type ?: ($service->is_online ? 'online' : 'offline'));
                    $chargeRows = $service->consultationChargeRows();
                    $modalId = 'consultantServiceDetailModal'.$service->id;
                    $enquiryModalId = 'consultantServiceEnquiryModal'.$service->id;
                @endphp
                <div class="col-sm-6 col-xl-3">
                    <article class="consultant-service-card h-100">
                        <div class="consultant-service-card__image-wrap">
                            @if($image)
                                <img src="{{ asset($image) }}" alt="{{ $service->name }}" class="consultant-service-card__image">
                            @else
                                <div class="consultant-service-card__placeholder"><i class="fa-solid fa-briefcase"></i></div>
                            @endif
                            <span class="consultant-service-card__type-badge">{{ $serviceType }}</span>
                        </div>
                        <div class="consultant-service-card__body">
                            <p class="consultant-service-card__category">{{ $service->categoryModel?->name ?? $service->category ?? 'Consultation' }}</p>
                            <h3>{{ $service->name }}</h3>
                            @if($service->service_area)
                                <p class="consultant-service-card__area mb-0"><i class="fa-solid fa-location-dot"></i> {{ \Illuminate\Support\Str::limit($service->service_area, 34) }}</p>
                            @endif
                            @auth
                                <button type="button" class="consultant-service-card__btn" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">View details</button>
                            @else
                                <button type="button" class="consultant-service-card__btn" data-bs-toggle="modal" data-bs-target="#consultantLoginRequiredModal">View details</button>
                            @endauth
                        </div>
                    </article>
                </div>

                <div class="modal fade consultant-service-detail-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <p class="consultant-service-detail-modal__eyebrow mb-1">{{ $service->categoryModel?->name ?? $service->category ?? 'Consultation' }}</p>
                                    <h3 class="modal-title" id="{{ $modalId }}Label">{{ $service->name }}</h3>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-4">
                                    <div class="col-md-5">
                                        <div class="consultant-service-detail-modal__media">
                                            @if($image)
                                                <img src="{{ asset($image) }}" alt="{{ $service->name }}">
                                            @else
                                                <div class="consultant-service-detail-modal__placeholder"><i class="fa-solid fa-briefcase"></i></div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="consultant-service-detail-modal__quick-grid">
                                            <div>
                                                <span>Consultation type</span>
                                                <strong>{{ $serviceType }}</strong>
                                            </div>
                                            @if($service->business_type)
                                                <div>
                                                    <span>Business type</span>
                                                    <strong>{{ $service->business_type }}</strong>
                                                </div>
                                            @endif
                                            @if($service->duration)
                                                <div>
                                                    <span>Duration</span>
                                                    <strong>{{ $service->duration }}</strong>
                                                </div>
                                            @endif
                                            @if($service->subcategoryModel?->name)
                                                <div>
                                                    <span>Speciality</span>
                                                    <strong>{{ $service->subcategoryModel->name }}</strong>
                                                </div>
                                            @endif
                                        </div>

                                        @if($service->short_description || $service->description)
                                            <div class="consultant-service-detail-modal__section">
                                                <h4>Overview</h4>
                                                @if($service->short_description)
                                                    <p>{{ $service->short_description }}</p>
                                                @endif
                                                @if($service->description)
                                                    <p class="mb-0">{!! nl2br(e($service->description)) !!}</p>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="consultant-service-detail-modal__section">
                                            <h4>Charges</h4>
                                            <div class="consultant-service-detail-modal__charges">
                                                @foreach($chargeRows as $charge)
                                                    <div class="consultant-service-detail-modal__charge-row">
                                                        <span>{{ $charge['duration'] }}</span>
                                                        <strong>{{ $charge['price'] }}</strong>
                                                        @if(!empty($charge['note']))
                                                            <small>{{ $charge['note'] }}</small>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        @if($service->service_area || $service->location)
                                            <div class="consultant-service-detail-modal__section">
                                                <h4>Service location</h4>
                                                @if($service->service_area)<p class="mb-1"><strong>Service area:</strong> {{ $service->service_area }}</p>@endif
                                                @if($service->location)<p class="mb-0"><strong>Location:</strong> {{ $service->location }}</p>@endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn consultant-service-detail-modal__contact-btn" data-bs-target="#{{ $enquiryModalId }}" data-bs-toggle="modal">Enquiry</button>
                            </div>
                        </div>
                    </div>
                </div>

                @auth
                    <div class="modal fade consultant-service-enquiry-modal" id="{{ $enquiryModalId }}" tabindex="-1" aria-labelledby="{{ $enquiryModalId }}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <p class="consultant-service-detail-modal__eyebrow mb-1">Service enquiry</p>
                                        <h3 class="modal-title" id="{{ $enquiryModalId }}Label">{{ $service->name }}</h3>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form class="consultant-service-enquiry-form" action="{{ route('consultant.services.enquiry', [$consultant->slug, $service]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="consultant_service_id" value="{{ $service->id }}">
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Client name *</label>
                                                <input type="text" name="client_name" class="form-control" value="{{ auth()->user()?->full_name ?: auth()->user()?->name }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Number *</label>
                                                <input type="text" name="phone_number" class="form-control" value="{{ auth()->user()?->phone_number }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email *</label>
                                                <input type="email" name="email" class="form-control" value="{{ auth()->user()?->email }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Occupation</label>
                                                <input type="text" name="occupation" class="form-control" placeholder="Your occupation">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">DOB</label>
                                                <input type="date" name="date_of_birth" class="form-control" value="{{ auth()->user()?->date_of_birth?->format('Y-m-d') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Upload image</label>
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Question *</label>
                                                <textarea name="question" class="form-control" rows="4" placeholder="Write your question for this service" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn consultant-service-detail-modal__contact-btn">Submit enquiry</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth
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

<div class="modal fade consultant-login-required-modal" id="consultantLoginRequiredModal" tabindex="-1" aria-hidden="true">
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
                        <p>Please log in to view this consultation details and share options.</p>
                        <a href="{{ route('login') }}" class="consultant-login-required-card__btn">Login to continue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

    .consultant-services-heading {
        align-items: flex-end;
        display: flex;
        gap: 18px;
        justify-content: space-between;
    }
    .consultant-services-heading__eyebrow,
    .consultant-service-detail-modal__eyebrow {
        color: #2276d2;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .consultant-services-heading__copy {
        color: #607188;
        max-width: 480px;
        text-align: right;
    }
    .consultant-service-card {
        background: #fff;
        border: 1px solid rgba(15, 43, 77, 0.1);
        border-radius: 22px;
        box-shadow: 0 16px 42px rgba(15, 43, 77, 0.08);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .consultant-service-card:hover {
        border-color: rgba(34, 118, 210, 0.24);
        box-shadow: 0 22px 54px rgba(15, 43, 77, 0.14);
        transform: translateY(-4px);
    }
    .consultant-service-card__image-wrap {
        background: linear-gradient(135deg, #eef6ff, #f4fbf5);
        height: 172px;
        overflow: hidden;
        position: relative;
    }
    .consultant-service-card__image,
    .consultant-service-card__placeholder {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }
    .consultant-service-card__placeholder,
    .consultant-service-detail-modal__placeholder {
        align-items: center;
        color: #2276d2;
        display: flex;
        font-size: 44px;
        justify-content: center;
    }
    .consultant-service-card__type-badge {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(15, 43, 77, 0.08);
        border-radius: 999px;
        box-shadow: 0 8px 22px rgba(15, 43, 77, 0.12);
        color: #0f2b4d;
        font-size: 12px;
        font-weight: 800;
        padding: 7px 12px;
        position: absolute;
        right: 14px;
        text-transform: uppercase;
        top: 14px;
    }
    .consultant-service-card__body {
        display: flex;
        flex: 1;
        flex-direction: column;
        padding: 20px;
    }
    .consultant-service-card__category {
        color: #2276d2;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .08em;
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    .consultant-service-card h3 {
        color: #0f2b4d;
        font-size: 21px;
        font-weight: 850;
        line-height: 1.25;
        margin-bottom: 16px;
    }
    .consultant-service-card__area {
        color: #607188;
        font-size: 14px;
        line-height: 1.45;
        margin-top: 12px;
    }
    .consultant-service-card__area i {
        color: #21833b;
        margin-right: 6px;
    }
    .consultant-service-card__btn {
        align-items: center;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(90deg, #2276d2, #21833b);
        color: #fff;
        display: inline-flex;
        justify-content: center;
        margin-top: 16px;
        padding: 11px 16px;
        font-weight: 800;
        text-decoration: none;
        width: 100%;
    }
    .consultant-service-card__btn:hover,
    .consultant-service-detail-modal__contact-btn:hover,
    .consultant-login-required-card__btn:hover {
        color: #fff;
        filter: brightness(.96);
    }
    .consultant-service-detail-modal .modal-content,
    .consultant-service-enquiry-modal .modal-content,
    .consultant-login-required-modal .modal-content {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 28px 80px rgba(6, 27, 58, 0.22);
        overflow: hidden;
    }
    .consultant-service-detail-modal .modal-header,
    .consultant-service-detail-modal .modal-footer,
    .consultant-service-enquiry-modal .modal-header,
    .consultant-service-enquiry-modal .modal-footer {
        border-color: rgba(15, 43, 77, 0.08);
        padding: 20px 24px;
    }
    .consultant-service-detail-modal .modal-title,
    .consultant-service-enquiry-modal .modal-title,
    .consultant-login-required-modal .modal-title {
        color: #0f2b4d;
        font-size: 26px;
        font-weight: 850;
    }
    .consultant-service-detail-modal .modal-body,
    .consultant-service-enquiry-modal .modal-body,
    .consultant-login-required-modal .modal-body {
        padding: 24px;
    }
    .consultant-service-detail-modal__media {
        background: linear-gradient(135deg, #eef6ff, #f4fbf5);
        border-radius: 20px;
        height: 100%;
        min-height: 280px;
        overflow: hidden;
    }
    .consultant-service-detail-modal__media img,
    .consultant-service-detail-modal__placeholder {
        height: 100%;
        min-height: 280px;
        object-fit: cover;
        width: 100%;
    }
    .consultant-service-detail-modal__quick-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .consultant-service-detail-modal__quick-grid div,
    .consultant-service-detail-modal__charge-row {
        background: #f6f9fc;
        border: 1px solid rgba(15, 43, 77, 0.07);
        border-radius: 16px;
        padding: 12px 14px;
    }
    .consultant-service-detail-modal__quick-grid span,
    .consultant-service-detail-modal__charge-row span {
        color: #607188;
        display: block;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .consultant-service-detail-modal__quick-grid strong,
    .consultant-service-detail-modal__charge-row strong {
        color: #0f2b4d;
        display: block;
        font-size: 16px;
        margin-top: 4px;
    }
    .consultant-service-detail-modal__section {
        margin-top: 22px;
    }
    .consultant-service-detail-modal__section h4 {
        color: #0f2b4d;
        font-size: 16px;
        font-weight: 850;
        margin-bottom: 10px;
    }
    .consultant-service-detail-modal__section p {
        color: #607188;
        line-height: 1.65;
    }
    .consultant-service-detail-modal__charges {
        display: grid;
        gap: 10px;
    }
    .consultant-service-detail-modal__charge-row {
        display: grid;
        gap: 4px 12px;
        grid-template-columns: 1fr auto;
    }
    .consultant-service-detail-modal__charge-row small {
        color: #607188;
        grid-column: 1 / -1;
    }
    .consultant-service-detail-modal__contact-btn,
    .consultant-login-required-card__btn {
        background: linear-gradient(90deg, #2276d2, #21833b);
        border: 0;
        border-radius: 999px;
        color: #fff;
        font-weight: 800;
        padding: 10px 18px;
        text-decoration: none;
    }
    .consultant-service-enquiry-modal .form-label {
        color: #0f2b4d;
        font-weight: 750;
    }
    .consultant-service-enquiry-modal .form-control {
        border-color: rgba(15, 43, 77, 0.14);
        border-radius: 12px;
        padding: 10px 12px;
    }
    .consultant-login-required-card {
        align-items: flex-start;
        background: #f7fbff;
        border: 1px solid #cfe0ff;
        border-radius: 16px;
        display: flex;
        gap: 18px;
        padding: 22px;
    }
    .consultant-login-required-card__icon {
        align-items: center;
        background: #e8f0ff;
        border-radius: 50%;
        color: #2563d8;
        display: flex;
        flex: 0 0 52px;
        font-size: 22px;
        height: 52px;
        justify-content: center;
        width: 52px;
    }
    .consultant-login-required-card h4 {
        color: #18345f;
        font-size: 24px;
        font-weight: 850;
        margin-bottom: 8px;
    }
    .consultant-login-required-card p {
        color: #607188;
        font-size: 20px;
        margin-bottom: 18px;
    }
    .consultant-login-required-card__btn {
        border-radius: 6px;
        display: inline-flex;
        font-size: 20px;
        padding: 10px 14px;
    }
    @media (max-width: 767.98px) {
        .consultant-services-heading {
            align-items: flex-start;
            flex-direction: column;
        }
        .consultant-services-heading__copy {
            text-align: left;
        }
        .consultant-service-detail-modal__quick-grid {
            grid-template-columns: 1fr;
        }
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

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }
        alert(message);
    }

    document.querySelectorAll('.consultant-service-enquiry-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            })
                .then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (payload) {
                        if (!response.ok) {
                            const errors = payload.errors ? Object.values(payload.errors).flat().join(' ') : '';
                            throw new Error(errors || payload.message || 'Unable to submit enquiry.');
                        }
                        return payload;
                    });
                })
                .then(function (payload) {
                    notify('success', payload.message || 'Enquiry submitted successfully.');
                    form.reset();
                    const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                    if (modal) modal.hide();
                })
                .catch(function (error) {
                    notify('error', error.message || 'Unable to submit enquiry.');
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
        });
    });
});
</script>
@endpush
