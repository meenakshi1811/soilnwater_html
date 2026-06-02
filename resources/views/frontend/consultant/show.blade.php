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
                    $serviceInfoRows = collect([
                        ['Service ID', $service->id],
                        ['Service slug', $service->slug],
                        ['Category', $service->categoryModel?->name ?? $service->category],
                        ['Subcategory', $service->subcategoryModel?->name],
                        ['Consultation type', $serviceType],
                        ['Business type', $service->business_type],
                        ['Duration', $service->duration],
                        ['Base price', $service->price ? '₹'.number_format((float) $service->price, 2) : null],
                        ['Online service', $service->is_online ? 'Yes' : 'No'],
                        ['Service area', $service->service_area],
                        ['Location', $service->location],
                        ['Status', ucfirst((string) $service->status)],
                        ['Approved at', $service->approved_at?->format('d M Y H:i')],
                        ['Created at', $service->created_at?->format('d M Y H:i')],
                        ['Updated at', $service->updated_at?->format('d M Y H:i')],
                    ])->filter(fn ($row) => filled($row[1]))->values();
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

                @auth
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
                                        <div class="col-lg-5">
                                            <div class="consultant-service-detail-modal__media">
                                                @if($image)
                                                    <img src="{{ asset($image) }}" alt="{{ $service->name }}">
                                                @else
                                                    <div class="consultant-service-detail-modal__placeholder"><i class="fa-solid fa-briefcase"></i></div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="consultant-service-detail-modal__section mt-0">
                                                <h4>Service information</h4>
                                                <div class="consultant-service-detail-modal__info-grid">
                                                    @foreach($serviceInfoRows as [$label, $value])
                                                        <div class="consultant-service-detail-modal__info-item">
                                                            <span>{{ $label }}</span>
                                                            <strong>{{ $value }}</strong>
                                                        </div>
                                                    @endforeach
                                                </div>
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
                                                <div class="table-responsive consultant-service-detail-modal__charges-table-wrap">
                                                    <table class="table consultant-service-detail-modal__charges-table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Duration</th>
                                                                <th>Price</th>
                                                                <th>Note</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($chargeRows as $charge)
                                                                <tr>
                                                                    <td>{{ $charge['duration'] }}</td>
                                                                    <td>{{ $charge['price'] }}</td>
                                                                    <td>{{ $charge['note'] ?: '—' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer consultant-service-detail-modal__footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn consultant-service-detail-modal__contact-btn" data-bs-target="#{{ $enquiryModalId }}" data-bs-toggle="modal">Enquiry</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth

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
