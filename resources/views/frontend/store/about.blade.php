@extends('frontend.store.layout')

@section('title', 'About '. $vendor->publicDisplayName())

@section('store_content')
<section class="vendor-contact-page py-5 py-lg-6">
    <div class="container">
        <div class="contact-hero shadow-sm overflow-hidden mb-4 mb-lg-5">
            <div class="contact-hero__bg"></div>
            <div class="contact-hero__content p-4 p-lg-5">
                <p class="text-uppercase fw-semibold mb-2 contact-eyebrow">About Us</p>
                <h1 class="display-5 fw-bold mb-3 text-white">{{ $vendor->publicDisplayName() }}</h1>
                <p class="mb-0 contact-subtitle">Know more about our company, values, and what we offer.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="contact-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h3 fw-bold mb-3">Company Profile</h2>

                        @if(trim(strip_tags((string) $vendor->description)) !== '')
                            <div class="content-body">{!! $vendor->description !!}</div>
                        @else
                            <p class="text-muted mb-0">About Us content is not added yet. Please check back soon.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="enquiry-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-4 d-flex flex-column">
                        <div class="enquiry-badge mb-3">Store Info</div>
                        <h3 class="h5 fw-bold mb-2">Connect with {{ $vendor->publicDisplayName() }}</h3>
                        <p class="text-muted mb-4">For product details, pricing, or service questions, use our contact page.</p>
                        <div class="mt-auto">
                            <a href="{{ route('store.contact', $vendor->slug) }}" class="btn enquiry-btn w-100">
                                <i class="fa-regular fa-address-card me-2"></i>Go to Contact Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.vendor-contact-page{background:linear-gradient(180deg,#f8faff 0%,#f3f6ff 100%)}
.contact-hero{position:relative;border-radius:20px}
.contact-hero__bg{position:absolute;inset:0;background:linear-gradient(135deg,#1f4ed8 0%,#4f46e5 40%,#6d28d9 100%)}
.contact-hero__content{position:relative;z-index:1}
.contact-eyebrow{letter-spacing:.12em;color:#bfdbfe}
.contact-subtitle{color:#e2e8f0;font-size:1.2rem;max-width:760px}
.contact-panel,.enquiry-panel{border-radius:18px;background:#fff}
.enquiry-badge{display:inline-flex;align-self:flex-start;padding:.35rem .75rem;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:.82rem;font-weight:700;letter-spacing:.02em}
.enquiry-btn{background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:#fff;border:0;padding:.72rem 1rem;font-weight:600;border-radius:12px;box-shadow:0 8px 22px rgba(79,70,229,.26)}
.enquiry-btn:hover{color:#fff;filter:brightness(.96)}
@media (max-width: 991.98px){.contact-subtitle{font-size:1.05rem}}
</style>
@endpush
