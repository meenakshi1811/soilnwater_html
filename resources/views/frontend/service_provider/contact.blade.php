@extends('frontend.service_provider.layout')

@section('title', 'Contact '. $service_provider->publicDisplayName())

@section('service_provider_content')
@php
    $approvedServices = $approvedServices ?? collect();
    $contactEnquiryModalId = 'service_providerContactEnquiryModal';
@endphp
<section class="service_provider-contact-page py-5 py-lg-6">
    <div class="container">
        <div class="contact-hero shadow-sm overflow-hidden mb-4 mb-lg-5">
            <div class="contact-hero__bg"></div>
            <div class="contact-hero__content p-4 p-lg-5">
                <p class="text-uppercase fw-semibold mb-2 contact-eyebrow">Let’s Connect</p>
                <h1 class="display-5 fw-bold mb-3 text-white">{{ $service_provider->publicDisplayName() }}</h1>
                <p class="mb-0 contact-subtitle">Reach out for consultation details, availability, pricing, and support.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="contact-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h3 fw-bold mb-4">Service Provider Details</h2>

                        <div class="d-flex align-items-start gap-3 mb-4">
                            <span class="contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <p class="mb-1 fw-semibold text-dark">Address</p>
                                <p class="mb-0 text-muted">{{ $service_provider->formattedAddress() ?: 'Address details are not available yet.' }}</p>
                            </div>
                        </div>

                        @if($service_provider->phone)
                            <div class="d-flex align-items-start gap-3 mb-4">
                                <span class="contact-icon"><i class="fa-solid fa-phone"></i></span>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark">Phone</p>
                                    <p class="mb-0 text-muted">{{ $service_provider->phone }}</p>
                                </div>
                            </div>
                        @endif

                        @if($service_provider->email)
                            <div class="d-flex align-items-start gap-3">
                                <span class="contact-icon"><i class="fa-solid fa-envelope"></i></span>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark">Email</p>
                                    <p class="mb-0 text-muted">{{ $service_provider->email }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="enquiry-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-4 d-flex flex-column">
                        <div class="enquiry-badge mb-3">Quick Contact</div>
                        <h3 class="h5 fw-bold mb-2">Share your requirement</h3>
                        <p class="text-muted mb-4">Submit an enquiry and this service_provider will be notified by email and SMS.</p>

                        @if($approvedServices->isNotEmpty())
                            @auth
                                <button type="button" class="btn enquiry-btn w-100 mt-auto" data-bs-toggle="modal" data-bs-target="#{{ $contactEnquiryModalId }}">
                                    <i class="fa-regular fa-paper-plane me-2"></i>Enquiry
                                </button>
                            @else
                                <button type="button" class="btn enquiry-btn w-100 mt-auto" data-bs-toggle="modal" data-bs-target="#service_providerLoginRequiredModal">
                                    <i class="fa-regular fa-paper-plane me-2"></i>Enquiry
                                </button>
                            @endauth
                        @else
                            <button type="button" class="btn btn-secondary w-100 mt-auto" disabled>No services available for enquiry</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@auth
    <div class="modal fade service_provider-service-enquiry-modal" id="{{ $contactEnquiryModalId }}" tabindex="-1" aria-labelledby="{{ $contactEnquiryModalId }}Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <p class="service_provider-service-detail-modal__eyebrow mb-1">Service Provider enquiry</p>
                        <h3 class="modal-title" id="{{ $contactEnquiryModalId }}Label">Share your requirement</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="service_provider-service-enquiry-form" action="{{ route('service_provider.enquiry', $service_provider->slug) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Select service_provider service *</label>
                                <select name="service_provider_service_id" class="form-select" required>
                                    <option value="">Choose a service</option>
                                    @foreach($approvedServices as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                                <textarea name="question" class="form-control" rows="4" placeholder="Write your question for this service_provider" required></textarea>
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
                        <p>Please log in to submit an enquiry for this service_provider.</p>
                        <a href="{{ route('login') }}" class="service_provider-login-required-card__btn">Login to continue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.service_provider-contact-page{background:linear-gradient(180deg,#f8faff 0%,#f3f6ff 100%)}
.contact-hero{position:relative;border-radius:20px}.contact-hero__bg{position:absolute;inset:0;background:linear-gradient(135deg,#1f4ed8 0%,#4f46e5 40%,#6d28d9 100%)}.contact-hero__content{position:relative;z-index:1}.contact-eyebrow{letter-spacing:.12em;color:#bfdbfe}.contact-subtitle{color:#e2e8f0;font-size:1.2rem;max-width:760px}.contact-panel,.enquiry-panel{border-radius:18px;background:#fff}.contact-icon{height:46px;width:46px;border-radius:14px;background:#e0e7ff;color:#3730a3;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;flex:0 0 auto}.enquiry-badge{display:inline-flex;align-self:flex-start;padding:.35rem .75rem;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:.82rem;font-weight:700;letter-spacing:.02em}.enquiry-btn{background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:#fff;border:0;padding:.72rem 1rem;font-weight:600;border-radius:12px;box-shadow:0 8px 22px rgba(79,70,229,.26)}.enquiry-btn:hover{color:#fff;filter:brightness(.96)}
</style>
@endpush
