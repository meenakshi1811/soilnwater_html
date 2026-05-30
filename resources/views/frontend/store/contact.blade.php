@extends('frontend.store.layout')

@section('title', 'Contact '. $vendor->publicDisplayName())

@section('store_content')
<section class="vendor-contact-page py-5 py-lg-6">
    <div class="container">
        <div class="contact-hero shadow-sm overflow-hidden mb-4 mb-lg-5">
            <div class="contact-hero__bg"></div>
            <div class="contact-hero__content p-4 p-lg-5">
                <p class="text-uppercase fw-semibold mb-2 contact-eyebrow">Let’s Connect</p>
                <h1 class="display-5 fw-bold mb-3 text-white">{{ $vendor->publicDisplayName() }}</h1>
                <p class="mb-0 contact-subtitle">Reach out for products, availability, pricing, and delivery support.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="contact-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h3 fw-bold mb-4">Vendor Details</h2>

                        <div class="d-flex align-items-start gap-3">
                            <span class="contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <p class="mb-1 fw-semibold text-dark">Address</p>
                                <p class="mb-0 text-muted">
                                    {{ $vendor->formattedAddress() ?: 'Address details are not available yet.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="enquiry-panel card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-4 d-flex flex-column">
                        <div class="enquiry-badge mb-3">Quick Enquiry</div>
                        <h3 class="h5 fw-bold mb-2">Share your requirement</h3>
                        <p class="text-muted mb-4">Our team will review your message and get back to you soon.</p>

                        <div class="mt-auto">
                            @if($inquiryProduct)
                                <button class="btn enquiry-btn w-100" data-bs-toggle="modal" data-bs-target="#enquiryModal">
                                    <i class="fa-regular fa-paper-plane me-2"></i>Send Enquiry
                                </button>
                            @else
                                <button class="btn btn-secondary w-100" disabled>No products available for enquiry</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if($inquiryProduct)
@include('frontend.store.partials.enquiry-modal')
@endif
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
.contact-icon{height:46px;width:46px;border-radius:14px;background:#e0e7ff;color:#3730a3;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;flex:0 0 auto}
.enquiry-badge{display:inline-flex;align-self:flex-start;padding:.35rem .75rem;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:.82rem;font-weight:700;letter-spacing:.02em}
.enquiry-btn{background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:#fff;border:0;padding:.72rem 1rem;font-weight:600;border-radius:12px;box-shadow:0 8px 22px rgba(79,70,229,.26)}
.enquiry-btn:hover{color:#fff;filter:brightness(.96)}
@media (max-width: 991.98px){.contact-subtitle{font-size:1.05rem}}
</style>
@endpush

@push('store_scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@if($inquiryProduct)
<script>
document.getElementById('enquiryForm')?.addEventListener('submit', async function(e){
    e.preventDefault();
    const submitBtn = this.querySelector('#enquirySubmitBtn');
    const loader = submitBtn?.querySelector('.js-enquiry-btn-loader');
    const sending = submitBtn?.querySelector('.js-enquiry-btn-sending');
    const btnText = submitBtn?.querySelector('.js-enquiry-btn-text');

    const showFeedback = (type, message) => {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            try {
                window.toastr[type](message);
                return;
            } catch (err) {
                console.warn('Toastr failed, falling back to alert.', err);
            }
        }
        alert(message);
    };

    if (submitBtn) submitBtn.disabled = true;
    btnText?.classList.add('d-none');
    loader?.classList.remove('d-none');
    sending?.classList.remove('d-none');

    const fd = new FormData(this);
    try {
        const res = await fetch("{{ route('store.enquiry', $vendor->slug) }}", {
            method:'POST',
            headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
            body:fd
        });
        const data = await res.json();
        const toastType = res.ok ? 'success' : 'error';
        const toastMessage = data.message || (res.ok ? 'Enquiry sent successfully.' : 'Unable to send enquiry.');

        bootstrap.Modal.getInstance(document.getElementById('enquiryModal'))?.hide();

        showFeedback(toastType, toastMessage);

        if (res.ok) {
            this.reset();
        }
    } catch (error) {
        bootstrap.Modal.getInstance(document.getElementById('enquiryModal'))?.hide();
        showFeedback('error', 'Unable to send enquiry. Please try again.');
    } finally {
        if (submitBtn) submitBtn.disabled = false;
        btnText?.classList.remove('d-none');
        loader?.classList.add('d-none');
        sending?.classList.add('d-none');
    }
});
</script>
@endif
@endpush
