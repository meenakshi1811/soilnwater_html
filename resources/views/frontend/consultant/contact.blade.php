@extends('frontend.consultant.layout')

@section('title', 'Contact '. $consultant->publicDisplayName())

@section('consultant_content')
<section class="consultant-contact-page py-5 py-lg-6">
    <div class="container">
        <div class="contact-hero shadow-sm overflow-hidden mb-4 mb-lg-5">
            <div class="contact-hero__bg"></div>
            <div class="contact-hero__content p-4 p-lg-5">
                <p class="text-uppercase fw-semibold mb-2 contact-eyebrow">Let’s Connect</p>
                <h1 class="display-5 fw-bold mb-3 text-white">{{ $consultant->publicDisplayName() }}</h1>
                <p class="mb-0 contact-subtitle">Reach out for consultation details, availability, pricing, and support.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="contact-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h3 fw-bold mb-4">Consultant Details</h2>

                        <div class="d-flex align-items-start gap-3 mb-4">
                            <span class="contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <p class="mb-1 fw-semibold text-dark">Address</p>
                                <p class="mb-0 text-muted">{{ $consultant->formattedAddress() ?: 'Address details are not available yet.' }}</p>
                            </div>
                        </div>

                        @if($consultant->phone)
                            <div class="d-flex align-items-start gap-3 mb-4">
                                <span class="contact-icon"><i class="fa-solid fa-phone"></i></span>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark">Phone</p>
                                    <p class="mb-0 text-muted">{{ $consultant->phone }}</p>
                                </div>
                            </div>
                        @endif

                        @if($consultant->email)
                            <div class="d-flex align-items-start gap-3">
                                <span class="contact-icon"><i class="fa-solid fa-envelope"></i></span>
                                <div>
                                    <p class="mb-1 fw-semibold text-dark">Email</p>
                                    <p class="mb-0 text-muted">{{ $consultant->email }}</p>
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
                        <p class="text-muted mb-4">Use the contact details to connect directly with this consultant.</p>

                        @if($consultant->whatsapp)
                            <a class="btn enquiry-btn w-100 mt-auto" href="https://wa.me/91{{ preg_replace('/\D/', '', $consultant->whatsapp) }}" target="_blank" rel="noopener">
                                <i class="fa-brands fa-whatsapp me-2"></i>WhatsApp Consultant
                            </a>
                        @elseif($consultant->email)
                            <a class="btn enquiry-btn w-100 mt-auto" href="mailto:{{ $consultant->email }}">
                                <i class="fa-regular fa-paper-plane me-2"></i>Email Consultant
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.consultant-contact-page{background:linear-gradient(180deg,#f8faff 0%,#f3f6ff 100%)}
.contact-hero{position:relative;border-radius:20px}.contact-hero__bg{position:absolute;inset:0;background:linear-gradient(135deg,#1f4ed8 0%,#4f46e5 40%,#6d28d9 100%)}.contact-hero__content{position:relative;z-index:1}.contact-eyebrow{letter-spacing:.12em;color:#bfdbfe}.contact-subtitle{color:#e2e8f0;font-size:1.2rem;max-width:760px}.contact-panel,.enquiry-panel{border-radius:18px;background:#fff}.contact-icon{height:46px;width:46px;border-radius:14px;background:#e0e7ff;color:#3730a3;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;flex:0 0 auto}.enquiry-badge{display:inline-flex;align-self:flex-start;padding:.35rem .75rem;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:.82rem;font-weight:700;letter-spacing:.02em}.enquiry-btn{background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:#fff;border:0;padding:.72rem 1rem;font-weight:600;border-radius:12px;box-shadow:0 8px 22px rgba(79,70,229,.26)}.enquiry-btn:hover{color:#fff;filter:brightness(.96)}
</style>
@endpush
