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
window.renderAdsMarketCards = window.renderAdsMarketCards || function (gridId, fillerPool, options) {
    gridId = gridId || 'ads';
    fillerPool = Array.isArray(fillerPool) ? fillerPool : [];
    options = options || {};
    const appendMode = !!options.append;
    const resetFillers = !!options.resetFillers || !appendMode;

    window.__adsMasonryInstances = window.__adsMasonryInstances || [];
    const existingIndex = window.__adsMasonryInstances.findIndex((entry) => entry.gridId === gridId);
    let instance;

    if (existingIndex >= 0) {
        instance = window.__adsMasonryInstances[existingIndex];
        instance.fillerPool = fillerPool;
        if (resetFillers) {
            instance.fillerPositions = [];
            instance.railWidth = 0;
            instance.placedAds = [];
            instance.contentHeight = 0;
        }
    } else {
        instance = {
            gridId,
            fillerPool,
            fillerPositions: [],
            railWidth: 0,
            placedAds: [],
            contentHeight: 0
        };
        window.__adsMasonryInstances.push(instance);
    }

    const GAP = 8;
    const TITLE_H = 50;
    const FILLER_POOL = fillerPool;
    const RAIL_SIZES = [
        { w: 320, h: 300 },
        { w: 360, h: 360 },
        { w: 229, h: 229 },
        { w: 458, h: 229 },
        { w: 458, h: 300 },
        { w: 458, h: 458 }
    ];

    let fillerIndex = 0;

    function getDims(card) {
        return {
            w: parseInt(card.dataset.adW || card.style.width || 458, 10),
            h: parseInt(card.dataset.adH || card.style.height || 229, 10)
        };
    }

    function pickRailWidth(containerWidth) {
        if (containerWidth < 900) return 0;

        for (let i = 0; i < RAIL_SIZES.length; i++) {
            const size = RAIL_SIZES[i];
            if (size.w + GAP + 458 <= containerWidth) {
                return size.w;
            }
        }

        return 0;
    }

    function pickRailSlotSize(railWidth) {
        for (let i = 0; i < RAIL_SIZES.length; i++) {
            if (RAIL_SIZES[i].w <= railWidth) {
                return RAIL_SIZES[i];
            }
        }

        return { w: Math.min(railWidth, 229), h: 229 };
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

    function mountFiller(grid, left, top, width, height) {
        const filler = createFiller(width, height);

        filler.style.position = 'absolute';
        filler.style.width = width + 'px';
        filler.style.height = height + 'px';
        filler.style.left = left + 'px';
        filler.style.top = top + 'px';

        grid.appendChild(filler);

        return { left, top, w: width, h: height };
    }

    function overlaps(a, left, top, width, height) {
        return !(
            left + width + GAP <= a.left ||
            left >= a.right + GAP ||
            top + height + GAP <= a.top ||
            top >= a.bottom + GAP
        );
    }

    function isFree(placed, left, top, width, height, packWidth) {
        if (left < 0 || left + width > packWidth + 0.5) {
            return false;
        }

        for (let i = 0; i < placed.length; i++) {
            if (overlaps(placed[i], left, top, width, height)) {
                return false;
            }
        }

        return true;
    }

    // Skyline packing: only test edges of already-placed cards (not every pixel).
    function findPlace(placed, width, height, packWidth) {
        const xs = [0];
        const ys = [0];

        for (let i = 0; i < placed.length; i++) {
            const item = placed[i];
            xs.push(item.right + GAP);
            ys.push(item.bottom + GAP);
        }

        xs.sort((a, b) => a - b);
        ys.sort((a, b) => a - b);

        const uniqX = [];
        const uniqY = [];
        for (let i = 0; i < xs.length; i++) {
            if (!uniqX.length || uniqX[uniqX.length - 1] !== xs[i]) uniqX.push(xs[i]);
        }
        for (let i = 0; i < ys.length; i++) {
            if (!uniqY.length || uniqY[uniqY.length - 1] !== ys[i]) uniqY.push(ys[i]);
        }

        let best = null;

        for (let yi = 0; yi < uniqY.length; yi++) {
            const top = uniqY[yi];
            for (let xi = 0; xi < uniqX.length; xi++) {
                const left = uniqX[xi];
                if (left + width > packWidth) continue;
                if (!isFree(placed, left, top, width, height, packWidth)) continue;
                if (!best || top < best.top || (top === best.top && left < best.left)) {
                    best = { left, top };
                }
            }
        }

        if (best) return best;

        const stackTop = placed.length
            ? Math.max.apply(null, placed.map((item) => item.bottom)) + GAP
            : 0;

        return { left: 0, top: stackTop };
    }

    function placeCard(card, pos, cardW, cardH, placed) {
        card.style.position = 'absolute';
        card.style.width = cardW + 'px';
        card.style.height = cardH + 'px';
        card.style.left = pos.left + 'px';
        card.style.top = pos.top + 'px';

        const imgBox = card.querySelector('.ad-image');
        if (imgBox) {
            imgBox.style.width = '100%';
            imgBox.style.height = 'calc(100% - ' + TITLE_H + 'px)';
        }

        placed.push({
            el: card,
            left: pos.left,
            right: pos.left + cardW,
            top: pos.top,
            bottom: pos.top + cardH
        });
    }

    function syncRailFillers(grid, containerWidth, contentHeight) {
        const railWidth = instance.railWidth || 0;
        if (!railWidth || contentHeight <= 0) {
            instance.fillerPositions = [];
            grid.querySelectorAll('[data-filler]').forEach((el) => el.remove());
            return;
        }

        const slot = pickRailSlotSize(railWidth);
        const railLeft = containerWidth - railWidth;
        const slots = [];
        let top = 0;

        while (top + slot.h <= contentHeight) {
            slots.push({ left: railLeft, top: top, w: slot.w, h: slot.h });
            top += slot.h + GAP;
        }

        const existing = Array.isArray(instance.fillerPositions) ? instance.fillerPositions : [];
        const existingCount = existing.length;

        // Keep earlier sponsored slots fixed; only grow the rail downward.
        let next = existing.slice();
        for (let i = existingCount; i < slots.length; i++) {
            next.push(slots[i]);
        }

        // If rail geometry changed (resize), rebuild from scratch.
        const geometryChanged = existingCount > 0 && (
            existing[0].left !== railLeft ||
            existing[0].w !== slot.w ||
            existing[0].h !== slot.h
        );

        if (geometryChanged || existingCount > slots.length) {
            next = slots.slice();
            grid.querySelectorAll('[data-filler]').forEach((el) => el.remove());
            fillerIndex = 0;
            next.forEach((s) => mountFiller(grid, s.left, s.top, s.w, s.h));
        } else {
            for (let i = existingCount; i < next.length; i++) {
                const s = next[i];
                mountFiller(grid, s.left, s.top, s.w, s.h);
            }
        }

        instance.fillerPositions = next;
    }

    function packGrid(grid, cardsToPlace, rebuildAll) {
        const containerWidth = grid.clientWidth;
        if (!containerWidth) return;

        if (rebuildAll) {
            instance.railWidth = pickRailWidth(containerWidth);
            instance.placedAds = [];
            instance.fillerPositions = [];
            instance.contentHeight = 0;
            grid.querySelectorAll('[data-filler]').forEach((el) => el.remove());
        }

        const railWidth = instance.railWidth || 0;
        const packWidth = railWidth > 0 ? Math.max(0, containerWidth - railWidth - GAP) : containerWidth;
        const placed = instance.placedAds.slice();

        cardsToPlace.forEach((card) => {
            const dims = getDims(card);
            let cardW = dims.w;
            let cardH = dims.h;

            if (cardW > packWidth) {
                const scale = packWidth / cardW;
                cardW = Math.max(1, Math.round(cardW * scale));
                cardH = Math.max(1, Math.round(cardH * scale));
            }

            const pos = findPlace(placed, cardW, cardH, packWidth);
            placeCard(card, pos, cardW, cardH, placed);
        });

        instance.placedAds = placed;
        instance.contentHeight = placed.length
            ? Math.max.apply(null, placed.map((item) => item.bottom))
            : 0;

        syncRailFillers(grid, containerWidth, instance.contentHeight);
        grid.style.height = instance.contentHeight + 'px';
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

        const sourceCards = Array.from(source.querySelectorAll('.ad-card'));

        if (!appendMode || !layout.querySelector('.masonry-grid, .ads-full-width')) {
            layout.innerHTML = '';

            let currentGrid = null;
            const masonryCards = [];

            sourceCards.forEach((original) => {
                const card = original.cloneNode(true);
                const isFullWidth =
                    card.classList.contains('banner') ||
                    card.classList.contains('hero') ||
                    card.classList.contains('ad-banner') ||
                    card.classList.contains('ad-hero');

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
                    masonryCards.push(card);
                }
            });

            requestAnimationFrame(() => {
                layout.querySelectorAll('.masonry-grid').forEach((grid) => {
                    const cards = Array.from(grid.querySelectorAll('.ad-card:not([data-filler])'));
                    packGrid(grid, cards, true);
                });
            });

            return;
        }

        // Incremental append: keep existing cards in place, only pack new ones.
        let currentGrid = layout.querySelector('.masonry-grid:last-of-type');
        if (!currentGrid) {
            currentGrid = createMasonrySection(layout);
        }

        const existingIds = new Set(
            Array.from(layout.querySelectorAll('.ad-card:not([data-filler])'))
                .map((card) => card.dataset.adId)
                .filter(Boolean)
        );

        const newCards = [];

        sourceCards.forEach((original) => {
            const adId = original.dataset.adId;
            if (adId && existingIds.has(adId)) return;

            const card = original.cloneNode(true);
            const isFullWidth =
                card.classList.contains('banner') ||
                card.classList.contains('hero') ||
                card.classList.contains('ad-banner') ||
                card.classList.contains('ad-hero');

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
                newCards.push(card);
            }
        });

        if (!newCards.length) return;

        requestAnimationFrame(() => {
            const grid = layout.querySelector('.masonry-grid:last-of-type');
            if (!grid) return;
            packGrid(grid, newCards, false);
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
                    window.renderAdsMarketCards(entry.gridId, entry.fillerPool, { resetFillers: true });
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
