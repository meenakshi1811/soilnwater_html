@php
    $profileUrl = $institute->publicUrl();
    $aboutText = trim((string) ($institute->about ?: $institute->description));
    $hasClasses = ($institute->schoolClasses ?? collect())->isNotEmpty() || collect($institute->grades_offered ?? [])->isNotEmpty();
    $hasAchievements = ($institute->achievements ?? collect())->isNotEmpty();
    $hasPerformers = ($institute->topPerformers ?? collect())->isNotEmpty();
    $hasBooks = ($institute->books ?? collect())->isNotEmpty();
    $hasGallery = count($institute->galleryUrls()) > 1;
    $hasFacilities = collect($institute->facilities ?? [])->isNotEmpty();
    $entityLabel = ($ownerRole ?? 'school') === 'school' ? 'School' : 'Institute';
    $listingContext = $listingContext ?? (($ownerRole ?? 'school') === 'school' ? 'schools' : 'institutes');
    $diaryUrl = route($listingContext.'.diary', $institute->slug);
@endphp

<header class="vendor-store-header">
    <div class="container">
        <div class="vendor-store-header__inner">
            <a href="{{ $profileUrl }}" class="vendor-store-brand">
                @if($institute->logo)
                    <img src="{{ asset($institute->logo) }}" alt="{{ $institute->displayName() }}" height="44">
                @else
                    <span class="vendor-store-brand__text">{{ $institute->displayName() }}</span>
                @endif
            </a>

            @include('frontend.partials.marketplace-store-header-link', [
                'storeUrl' => $profileUrl,
                'linkLabel' => $entityLabel,
            ])

            <button class="vendor-store-mobile-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#schoolStoreNav" aria-controls="schoolStoreNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="vendor-store-nav collapse d-lg-flex" id="schoolStoreNav">
                <a href="#sch-overview" class="vendor-store-nav-link js-sch-nav-link {{ ($activeNav ?? '') === 'home' ? 'is-active' : '' }}">Home</a>

                @if(filled($aboutText))
                    <a href="#sch-about" class="vendor-store-nav-link js-sch-nav-link">About Us</a>
                @endif

                @if($hasClasses)
                    <a href="#sch-classes" class="vendor-store-nav-link js-sch-nav-link">Classes</a>
                @endif

                @if($hasPerformers)
                    <a href="#sch-performers" class="vendor-store-nav-link js-sch-nav-link">Top Performers</a>
                @endif

                @if($hasAchievements)
                    <a href="#sch-achievements" class="vendor-store-nav-link js-sch-nav-link">Achievements</a>
                @endif

                @if($hasBooks)
                    <a href="#sch-books" class="vendor-store-nav-link js-sch-nav-link">Books</a>
                @endif

                @if($hasGallery)
                    <a href="#sch-gallery" class="vendor-store-nav-link js-sch-nav-link">Gallery</a>
                @endif

                @if($hasFacilities)
                    <a href="#sch-facilities" class="vendor-store-nav-link js-sch-nav-link">Facilities</a>
                @endif

                <a href="#sch-contact" class="vendor-store-nav-link js-sch-nav-link">Contact</a>

                <a href="{{ $diaryUrl }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'diary' ? 'is-active' : '' }}">Diary</a>

                <button type="button" class="vendor-share-trigger vendor-store-nav-share" data-bs-toggle="modal" data-bs-target="#schoolShareModal">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Scan &amp; Share</span>
                </button>
            </nav>
        </div>
    </div>
</header>

<div class="modal fade" id="schoolShareModal" tabindex="-1" aria-labelledby="schoolShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content vendor-share-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="schoolShareModalLabel">Scan & Share this {{ strtolower($entityLabel) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="vendor-share-qr-wrap mb-3 text-center">
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($profileUrl) }}"
                        class="vendor-share-qr"
                        alt="QR code for {{ $institute->displayName() }}"
                        loading="lazy"
                    >
                </div>
                <label for="schoolStoreShareUrl" class="form-label small text-muted mb-1">{{ $entityLabel }} link</label>
                <div class="input-group mb-3">
                    <input id="schoolStoreShareUrl" type="text" class="form-control" readonly value="{{ $profileUrl }}">
                    <button class="btn btn-outline-secondary js-copy-store-url" type="button" data-url="{{ $profileUrl }}">Copy</button>
                </div>
                <div class="vendor-share-actions">
                    <a href="https://wa.me/?text={{ urlencode('Check out this '.$entityLabel.': '.$profileUrl) }}" target="_blank" rel="noopener" class="vendor-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($profileUrl) }}" target="_blank" rel="noopener" class="vendor-share-btn share-facebook"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span></a>
                    <a href="https://www.instagram.com/?url={{ urlencode($profileUrl) }}" target="_blank" rel="noopener" class="vendor-share-btn share-instagram"><i class="fa-brands fa-instagram"></i><span>Instagram</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
