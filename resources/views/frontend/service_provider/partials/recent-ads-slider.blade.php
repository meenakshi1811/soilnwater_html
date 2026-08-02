@php
    $ads = ($ads ?? collect())->values();
    $selectedCategoryNamesByAdId = $selectedCategoryNamesByAdId ?? [];
    $sectionTitle = $sectionTitle ?? 'Recent Ads';
    $sliderLabel = $sliderLabel ?? $sectionTitle.' slider';
    $moduleLabels = \App\Support\ModulePermissions::modules();
@endphp

@if($ads->isNotEmpty())
    <section class="vendor-store-section vendor-store-recent-ads-section recent-ads-section">
        <div class="container">
            <div class="sec-head vendor-store-recent-ads-head">
                <div class="sec-title"><span class="icon"><i class="fa-solid fa-rectangle-ad"></i></span> {{ $sectionTitle }}</div>
                <a class="view-all" href="{{ route('frontend.ads.index') }}">VIEW ALL ▶</a>
            </div>
            <div class="ad-slider auto-ad-slider recent-ads-slider vendor-store-recent-ads-slider" data-show-arrows="true" data-show-dots="false" aria-label="{{ $sliderLabel }}">
                @foreach($ads->chunk(6) as $adsChunk)
                    <div class="ad-slide {{ $loop->first ? 'is-active' : '' }}" @if(! $loop->first) hidden @endif>
                        <div class="product-grid-4 recent-ads-grid vendor-store-recent-ads-grid">
                            @foreach($adsChunk as $ad)
                                @php
                                    $selectedCategoryNames = $selectedCategoryNamesByAdId[$ad->id] ?? [];
                                    if ($selectedCategoryNames === [] && $ad->category?->name) {
                                        $selectedCategoryNames = [$ad->category->name];
                                    }

                                    $selectedServiceNames = collect($ad->selected_modules ?? [])
                                        ->filter(fn ($key) => is_string($key) && isset($moduleLabels[$key]))
                                        ->map(fn ($key) => $moduleLabels[$key])
                                        ->values()
                                        ->all();
                                @endphp
                                <article class="prod-card recent-ad-card vendor-store-recent-ad-card js-ad-modal-trigger"
                                    role="button"
                                    tabindex="0"
                                    @include('frontend.ads.partials.ad-modal-attrs', [
                                        'ad' => $ad,
                                        'adModalMeta' => $selectedCategoryNames !== [] ? implode(', ', $selectedCategoryNames) : $defaultCategoryLabel,
                                    ])
                                    data-ad-services="{{ implode(', ', $selectedServiceNames) }}"
                                >
                                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" width="768" height="1080" loading="{{ $loop->parent->first ? 'eager' : 'lazy' }}" fetchpriority="{{ $loop->parent->first && $loop->first ? 'high' : 'low' }}" decoding="async">
                                    <div class="prod-card-body">
                                        <h6 class="mb-1 offer-coupon-title">{{ $ad->title }}</h6>
                                        <span class="recent-ad-meta">
                                            <i class="fa-solid fa-layer-group"></i>
                                            {{ ($selectedCategoryNames !== [] ? implode(', ', $selectedCategoryNames) : 'Services') }} • {{ $ad->created_at?->format('d M Y') ?? 'N/A' }}
                                        </span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
