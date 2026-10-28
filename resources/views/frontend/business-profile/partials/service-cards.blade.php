{{--
  A consultant's or service provider's services on its profile page: a card each (its details and
  an enquiry in popups; guests are asked to log in).
  $services, $kind ('consultant' | 'service'), $slug
--}}
@php
    $isConsultant = $kind === 'consultant';
    $enquiryRoute = $isConsultant ? 'consultant.services.enquiry' : 'service_provider.services.enquiry';
    $serviceField = $isConsultant ? 'consultant_service_id' : 'service_provider_service_id';
    $fallbackCategory = $isConsultant ? 'Consultation' : 'Service';
    $fallbackIcon = $isConsultant ? 'fa-briefcase' : 'fa-screwdriver-wrench';
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
            <div class="modal fade consultant-service-detail-modal" id="{{ $detailModalId }}" tabindex="-1" aria-labelledby="{{ $detailModalId }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <p class="consultant-service-detail-modal__eyebrow mb-1">{{ $categoryName }}</p>
                                <h3 class="modal-title" id="{{ $detailModalId }}Label">{{ $service->name }}</h3>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="consultant-service-detail-modal__media consultant-service-detail-modal__media--centered">
                                @if($image)
                                    <img src="{{ asset($image) }}" alt="{{ $service->name }}">
                                @else
                                    <div class="consultant-service-detail-modal__placeholder"><i class="fa-solid {{ $fallbackIcon }}"></i></div>
                                @endif
                            </div>

                            <div class="consultant-service-detail-modal__section">
                                <h4>Service information</h4>
                                <div class="consultant-service-detail-modal__info-grid">
                                    @foreach($infoRows as [$label, $value])
                                        <div class="consultant-service-detail-modal__info-item">
                                            <span>{{ $label }}</span>
                                            <strong>{{ $value }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if($service->short_description)
                                <div class="consultant-service-detail-modal__section">
                                    <h4>Brief detail</h4>
                                    <p class="mb-0">{{ $service->short_description }}</p>
                                </div>
                            @endif

                            @if($service->description)
                                <div class="consultant-service-detail-modal__section">
                                    <h4>Overview</h4>
                                    <p class="mb-0">{!! nl2br(e($service->description)) !!}</p>
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
                        <div class="modal-footer consultant-service-detail-modal__footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn consultant-service-detail-modal__contact-btn" data-bs-target="#{{ $enquiryModalId }}" data-bs-toggle="modal">Enquiry</button>
                        </div>
                    </div>
                </div>
            </div>

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
