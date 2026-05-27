@php
    $adSizes = \App\Support\AdSizes::all();
    $sponsoredFillers = collect($sponsoredFillers ?? [])->values()->all();
    $selectedCategoryNamesByAdId = $selectedCategoryNamesByAdId ?? [];
    $gridId = $gridId ?? 'ads';
    $autoRender = $autoRender ?? ($gridId === 'ads');
@endphp

<div id="{{ $gridId }}Source" class="d-none">
    @forelse ($ads as $ad)
        @php
            $rawSizeType = (string) ($ad->size_type ?? '');

            $normalizedSizeType = \Illuminate\Support\Str::of($rawSizeType)
                ->lower()
                ->replace([' ', '-'], '_')
                ->value();

            $sizeConfig = $adSizes[$rawSizeType]
                ?? $adSizes[$normalizedSizeType]
                ?? collect($adSizes)->first(function ($config, $key) use ($normalizedSizeType) {
                    return \Illuminate\Support\Str::of($key)
                        ->lower()
                        ->replace([' ', '-'], '_')
                        ->value() === $normalizedSizeType;
                });

            $adWidth  = (int) ($sizeConfig['w'] ?? ($ad->adSize?->width ?? 458));
            $adHeight = (int) ($sizeConfig['h'] ?? ($ad->adSize?->height ?? 229));
            $categoryNames = $selectedCategoryNamesByAdId[$ad->id] ?? [];
            $adMeta = $categoryNames !== [] ? implode(', ', $categoryNames) : ($ad->category?->name ?? 'Uncategorized');

            $shapeClass = 'ad-landscape';

            if ($adWidth >= 1100 && $adHeight >= 420) {
                $shapeClass = 'ad-hero';
            } elseif ($adWidth >= 1100) {
                $shapeClass = 'ad-banner';
            } elseif ($adWidth <= 280 && $adHeight >= 400) {
                $shapeClass = 'ad-portrait';
            } elseif (abs($adWidth - $adHeight) <= 40) {
                $shapeClass = 'ad-square';
            }
        @endphp

        <article
            class="ad-card db-sized {{ $shapeClass }} js-ad-modal-trigger"
            style="--ad-w: {{ $adWidth }}; --ad-h: {{ $adHeight }};"
            role="button"
            tabindex="0"
            data-ad-w="{{ $adWidth }}"
            data-ad-h="{{ $adHeight }}"
            data-ad-title="{{ $ad->title }}"
            data-ad-meta="{{ $adMeta }}"
            data-ad-description="{{ $ad->short_description ?: 'Special marketplace ad available now.' }}"
            data-ad-image="{{ $ad->final_image ? asset($ad->final_image) : '' }}"
            data-ad-url="{{ route('frontend.ads.show', $ad) }}"
            data-ad-id="{{ $ad->id }}"
        >
            <h3 class="ad-title">{{ $ad->title }}</h3>
            <div class="ad-image">
                @if ($ad->final_image)
                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" loading="lazy">
                @endif
            </div>
        </article>
    @empty
        <div class="text-center py-4 w-100">
            <h4>No ads found</h4>
        </div>
    @endforelse
</div>

<div id="{{ $gridId }}Layout" class="ads-layout"></div>

