@forelse ($ads as $ad)
<div class="col">
<article class="card h-100 shadow-sm border-0 offer-coupon-card js-ad-modal-trigger" role="button" tabindex="0"
 data-ad-title="{{ $ad->title }}"
 data-ad-meta="{{ $ad->category?->name ?? 'Uncategorized' }}{{ $ad->subcategory ? ' • '.$ad->subcategory->name : '' }} • {{ $ad->reviewed_at?->format('d M Y') ?? 'N/A' }}"
 data-ad-description="{{ $ad->location ? 'Location: '.$ad->location : 'Approved user ad from marketplace.' }}"
 data-ad-image="{{ $ad->final_image ? asset($ad->final_image) : '' }}"
 data-ad-url="{{ route('frontend.ads.show', $ad) }}">
@if ($ad->final_image)<div class="offer-coupon-image-wrap"><img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" class="offer-coupon-image"></div>@endif
<div class="card-body d-flex flex-column gap-2"><h2 class="offer-card-title mb-1">{{ $ad->title }}</h2><div class="d-flex align-items-center flex-wrap gap-2 mt-auto offer-meta-row"><span class="offer-meta-pill offer-meta-pill-discount">{{ $ad->category?->name ?? 'Uncategorized' }}</span></div></div>
</article></div>
@empty
<div class="col-12 offer-empty-state"><div class="offer-empty-state-card"><div class="offer-empty-state-content"><h3 class="offer-empty-state-title mb-1">No ads found</h3><p class="offer-empty-state-text mb-0">Try changing filters.</p></div></div></div>
@endforelse
