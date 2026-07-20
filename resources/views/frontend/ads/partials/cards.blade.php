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

            // Wide marketplace sizes must break out of masonry or they cover the next row.
            if ($adWidth >= 900 && $adHeight >= 400) {
                $shapeClass = 'ad-hero';
            } elseif ($adWidth >= 900) {
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

    window.__adsMasonryInstances = window.__adsMasonryInstances || [];
    let instance = window.__adsMasonryInstances.find(function (entry) { return entry.gridId === gridId; });
    if (!instance) {
        instance = { gridId: gridId, fillerPool: fillerPool, gridState: new WeakMap() };
        window.__adsMasonryInstances.push(instance);
    } else {
        instance.fillerPool = fillerPool;
        if (!instance.gridState) instance.gridState = new WeakMap();
    }

    const GAP = 8;
    const TITLE_H = 50;
    const FILLER_POOL = fillerPool;
    const STATIC_SIZES = [
        { w: 458, h: 458 },
        { w: 458, h: 300 },
        { w: 458, h: 229 },
        { w: 360, h: 360 },
        { w: 320, h: 300 },
        { w: 229, h: 229 }
    ];

    let fillerIndex = 0;

    function getGridState(grid) {
        let state = instance.gridState.get(grid);
        if (!state) {
            state = { fillerPositions: [], contentHeight: 0 };
            instance.gridState.set(grid, state);
        }
        return state;
    }

    function getDims(card) {
        return {
            w: parseInt(card.dataset.adW || card.style.width || 458, 10),
            h: parseInt(card.dataset.adH || card.style.height || 229, 10)
        };
    }

    function isFullWidthCard(card) {
        if (
            card.classList.contains('ad-banner') ||
            card.classList.contains('ad-hero') ||
            card.classList.contains('banner') ||
            card.classList.contains('hero')
        ) {
            return true;
        }

        return parseInt(card.dataset.adW || '0', 10) >= 900;
    }

    function createFiller(width, height) {
        const matchingItems = FILLER_POOL.filter(function (item) {
            return Number(item.w) === Number(width) && Number(item.h) === Number(height);
        });
        const pool = matchingItems.length ? matchingItems : FILLER_POOL;
        const item = pool[fillerIndex % pool.length] || { label: 'Sponsored', image: null, url: null };
        fillerIndex++;

        const card = document.createElement('article');
        card.className = 'ad-card filler';
        card.dataset.filler = '1';
        card.style.width = width + 'px';
        card.style.height = height + 'px';

        const imageHtml = item.image
            ? '<img src="' + item.image + '" alt="' + item.label + '" loading="lazy" onerror="this.style.display=\'none\'; this.parentElement.innerHTML=\'<div class=&quot;filler-placeholder&quot;></div>\';">'
            : '<div class="filler-placeholder"></div>';

        const adTitleHtml = item.title
            ? '<span class="filler-title">' + item.title + '</span>'
            : '';

        const imageBlock = item.url
            ? '<a href="' + item.url + '" class="d-block w-100 h-100">' + imageHtml + '</a>'
            : imageHtml;

        card.innerHTML =
            '<span class="filler-label">' + item.label + '</span>' +
            adTitleHtml +
            '<div class="ad-image">' + imageBlock + '</div>';

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
        return { left: left, top: top, w: width, h: height };
    }

    function toRect(slot) {
        return {
            left: slot.left,
            right: slot.left + slot.w,
            top: slot.top,
            bottom: slot.top + slot.h
        };
    }

    function overlaps(a, left, top, width, height) {
        return !(
            left + width + GAP <= a.left ||
            left >= a.right + GAP ||
            top + height + GAP <= a.top ||
            top >= a.bottom + GAP
        );
    }

    function isFree(obstacles, left, top, width, height, packWidth) {
        if (left < 0 || top < 0 || left + width > packWidth + 0.5) return false;
        for (let i = 0; i < obstacles.length; i++) {
            if (overlaps(obstacles[i], left, top, width, height)) return false;
        }
        return true;
    }

    function uniqueSorted(values) {
        const sorted = values.slice().sort(function (a, b) { return a - b; });
        const out = [];
        for (let i = 0; i < sorted.length; i++) {
            if (!out.length || out[out.length - 1] !== sorted[i]) out.push(sorted[i]);
        }
        return out;
    }

    function findPlace(obstacles, width, height, packWidth) {
        const xs = [0];
        const ys = [0];

        for (let i = 0; i < obstacles.length; i++) {
            xs.push(obstacles[i].left);
            xs.push(obstacles[i].right + GAP);
            ys.push(obstacles[i].top);
            ys.push(obstacles[i].bottom + GAP);
        }

        const uniqX = uniqueSorted(xs);
        const uniqY = uniqueSorted(ys);
        let best = null;

        for (let yi = 0; yi < uniqY.length; yi++) {
            const top = uniqY[yi];
            for (let xi = 0; xi < uniqX.length; xi++) {
                const left = uniqX[xi];
                if (left + width > packWidth) continue;
                if (!isFree(obstacles, left, top, width, height, packWidth)) continue;
                if (!best || top < best.top || (top === best.top && left < best.left)) {
                    best = { left: left, top: top };
                }
            }
        }

        if (best) return best;

        const stackTop = obstacles.length
            ? Math.max.apply(null, obstacles.map(function (item) { return item.bottom; })) + GAP
            : 0;

        return { left: 0, top: stackTop };
    }

    function placeCard(card, pos, cardW, cardH, placed) {
        card.style.position = 'absolute';
        card.style.width = cardW + 'px';
        card.style.height = cardH + 'px';
        card.style.left = pos.left + 'px';
        card.style.top = pos.top + 'px';
        card.style.zIndex = '1';

        const imgBox = card.querySelector('.ad-image');
        if (imgBox) {
            imgBox.style.width = '100%';
            imgBox.style.height = 'calc(100% - ' + TITLE_H + 'px)';
            imgBox.style.minHeight = '0';
        }

        placed.push({
            el: card,
            left: pos.left,
            right: pos.left + cardW,
            top: pos.top,
            bottom: pos.top + cardH
        });
    }

    function hasRealAdAfter(slot, placedAds) {
        const slotBottom = slot.top + (slot.h || (slot.bottom - slot.top));
        for (let i = 0; i < placedAds.length; i++) {
            if (placedAds[i].top >= slotBottom - 1) {
                return true;
            }
        }
        return false;
    }

    function fillSponsoredGaps(grid, placedAds, packWidth, contentHeight, pinnedFillers) {
        // Keep only sponsored slots that still have a real ad after them.
        const fillers = (pinnedFillers || []).filter(function (slot) {
            return hasRealAdAfter(slot, placedAds);
        });

        const obstacles = placedAds
            .map(function (item) {
                return { left: item.left, right: item.right, top: item.top, bottom: item.bottom };
            })
            .concat(fillers.map(toRect));

        // Never place sponsored cards into the trailing/end area.
        const bottomLimit = Math.max(0, contentHeight - 40);
        if (bottomLimit <= 0 || packWidth < 200 || !placedAds.length) return fillers;

        const xs = [0];
        const ys = [0];
        obstacles.forEach(function (item) {
            xs.push(item.left);
            xs.push(item.right + GAP);
            ys.push(item.top);
            ys.push(item.bottom + GAP);
        });

        const uniqX = uniqueSorted(xs);
        const uniqY = uniqueSorted(ys);
        const pinnedKeys = {};
        fillers.forEach(function (slot) {
            pinnedKeys[slot.left + ':' + slot.top + ':' + slot.w + ':' + slot.h] = true;
        });

        for (let yi = 0; yi < uniqY.length; yi++) {
            const top = uniqY[yi];
            if (top >= bottomLimit) continue;

            for (let xi = 0; xi < uniqX.length; xi++) {
                const left = uniqX[xi];

                for (let si = 0; si < STATIC_SIZES.length; si++) {
                    const size = STATIC_SIZES[si];
                    if (left + size.w > packWidth) continue;
                    if (top + size.h > bottomLimit) continue;
                    if (!isFree(obstacles, left, top, size.w, size.h, packWidth)) continue;

                    // Skip end-of-grid sponsored slots when no real ad comes after them.
                    if (!hasRealAdAfter({ top: top, h: size.h }, placedAds)) continue;

                    const key = left + ':' + top + ':' + size.w + ':' + size.h;
                    if (pinnedKeys[key]) break;

                    const slot = mountFiller(grid, left, top, size.w, size.h);
                    fillers.push(slot);
                    obstacles.push(toRect(slot));
                    pinnedKeys[key] = true;
                    break;
                }
            }
        }

        return fillers;
    }

    // Always re-pack every card currently in this grid so append never stacks on top of old ads.
    function packGrid(grid, keepPinnedFillers) {
        const packWidth = grid.clientWidth;
        if (!packWidth) return;

        const state = getGridState(grid);
        const cards = Array.from(grid.querySelectorAll('.ad-card:not([data-filler])'));
        const candidatePinned = keepPinnedFillers && Array.isArray(state.fillerPositions)
            ? state.fillerPositions.filter(function (slot) { return slot.left + slot.w <= packWidth; })
            : [];

        grid.querySelectorAll('[data-filler]').forEach(function (el) { el.remove(); });
        fillerIndex = 0;

        function packCards(reservedFillers) {
            const placed = [];
            const obstacles = reservedFillers.map(toRect);

            cards.forEach(function (card) {
                const dims = getDims(card);
                let cardW = dims.w;
                let cardH = dims.h;

                if (cardW > packWidth) {
                    const scale = packWidth / cardW;
                    cardW = Math.max(1, Math.round(cardW * scale));
                    cardH = Math.max(1, Math.round(cardH * scale));
                }

                if (cardW >= packWidth * 0.85) {
                    cardW = packWidth;
                }

                const pos = findPlace(obstacles, cardW, cardH, packWidth);
                placeCard(card, pos, cardW, cardH, placed);
                obstacles.push({
                    left: pos.left,
                    right: pos.left + cardW,
                    top: pos.top,
                    bottom: pos.top + cardH
                });
            });

            return placed;
        }

        // Pack around previously kept sponsored slots (if any).
        let keptPinned = candidatePinned.slice();
        let placed = packCards(keptPinned);

        // Drop trailing sponsored slots that no longer have a real ad after them.
        const filteredPinned = keptPinned.filter(function (slot) {
            return hasRealAdAfter(slot, placed);
        });
        if (filteredPinned.length !== keptPinned.length) {
            keptPinned = filteredPinned;
            placed = packCards(keptPinned);
        }

        const contentHeight = placed.length
            ? Math.max.apply(null, placed.map(function (item) { return item.bottom; }))
            : 0;

        keptPinned.forEach(function (slot) {
            mountFiller(grid, slot.left, slot.top, slot.w, slot.h);
        });

        state.fillerPositions = fillSponsoredGaps(
            grid,
            placed,
            packWidth,
            contentHeight,
            keptPinned
        );

        let maxBottom = contentHeight;
        grid.querySelectorAll('.ad-card').forEach(function (card) {
            const bottom = card.offsetTop + card.offsetHeight;
            if (bottom > maxBottom) maxBottom = bottom;
        });

        state.contentHeight = maxBottom;
        grid.style.height = maxBottom + 'px';
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

        // Full rebuild (first load / filters / resize)
        if (!appendMode || !layout.querySelector('.masonry-grid, .ads-full-width')) {
            layout.innerHTML = '';
            instance.gridState = new WeakMap();

            let currentGrid = null;
            sourceCards.forEach(function (original) {
                const card = original.cloneNode(true);
                if (isFullWidthCard(card)) {
                    currentGrid = null;
                    const section = document.createElement('div');
                    section.className = 'ads-section ads-full-width';
                    section.appendChild(card);
                    layout.appendChild(section);
                } else {
                    if (!currentGrid) currentGrid = createMasonrySection(layout);
                    currentGrid.appendChild(card);
                }
            });

            requestAnimationFrame(function () {
                layout.querySelectorAll('.masonry-grid').forEach(function (grid) {
                    packGrid(grid, false);
                });
            });
            return;
        }

        // Append: keep old sections in place, only add missing ads, then re-pack changed grids.
        let currentGrid = layout.querySelector('.masonry-grid:last-of-type');
        if (!currentGrid) currentGrid = createMasonrySection(layout);

        const existingIds = new Set(
            Array.from(layout.querySelectorAll('.ad-card:not([data-filler])'))
                .map(function (card) { return card.dataset.adId; })
                .filter(Boolean)
        );

        const dirtyGrids = [];

        function markDirty(grid) {
            if (grid && dirtyGrids.indexOf(grid) === -1) dirtyGrids.push(grid);
        }

        sourceCards.forEach(function (original) {
            const adId = original.dataset.adId;
            if (adId && existingIds.has(adId)) return;

            const card = original.cloneNode(true);

            if (isFullWidthCard(card)) {
                currentGrid = null;
                const section = document.createElement('div');
                section.className = 'ads-section ads-full-width';
                section.appendChild(card);
                layout.appendChild(section);
            } else {
                if (!currentGrid) currentGrid = createMasonrySection(layout);
                currentGrid.appendChild(card);
                markDirty(currentGrid);
            }
        });

        if (!dirtyGrids.length) return;

        requestAnimationFrame(function () {
            dirtyGrids.forEach(function (grid) {
                // Keep already-shown sponsored slots in this grid stable across appends.
                packGrid(grid, true);
            });
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