@once
<script>
window.renderAdsMarketCards = window.renderAdsMarketCards || function (gridId, fillerPool) {
    gridId = gridId || 'ads';
    fillerPool = Array.isArray(fillerPool) ? fillerPool : [];

    window.__adsMasonryInstances = window.__adsMasonryInstances || [];
    const existingIndex = window.__adsMasonryInstances.findIndex((entry) => entry.gridId === gridId);
    const instance = { gridId, fillerPool };
    if (existingIndex >= 0) {
        window.__adsMasonryInstances[existingIndex] = instance;
    } else {
        window.__adsMasonryInstances.push(instance);
    }

    const GAP = 8;
    const SEARCH_STEP = 1;

    const FILLER_POOL = fillerPool;

    let fillerIndex = 0;

    function getDims(card) {
        return {
            w: parseInt(card.dataset.adW || card.style.width || 458, 10),
            h: parseInt(card.dataset.adH || card.style.height || 229, 10)
        };
    }

    function createFiller(width, height) {
        const matchingItems = FILLER_POOL.filter((item) => Number(item.w) === Number(width) && Number(item.h) === Number(height));
        const pool = matchingItems.length ? matchingItems : FILLER_POOL;
        const item = pool[fillerIndex % pool.length] || { label: 'Sponsored', image: null, url: null };
        fillerIndex++;

        const card = document.createElement('article');
        card.className = 'ad-card filler';
        card.dataset.filler = '1';
        card.style.width = width + 'px';
        card.style.height = height + 'px';

        const imageHtml = item.image
            ? `<img src="${item.image}" alt="${item.label}" loading="lazy" onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=&quot;filler-placeholder&quot;></div>';">`
            : `<div class="filler-placeholder"></div>`;

        const adTitleHtml = item.title
            ? `<span class="filler-title">${item.title}</span>`
            : '';

        const imageBlock = item.url
            ? `<a href="${item.url}" class="d-block w-100 h-100">${imageHtml}</a>`
            : imageHtml;

        card.innerHTML = `
            <span class="filler-label">${item.label}</span>
            ${adTitleHtml}
            <div class="ad-image">${imageBlock}</div>
        `;

        return card;
    }

    function packGrid(grid) {
        const cards = Array.from(grid.querySelectorAll('.ad-card:not([data-filler])'));
        if (!cards.length) return;

        grid.querySelectorAll('[data-filler]').forEach(el => el.remove());

        const containerWidth = grid.clientWidth;
        if (!containerWidth) return;

        const placed = [];

        function isFree(left, top, width, height) {
            if (left < 0 || left + width > containerWidth) {
                return false;
            }

            return !placed.some(item => {
                return !(
                    left + width + GAP <= item.left ||
                    left >= item.right + GAP ||
                    top + height + GAP <= item.top ||
                    top >= item.bottom + GAP
                );
            });
        }

        function findPlace(width, height) {
            for (let top = 0; top <= 5000; top += SEARCH_STEP) {
                for (let left = 0; left <= containerWidth - width; left += SEARCH_STEP) {
                    if (isFree(left, top, width, height)) {
                        return { left, top };
                    }
                }
            }

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Place real DB ads in actual DB size
        |--------------------------------------------------------------------------
        */
        cards.forEach(card => {
            const dims = getDims(card);

            let cardW = dims.w;
            let cardH = dims.h;

            if (cardW > containerWidth) {
                const scale = containerWidth / cardW;
                cardW = Math.round(cardW * scale);
                cardH = Math.round(cardH * scale);
            }

            const pos = findPlace(cardW, cardH);

            if (!pos) return;

            card.style.width = cardW + 'px';
            card.style.height = cardH + 'px';
            card.style.left = pos.left + 'px';
            card.style.top = pos.top + 'px';

            const imgBox = card.querySelector('.ad-image');
            const titleBox = card.querySelector('.ad-title');

            if (imgBox) {
                const titleHeight = titleBox ? Math.ceil(titleBox.getBoundingClientRect().height) : 50;
                imgBox.style.width = '100%';
                imgBox.style.height = `calc(100% - ${titleHeight}px)`;
            }

            placed.push({
                el: card,
                left: pos.left,
                right: pos.left + cardW,
                top: pos.top,
                bottom: pos.top + cardH
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Fill only internal blank spaces
        |--------------------------------------------------------------------------
        | Important:
        | - Static ads will not show at the bottom/end.
        | - Bigger static ads are tried first.
        |--------------------------------------------------------------------------
        */
        const staticSizes = [
            { w: 520, h: 360 },
            { w: 520, h: 300 },
            { w: 458, h: 458 },
            { w: 458, h: 300 },
            { w: 458, h: 229 },
            { w: 360, h: 360 },
            { w: 320, h: 300 },
            { w: 229, h: 229 }
        ];

        const currentHeight = placed.length
            ? Math.max(...placed.map(item => item.bottom))
            : 0;

        const bottomLimit = currentHeight - 40;

        if (bottomLimit > 0) {
            for (let top = 0; top <= bottomLimit; top += 10) {
                for (let left = 0; left <= containerWidth; left += 10) {

                    for (let i = 0; i < staticSizes.length; i++) {
                        const size = staticSizes[i];

                        if (left + size.w > containerWidth) {
                            continue;
                        }

                        if (top + size.h > currentHeight - 40) {
                            continue;
                        }

                        if (isFree(left, top, size.w, size.h)) {
                            const filler = createFiller(size.w, size.h);

                            filler.style.position = 'absolute';
                            filler.style.width = size.w + 'px';
                            filler.style.height = size.h + 'px';
                            filler.style.left = left + 'px';
                            filler.style.top = top + 'px';

                            grid.appendChild(filler);

                            placed.push({
                                el: filler,
                                left: left,
                                right: left + size.w,
                                top: top,
                                bottom: top + size.h
                            });

                            break;
                        }
                    }
                }
            }
        }

        const finalHeight = placed.length
            ? Math.max(...placed.map(item => item.bottom))
            : 0;

        grid.style.height = finalHeight + 'px';
    }

    function createMasonrySection(layout) {
        const section = document.createElement('div');
        section.className = 'ads-section masonry-grid';
        layout.appendChild(section);
        return section;
    }

    function buildLayout() {
        const source = document.getElementById(gridId + 'Source');
        const layout = document.getElementById(gridId + 'Layout');

        if (!source || !layout) return;

        layout.innerHTML = '';

        let currentGrid = null;

        Array.from(source.querySelectorAll('.ad-card')).forEach(original => {
            const card = original.cloneNode(true);

            const isFullWidth =
                card.classList.contains('banner') ||
                card.classList.contains('hero');

            if (isFullWidth) {
                currentGrid = null;

                const section = document.createElement('div');
                section.className = 'ads-section ads-full-width';

                section.appendChild(card);
                layout.appendChild(section);
            } else {
                if (!currentGrid) {
                    currentGrid = createMasonrySection(layout);
                }

                currentGrid.appendChild(card);
            }
        });

        requestAnimationFrame(() => {
            layout.querySelectorAll('.masonry-grid').forEach(packGrid);
        });
    }

    buildLayout();
};

if (!window.__adsMasonryResizeBound) {
    window.__adsMasonryResizeBound = true;
    window.addEventListener('resize', function () {
        clearTimeout(window.__adsMasonryResizeTimer);
        window.__adsMasonryResizeTimer = setTimeout(function () {
            (window.__adsMasonryInstances || []).forEach(function (entry) {
                if (typeof window.renderAdsMarketCards === 'function') {
                    window.renderAdsMarketCards(entry.gridId, entry.fillerPool);
                }
            });
        }, 250);
    });
}

</script>
@endonce

@if($autoRender)
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.renderAdsMarketCards === 'function') {
        window.renderAdsMarketCards(@json($gridId), @json($sponsoredFillers));
    }
});
</script>
@endif
