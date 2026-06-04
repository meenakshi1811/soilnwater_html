@php
    $approvedServices = $approvedServices ?? collect();
    $wrapSection = $wrapSection ?? true;
    $wrapContainer = $wrapContainer ?? true;
    $showServicesHeading = $showServicesHeading ?? true;
    $serviceColumnClass = $serviceColumnClass ?? 'col-sm-6 col-xl-3';
@endphp

@if($approvedServices->count() > 0)
@if($wrapSection)
<section class="vendor-store-section service-provider-services-section">
@endif
    @if($wrapContainer)
    <div class="container">
    @endif
        @if($showServicesHeading)
        <div class="service-provider-services-heading mb-4">
            <div>
                <p class="service-provider-services-heading__eyebrow mb-1">Services</p>
                <h2 class="vendor-section-title-display mb-0">Available services</h2>
            </div>
            @if($showViewAllServicesButton ?? true)
                <a href="{{ route('service_provider.public-services.index', $service_provider->slug) }}" class="service-provider-services-heading__btn">View all services</a>
            @endif
        </div>
        @endif
        <div class="row g-3 g-md-4">
            @foreach($approvedServices as $service)
                @php
                    $image = $service->image_path;
                    $serviceType = ucfirst($service->consultation_type ?: ($service->is_online ? 'online' : 'offline'));
                    $chargeRows = $service->consultationChargeRows();
                    $modalId = 'service_providerServiceDetailModal'.$service->id;
                    $enquiryModalId = 'service_providerServiceEnquiryModal'.$service->id;
                    $serviceInfoRows = collect([
                        ['Category', $service->categoryModel?->name ?? $service->category],
                        ['Subcategory', $service->subcategoryModel?->name],
                        ['Service type', $serviceType],
                        ['Business type', $service->business_type],
                        ['Geographical area', $service->service_area],
                        ['Location', $service->location],
                    ])->filter(fn ($row) => filled($row[1]))->values();
                @endphp
                <div class="{{ $serviceColumnClass }}">
                    <article class="service_provider-service-card h-100">
                        <div class="service_provider-service-card__image-wrap">
                            @if($image)
                                <img src="{{ asset($image) }}" alt="{{ $service->name }}" class="service_provider-service-card__image">
                            @else
                                <div class="service_provider-service-card__placeholder"><i class="fa-solid fa-briefcase"></i></div>
                            @endif
                            <span class="service_provider-service-card__type-badge">{{ $serviceType }}</span>
                        </div>
                        <div class="service_provider-service-card__body">
                            <p class="service_provider-service-card__category">{{ $service->categoryModel?->name ?? $service->category ?? 'Service' }}</p>
                            <h3>{{ $service->name }}</h3>
                            @if($service->service_area)
                                <p class="service_provider-service-card__area mb-0"><i class="fa-solid fa-location-dot"></i> {{ \Illuminate\Support\Str::limit($service->service_area, 34) }}</p>
                            @endif
                            @auth
                                <button type="button" class="service_provider-service-card__btn" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">View details</button>
                            @else
                                <button type="button" class="service_provider-service-card__btn" data-bs-toggle="modal" data-bs-target="#service_providerLoginRequiredModal">View details</button>
                            @endauth
                        </div>
                    </article>
                </div>

                @auth
                    <div class="modal fade service_provider-service-detail-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <p class="service_provider-service-detail-modal__eyebrow mb-1">{{ $service->categoryModel?->name ?? $service->category ?? 'Service' }}</p>
                                        <h3 class="modal-title" id="{{ $modalId }}Label">{{ $service->name }}</h3>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="service_provider-service-detail-modal__media service_provider-service-detail-modal__media--centered">
                                        @if($image)
                                            <img src="{{ asset($image) }}" alt="{{ $service->name }}">
                                        @else
                                            <div class="service_provider-service-detail-modal__placeholder"><i class="fa-solid fa-briefcase"></i></div>
                                        @endif
                                    </div>

                                    <div class="service_provider-service-detail-modal__section">
                                        <h4>Service information</h4>
                                        <div class="service_provider-service-detail-modal__info-grid">
                                            @foreach($serviceInfoRows as [$label, $value])
                                                <div class="service_provider-service-detail-modal__info-item">
                                                    <span>{{ $label }}</span>
                                                    <strong>{{ $value }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    @if($service->short_description)
                                        <div class="service_provider-service-detail-modal__section">
                                            <h4>Brief detail</h4>
                                            <p class="mb-0">{{ $service->short_description }}</p>
                                        </div>
                                    @endif

                                    @if($service->description)
                                        <div class="service_provider-service-detail-modal__section">
                                            <h4>Overview</h4>
                                            <p class="mb-0">{!! nl2br(e($service->description)) !!}</p>
                                        </div>
                                    @endif

                                    <div class="service_provider-service-detail-modal__section">
                                        <h4>Charges</h4>
                                        <div class="table-responsive service_provider-service-detail-modal__charges-table-wrap">
                                            <table class="table service_provider-service-detail-modal__charges-table mb-0">
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
                                <div class="modal-footer service_provider-service-detail-modal__footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn service_provider-service-detail-modal__contact-btn" data-bs-target="#{{ $enquiryModalId }}" data-bs-toggle="modal">Enquiry</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth

                @auth
                    <div class="modal fade service_provider-service-enquiry-modal" id="{{ $enquiryModalId }}" tabindex="-1" aria-labelledby="{{ $enquiryModalId }}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <p class="service_provider-service-detail-modal__eyebrow mb-1">Service enquiry</p>
                                        <h3 class="modal-title" id="{{ $enquiryModalId }}Label">{{ $service->name }}</h3>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form class="service_provider-service-enquiry-form" action="{{ route('service_provider.services.enquiry', [$service_provider->slug, $service]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="service_provider_service_id" value="{{ $service->id }}">
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
                                        <button type="submit" class="btn service_provider-service-detail-modal__contact-btn">Submit enquiry</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth
            @endforeach
        </div>
    @if($wrapContainer)
    </div>
    @endif
@if($wrapSection)
</section>
@endif
@endif


<div class="modal fade service_provider-login-required-modal" id="service_providerLoginRequiredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title">You are not logged in</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="service_provider-login-required-card">
                    <div class="service_provider-login-required-card__icon"><i class="fa-solid fa-lock"></i></div>
                    <div>
                        <h4>You are not logged in</h4>
                        <p>Please log in to view these service details and share options.</p>
                        <a href="{{ route('login') }}" class="service_provider-login-required-card__btn">Login to continue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
