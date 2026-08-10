@php
    $profileName = $profile->publicDisplayName();
    $heroImage = $profile->bannerSlides?->first()?->image_path;
    $hasDescription = trim(strip_tags((string) $profile->description)) !== '';
    $professionalBranches = collect($professionalBranches ?? $profile->branches ?? [])
        ->filter(fn ($branch) => filled($branch->professional_experience) || filled($branch->services_offered));
@endphp

<section
    class="vendor-store-page-hero vendor-store-page-hero--about{{ $heroImage ? ' has-cover' : '' }}"
    @if($heroImage) style="--store-about-hero-cover: url('{{ asset($heroImage) }}')" @endif
>
    <div class="vendor-store-page-hero__overlay" aria-hidden="true"></div>
    <div class="container position-relative">
        <nav class="vendor-store-breadcrumb mb-3" aria-label="breadcrumb">
            <a href="{{ $homeRoute }}">{{ $homeLabel ?? 'Home' }}</a>
            <span class="mx-1">›</span>
            <span aria-current="page">About Us</span>
        </nav>

        <div class="vendor-about-hero__layout">
            @if(filled($profile->logo))
                <div class="vendor-about-hero__logo-wrap">
                    <img src="{{ asset($profile->logo) }}" alt="{{ $profileName }} logo" class="vendor-about-hero__logo">
                </div>
            @endif
            <div class="vendor-about-hero__copy">
                <p class="vendor-about-hero__eyebrow">About Us</p>
                <h1 class="vendor-store-page-hero__title">{{ $profileName }}</h1>
                <p class="vendor-store-page-hero__subtitle mb-0">
                    {{ $heroSubtitle ?? 'Discover our story, expertise, and what makes us a trusted partner on SoilnWater.' }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="vendor-about-page">
    <div class="container py-4 py-lg-5">
        <div class="vendor-about-detail">
            <article class="vendor-about-detail__panel">
                <div class="vendor-about-detail__panel-bg" aria-hidden="true"></div>
                <header class="vendor-about-detail__head">
                    <span class="vendor-about-detail__badge">Our Story</span>
                    <h2 class="vendor-about-detail__title">Company Profile</h2>
                    <p class="vendor-about-detail__intro mb-0">Learn more about who we are, what we do, and how we serve our customers.</p>
                </header>

                <div class="vendor-about-detail__body">
                    @if($hasDescription)
                        <div class="content-body">{!! \App\Support\StoreRichText::normalizeTypography($profile->description) !!}</div>
                    @else
                        <p class="vendor-about-detail__empty mb-0">About Us content is not added yet. Please check back soon.</p>
                    @endif
                </div>
            </article>

            @if($professionalBranches->isNotEmpty())
                <div class="vendor-about-professional mt-4 mt-lg-5">
                    <header class="vendor-about-professional__head">
                        <span class="vendor-about-detail__badge">Expertise</span>
                        <h2 class="vendor-about-detail__title mb-0">Professional Experience &amp; Services</h2>
                    </header>
                    <div class="row g-4 mt-1">
                        @foreach($professionalBranches as $branch)
                            <div class="col-12{{ $professionalBranches->count() > 1 ? ' col-lg-6' : '' }}">
                                <article class="vendor-about-professional__card">
                                    <div class="vendor-about-professional__card-head">
                                        <h3>{{ $branch->branch_name }}</h3>
                                        @if($branch->is_primary)
                                            <span class="vendor-about-professional__chip">Primary</span>
                                        @endif
                                    </div>
                                    @if(filled($branch->professional_experience))
                                        <div class="vendor-about-professional__block">
                                            <p class="vendor-about-professional__label">Professional Experience</p>
                                            <p class="vendor-about-professional__text">{{ $branch->professional_experience }}</p>
                                        </div>
                                    @endif
                                    @if(filled($branch->services_offered))
                                        <div class="vendor-about-professional__block">
                                            <p class="vendor-about-professional__label">Services Offered</p>
                                            <p class="vendor-about-professional__text">{{ $branch->services_offered }}</p>
                                        </div>
                                    @endif
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
