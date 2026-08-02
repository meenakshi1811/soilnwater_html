@php
    use App\Support\AdSizes;

    $adModalMeta = $adModalMeta ?? ($ad->category?->name ?? 'Uncategorized');
    $adModalDescription = $adModalDescription ?? ($ad->short_description ?: 'Special marketplace ad available now.');
    $sizeConfig = AdSizes::dimensionsForSizeType((string) ($ad->size_type ?? ''));
    $adModalW = (int) ($adModalW ?? ($sizeConfig['w'] ?? ($ad->adSize?->width ?? 458)));
    $adModalH = (int) ($adModalH ?? ($sizeConfig['h'] ?? ($ad->adSize?->height ?? 229)));
@endphp
data-bs-toggle="modal"
data-bs-target="#adDetailsModal"
data-ad-id="{{ $ad->id }}"
data-ad-title="{{ $ad->title }}"
data-ad-meta="{{ $adModalMeta }}"
data-ad-description="{{ $adModalDescription }}"
data-ad-image="{{ asset($ad->final_image) }}"
data-ad-url="{{ $ad->shareUrl() }}"
data-ad-w="{{ $adModalW }}"
data-ad-h="{{ $adModalH }}"
