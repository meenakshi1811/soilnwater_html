{{--
  A consultant's or service provider's services on its profile page: a card each (its details and
  an enquiry in popups; guests are asked to log in).
  $services, $kind ('consultant' | 'service'), $slug; the page's $biz (for the provider's phone)
--}}
@php
    $isConsultant = $kind === 'consultant';
    $enquiryRoute = $isConsultant ? 'consultant.services.enquiry' : 'service_provider.services.enquiry';
    $serviceField = $isConsultant ? 'consultant_service_id' : 'service_provider_service_id';
    $fallbackCategory = $isConsultant ? 'Consultation' : 'Service';
    $fallbackIcon = $isConsultant ? 'fa-briefcase' : 'fa-screwdriver-wrench';
    $providerPhone = $biz['phones'][0] ?? null;
    $providerWhatsapp = $biz['whatsappUrl'] ?? null;
    $providerUrl = $biz['url'] ?? null;
@endphp

@foreach($services as $service)
    @php
        $image = $service->image_path;
        $serviceType = ucfirst($service->consultation_type ?: ($service->is_online ? 'online' : 'offline'));
        $categoryName = $service->categoryModel?->name ?? $service->category ?? $fallbackCategory;
        $chargeRows = $service->consultationChargeRows();
        $startingCharge = collect($chargeRows)->first(fn ($row) => $row['price'] !== '₹0.00');
        $detailModalId = 'bizServiceDetail'.$service->id;
        $enquiryModalId = 'bizServiceEnquiry'.$service->id;
        $openTarget = auth()->check() ? '#'.$detailModalId : '#bizLoginRequiredModal';
        $infoRows = collect($isConsultant
            ? [
                ['Category', $categoryName],
                ['Subcategory', $service->subcategoryModel?->name],
                ['Consultation type', $serviceType],
                ['Business type', $service->business_type],
                ['Geographical area', $service->service_area],
                ['Location', $service->location],
            ]
            : [
                ['Category', $categoryName],
                ['Subcategory', $service->subcategoryModel?->name],
                ['Service type', $serviceType],
                ['Business type', $service->business_type],
                ['Service area', $service->service_area],
                ['Address', $service->location],
                ['Postal code', $service->postal_code],
                ['City', $service->city],
                ['Service radius', filled($service->service_radius) ? $service->service_radius.' km' : null],
                ['Working hours', $service->working_hours],
            ])->filter(fn ($row) => filled($row[1]))->values();
        // For the popup: not the category (it is in the header) nor what repeats the area (under the price)
        $facts = $infoRows->slice(1)
            ->reject(fn ($row) => filled($service->service_area) && \Illuminate\Support\Str::lower(trim((string) $row[1])) === \Illuminate\Support\Str::lower(trim($service->service_area)))
            ->values();
    @endphp

    <article class="biz-item" data-biz-item data-biz-category="{{ $service->category_id }}">
        <button type="button" class="biz-item__media" data-bs-toggle="modal" data-bs-target="{{ $openTarget }}" aria-label="View {{ $service->name }}">
            @if($image)
                <img src="{{ asset($image) }}" alt="{{ $service->name }}" loading="lazy" decoding="async">
            @else
                <span class="biz-item__placeholder" aria-hidden="true"><i class="fa-solid {{ $fallbackIcon }}"></i></span>
            @endif
            <span class="biz-item__badge">{{ $serviceType }}</span>
        </button>
        <div class="biz-item__body">
            <p class="biz-item__eyebrow">{{ $categoryName }}</p>
            <h3 class="biz-item__title">{{ $service->name }}</h3>
            @if($service->service_area)
                <p class="biz-item__meta"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $service->service_area }}</p>
            @endif
            @if($startingCharge)
                <p class="biz-item__price">{{ $startingCharge['price'] }} <span>{{ $startingCharge['duration'] }}</span></p>
            @endif
            <button type="button" class="biz-item__btn" data-bs-toggle="modal" data-bs-target="{{ $openTarget }}">View details</button>
        </div>
    </article>

    @auth
        @push('biz_modals')
            <div class="modal fade biz-modal biz-modal--{{ $kind }} biz-service-modal" id="{{ $detailModalId }}" tabindex="-1" aria-labelledby="{{ $detailModalId }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-fullscreen-sm-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <p class="biz-modal__eyebrow">{{ $categoryName }}</p>
                                <h3 class="biz-modal__title" id="{{ $detailModalId }}Label">{{ $service->name }}</h3>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="biz-service-modal__top">
                                <div class="biz-service-modal__media">
                                    @if($image)
                                        <img src="{{ asset($image) }}" alt="{{ $service->name }}" loading="lazy">
                                    @else
                                        <span class="biz-item__placeholder" aria-hidden="true"><i class="fa-solid {{ $fallbackIcon }}"></i></span>
                                    @endif
                                    <span class="biz-item__badge">{{ $serviceType }}</span>
                                </div>
                                <div class="biz-service-modal__info">
                                    <div class="biz-product__price">
                                        @if($startingCharge)
                                            <strong>{{ $startingCharge['price'] }}</strong>
                                            <span class="biz-service-modal__per">{{ $startingCharge['duration'] }}</span>
                                        @else
                                            <strong class="biz-product__ask">Price on request</strong>
                                        @endif
                                    </div>
                                    @if($service->service_area)
                                        <p class="biz-service-modal__meta"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $service->service_area }}</p>
                                    @endif
                                    @if($facts->isNotEmpty())
                                        <dl class="biz-product__facts">
                                            @foreach($facts as [$label, $value])
                                                <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                                            @endforeach
                                        </dl>
                                    @endif
                                </div>
                            </div>

                            @if($service->short_description || $service->description)
                                <section class="biz-service-modal__block">
                                    <h4>About this service</h4>
                                    @if($service->short_description)
                                        <p class="biz-service-modal__lead">{{ $service->short_description }}</p>
                                    @endif
                                    @if($service->description)
                                        <p class="biz-service-modal__text">{{ $service->description }}</p>
                                    @endif
                                </section>
                            @endif

                            <section class="biz-service-modal__block">
                                <h4>Charges</h4>
                                <ul class="biz-charges">
                                    @foreach($chargeRows as $charge)
                                        <li>
                                            <span>{{ $charge['duration'] }}</span>
                                            <strong>{{ $charge['price'] }}</strong>
                                            @if($charge['note'])
                                                <small>{{ $charge['note'] }}</small>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        </div>
                        <div class="modal-footer biz-service-modal__footer">
                            @if($providerPhone)
                                <a href="tel:{{ $providerPhone }}" class="biz-btn biz-btn--outline"><i class="fa-solid fa-phone" aria-hidden="true"></i> Call</a>
                            @endif
                            @if($providerWhatsapp)
                                <a href="{{ $providerWhatsapp }}?text={{ urlencode('Hi, I am interested in '.$service->name.($providerUrl ? ' ('.$providerUrl.')' : '')) }}" target="_blank" rel="noopener" class="biz-btn biz-btn--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                            @endif
                            <button type="button" class="biz-btn biz-btn--primary" data-bs-target="#{{ $enquiryModalId }}" data-bs-toggle="modal">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Enquire
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade consultant-service-enquiry-modal" id="{{ $enquiryModalId }}" tabindex="-1" aria-labelledby="{{ $enquiryModalId }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <p class="consultant-service-detail-modal__eyebrow mb-1">Service enquiry</p>
                                <h3 class="modal-title" id="{{ $enquiryModalId }}Label">{{ $service->name }}</h3>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route($enquiryRoute, [$slug, $service]) }}" method="POST" enctype="multipart/form-data" data-biz-ajax>
                            @csrf
                            <input type="hidden" name="{{ $serviceField }}" value="{{ $service->id }}">
                            <div class="modal-body">
                                @include('frontend.business-profile.partials.service-enquiry-fields', ['questionPlaceholder' => 'Write your question for this service'])
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn consultant-service-detail-modal__contact-btn">Submit enquiry</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endpush
    @endauth
@endforeach
