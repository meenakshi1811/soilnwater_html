@php
    use App\Support\HomepageCategoryCardImage;

    $ad = $ad ?? null;
    $adImage = '';
    if ($ad) {
        $entityCover = filled($ad->final_image) ? asset($ad->final_image) : '';
        $adImage = HomepageCategoryCardImage::forUserAd($ad, $entityCover);
    }
@endphp
@if ($ad && $adImage !== '')
    <article
        class="homepage-vendors-mobile-ad"
        role="link"
        tabindex="0"
        data-ad-id="{{ $ad->id }}"
        data-ad-url="{{ $ad->shareUrl() }}"
        data-ad-description="{{ $ad->short_description ?: 'Special marketplace ad available now.' }}"
    >
        <div class="homepage-vendors-mobile-ad__media">
            <img
                src="{{ $adImage }}"
                alt="{{ $ad->title }}"
                loading="lazy"
                decoding="async"
                data-ad-id="{{ $ad->id }}"
                data-ad-url="{{ $ad->shareUrl() }}"
                data-ad-description="{{ $ad->short_description ?: 'Special marketplace ad available now.' }}"
            >
        </div>
        <div class="homepage-vendors-mobile-ad__body">
            <span class="homepage-vendors-mobile-ad__badge">Sponsored</span>
            <h3 class="homepage-vendors-mobile-ad__title">{{ $ad->title }}</h3>
            <span class="homepage-vendors-mobile-ad__cta">View ad <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
        </div>
    </article>
@endif
