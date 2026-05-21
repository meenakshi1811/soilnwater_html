@extends('frontend.store.layout')

@section('title', 'Contact '. $vendor->publicDisplayName())

@section('store_content')
<section class="vendor-contact-page py-5">
    <div class="container">
        <div class="contact-hero card border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4 p-lg-5">
                <p class="text-uppercase fw-bold mb-2 contact-eyebrow">Let’s Connect</p>
                <h1 class="display-6 fw-bold mb-2">{{ $vendor->publicDisplayName() }}</h1>
                <p class="mb-0 text-muted">Reach out to us for products, availability, pricing, and delivery support.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="contact-info-card card h-100 border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h4 fw-bold mb-3">Vendor Details</h2>
                        <p class="mb-3 text-muted">{{ $vendor->description ?: 'Trusted seller on SoilNWater marketplace.' }}</p>
                        <div class="d-flex align-items-start gap-3">
                            <span class="contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <p class="mb-1 fw-semibold">Address</p>
                                <p class="mb-0 text-muted">
                                    @if($vendor->address){{ $vendor->address }}, @endif
                                    {{ $vendor->city }}@if($vendor->state), {{ $vendor->state }}@endif @if($vendor->pincode){{ $vendor->pincode }}@endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="enquiry-card card border-0 shadow-sm h-100">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <p class="small text-uppercase fw-bold mb-2 text-primary">Quick Enquiry</p>
                            <h3 class="h5 fw-bold mb-2">Need help choosing a product?</h3>
                            <p class="text-muted mb-3">Send your requirement and our team will get back to you shortly.</p>
                        </div>
                        @if($inquiryProduct)
                            <button class="btn btn-store-primary w-100" data-bs-toggle="modal" data-bs-target="#enquiryModal">Send Enquiry</button>
                        @else
                            <button class="btn btn-secondary w-100" disabled>No products available</button>
                        @endif
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
.vendor-contact-page{background:linear-gradient(180deg,#f8fbff 0%,#f5f7ff 100%)}
.contact-hero{background:linear-gradient(120deg,#1d4ed8,#7c3aed);color:#fff}
.contact-eyebrow{letter-spacing:.08em;color:#bfdbfe}
.contact-info-card,.enquiry-card{border-radius:18px}
.contact-icon{height:42px;width:42px;border-radius:12px;background:#e0e7ff;color:#3730a3;display:inline-flex;align-items:center;justify-content:center}
</style>
@endpush

@push('store_scripts')
@if($inquiryProduct)
<script>
document.getElementById('enquiryForm')?.addEventListener('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);
    const res = await fetch("{{ route('store.products.enquiry', [$vendor->slug, $inquiryProduct->id]) }}", {
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
        body:fd
    });
    const data = await res.json();
    alert(data.message || 'Done');
    if(res.ok){ bootstrap.Modal.getInstance(document.getElementById('enquiryModal')).hide(); }
});
</script>
@endif
@endpush
