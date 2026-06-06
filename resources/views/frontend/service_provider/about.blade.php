@extends('frontend.service_provider.layout')

@section('title', 'About '. $service_provider->publicDisplayName())

@section('service_provider_content')
<section class="service_provider-contact-page py-5 py-lg-6">
    <div class="container">
        <div class="contact-hero shadow-sm overflow-hidden mb-4 mb-lg-5">
            <div class="contact-hero__bg"></div>
            <div class="contact-hero__content p-4 p-lg-5">
                <p class="text-uppercase fw-semibold mb-2 contact-eyebrow">About Us</p>
                <h1 class="display-5 fw-bold mb-3 text-white">{{ $service_provider->publicDisplayName() }}</h1>
                <p class="mb-0 contact-subtitle">Know more about our company, values, and what we offer.</p>
            </div>
        </div>

        <div class="contact-panel card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <h2 class="h3 fw-bold mb-3">Company Profile</h2>

                @if(trim(strip_tags((string) $service_provider->description)) !== '')
                    <div class="content-body">{!! $service_provider->description !!}</div>
                @else
                    <p class="text-muted mb-0">About Us content is not added yet. Please check back soon.</p>
                @endif

                @php($professionalBranches = $service_provider->branches->filter(fn ($branch) => filled($branch->professional_experience) || filled($branch->services_offered)))
                @if($professionalBranches->isNotEmpty())
                    <div class="professional-details mt-5">
                        <h2 class="h3 fw-bold mb-4">Professional Details</h2>
                        <div class="row g-4">
                            @foreach($professionalBranches as $branch)
                                <div class="col-12">
                                    <article class="professional-details__card">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                            <h3 class="h5 fw-bold mb-0">{{ $branch->branch_name }}</h3>
                                            @if($branch->is_primary)
                                                <span class="badge rounded-pill text-bg-primary">Primary</span>
                                            @endif
                                        </div>
                                        <div class="row g-4">
                                            @if(filled($branch->professional_experience))
                                                <div class="col-md-6">
                                                    <p class="professional-details__label mb-2">Professional Experience</p>
                                                    <p class="professional-details__text mb-0">{{ $branch->professional_experience }}</p>
                                                </div>
                                            @endif
                                            @if(filled($branch->services_offered))
                                                <div class="col-md-6">
                                                    <p class="professional-details__label mb-2">Services Offered</p>
                                                    <p class="professional-details__text mb-0">{{ $branch->services_offered }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.service_provider-contact-page{background:linear-gradient(180deg,#f8faff 0%,#f3f6ff 100%)}
.contact-hero{position:relative;border-radius:20px}
.contact-hero__bg{position:absolute;inset:0;background:linear-gradient(135deg,#1f4ed8 0%,#4f46e5 40%,#6d28d9 100%)}
.contact-hero__content{position:relative;z-index:1}
.contact-eyebrow{letter-spacing:.12em;color:#bfdbfe}
.contact-subtitle{color:#e2e8f0;font-size:1.2rem;max-width:760px}
.contact-panel{border-radius:18px;background:#fff}
.professional-details__card{background:#f8faff;border:1px solid #e0e7ff;border-radius:16px;padding:1.5rem}
.professional-details__label{color:#4338ca;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.professional-details__text{color:#475569;white-space:pre-line}
@media (max-width: 991.98px){.contact-subtitle{font-size:1.05rem}}
</style>
@endpush
