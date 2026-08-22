@php
    $adSizes = \App\Support\AdSizes::all();
    $sponsoredFillers = collect($sponsoredFillers ?? [])->values()->all();
    $sponsoredBlankSizes = collect($sponsoredBlankSizes ?? \App\Support\AdSizes::sponsoredFillerSizesFromDatabase())->values()->all();
    $staticSponsoredImages = collect($staticSponsoredImages ?? \App\Support\StaticSponsoredAds::imageUrls())->values()->all();
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
            $adImageHeight = $adHeight;
            $adMaxDim = max($adWidth, $adHeight);
            $adArea = $adWidth * $adHeight;
            $adIsPaid = (bool) ($sizeConfig['is_paid'] ?? ($ad->adSize?->is_paid ?? false));
            $mobileSizeTier = 'sm';

            if ($adWidth >= 900) {
                $mobileSizeTier = 'full';
            } else            if ($adMaxDim <= 320 || $adArea <= 70000) {
                $mobileSizeTier = 'xs';
            } elseif (abs($adWidth - $adHeight) <= 40 && $adMaxDim <= 520) {
                $mobileSizeTier = 'xs';
            } elseif ($adMaxDim <= 520 || $adArea <= 260000) {
                $mobileSizeTier = 'sm';
            } else {
                $mobileSizeTier = 'md';
            }

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
            class="ad-card db-sized {{ $shapeClass }} ad-mobile-tier-{{ $mobileSizeTier }} js-ad-modal-trigger"
            style="--ad-w: {{ $adWidth }}; --ad-h: {{ $adHeight }}; --ad-image-h: {{ $adImageHeight }};"
            role="button"
            tabindex="0"
            data-bs-toggle="modal"
            data-bs-target="#adDetailsModal"
            data-ad-w="{{ $adWidth }}"
            data-ad-h="{{ $adHeight }}"
            data-ad-mobile-tier="{{ $mobileSizeTier }}"
            data-ad-is-paid="{{ $adIsPaid ? '1' : '0' }}"
            data-ad-title="{{ $ad->title }}"
            data-ad-meta="{{ $adMeta }}"
            data-ad-description="{{ $ad->short_description ?: 'Special marketplace ad available now.' }}"
            data-ad-image="{{ $ad->final_image ? asset($ad->final_image) : '' }}"
            data-ad-url="{{ $ad->shareUrl() }}"
            data-ad-id="{{ $ad->id }}"
        >
            <h3 class="ad-title visually-hidden">{{ $ad->title }}</h3>
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
        instance = { gridId: gridId, fillerPool: fillerPool, blankSizes: [], staticImages: [], gridState: new WeakMap() };
        window.__adsMasonryInstances.push(instance);
    } else {
        instance.fillerPool = fillerPool;
        if (!instance.gridState) instance.gridState = new WeakMap();
    }

    if (Array.isArray(options.blankSizes)) {
        instance.blankSizes = options.blankSizes;
    } else if (!Array.isArray(instance.blankSizes)) {
        instance.blankSizes = [];
    }

    if (Array.isArray(options.staticImages)) {
        instance.staticImages = options.staticImages;
    } else if (!Array.isArray(instance.staticImages)) {
        instance.staticImages = [];
    }

    const GAP = 8;
    const TITLE_H = 0;
    const FILLER_TITLE_H = 34;
    const STACKED_LAYOUT_QUERY = window.matchMedia('(max-width: 991px)');
    const FILLER_POOL = fillerPool;
    const BLANK_SIZES = instance.blankSizes.slice().sort(function (a, b) {
        return (Number(b.w) * Number(b.h)) - (Number(a.w) * Number(a.h));
    });

    if (!instance.usedFillerKeys) {
        instance.usedFillerKeys = new Set();
    }

    function resetUsedFillers() {
        instance.usedFillerKeys = new Set();
    }

    function isStackedLayout() {
        return STACKED_LAYOUT_QUERY.matches;
    }

    function getMobileTierMultiplier(tier, isPaid, isFiller) {
        const multipliers = {
            xs: 0.34,
            sm: 0.48,
            md: 0.66,
            full: 1,
        };
        let multiplier = multipliers[tier] || multipliers.sm;

        if (!isPaid && !isFiller && tier !== 'full') {
            multiplier *= 0.82;
        }

        return multiplier;
    }

    function resolveMobileSizeTier(card, adW, adH) {
        if (card.dataset.adMobileTier) {
            return card.dataset.adMobileTier;
        }

        const maxDim = Math.max(adW, adH);
        const area = adW * adH;

        if (adW >= 900 || card.classList.contains('ad-banner') || card.classList.contains('ad-hero')) {
            return 'full';
        }
        if (maxDim <= 320 || area <= 70000) {
            return 'xs';
        }
        if (Math.abs(adW - adH) <= 40 && maxDim <= 520) {
            return 'xs';
        }
        if (maxDim <= 520 || area <= 260000) {
            return 'sm';
        }

        return 'md';
    }

    function applyMobileCardScale(card, gridWidth) {
        const adW = parseInt(card.dataset.adW || '0', 10);
        const adH = parseInt(card.dataset.adH || '0', 10);
        const isFiller = card.hasAttribute('data-filler');
        const isPaid = card.dataset.adIsPaid === '1';
        const tier = resolveMobileSizeTier(card, adW, adH);
        const isFullWidth = tier === 'full';

        card.classList.remove('ad-mobile-tier-xs', 'ad-mobile-tier-sm', 'ad-mobile-tier-md', 'ad-mobile-tier-full');
        card.classList.add('ad-mobile-tier-' + tier);

        if (isFullWidth || adW <= 0) {
            card.style.removeProperty('--ad-mobile-w');
            card.style.removeProperty('--ad-mobile-scale');
            return;
        }

        const isSquare = card.classList.contains('ad-square');
        if (isSquare && adW >= 380 && adW <= 520 && Math.abs(adW - adH) <= 40) {
            const gap = 10;
            const twoColWidth = Math.max(140, Math.floor((gridWidth - gap) / 2));
            card.style.setProperty('--ad-mobile-w', twoColWidth + 'px');
            card.style.setProperty('--ad-mobile-scale', String(twoColWidth / adW));
            return;
        }

        const viewportRatio = Math.min(1, gridWidth / 390);
        const tierMultiplier = getMobileTierMultiplier(tier, isPaid, isFiller);
        const fillerMultiplier = isFiller ? 0.92 : 1;
        const widthCap = tier === 'xs'
            ? gridWidth * 0.42
            : (tier === 'sm' ? gridWidth * 0.58 : gridWidth * 0.78);
        const minWidth = tier === 'xs' ? 88 : (tier === 'sm' ? 118 : 148);
        let mobileW = Math.round(adW * tierMultiplier * viewportRatio * fillerMultiplier);

        mobileW = Math.max(minWidth, Math.min(widthCap, mobileW));
        card.style.setProperty('--ad-mobile-w', mobileW + 'px');
        card.style.setProperty('--ad-mobile-scale', String(tierMultiplier));
    }

    function measureFlexCards(grid) {
        const gridRect = grid.getBoundingClientRect();
        const placed = [];

        grid.querySelectorAll('.ad-card:not([data-filler])').forEach(function (card) {
            const rect = card.getBoundingClientRect();
            const left = Math.round(rect.left - gridRect.left + grid.scrollLeft);
            const top = Math.round(rect.top - gridRect.top + grid.scrollTop);
            const w = Math.round(rect.width);
            const h = Math.round(rect.height);

            placed.push({
                el: card,
                left: left,
                top: top,
                right: left + w,
                bottom: top + h,
                w: w,
                h: h,
            });
        });

        return placed;
    }

    function insertMobileSponsoredCards(grid) {
        const packWidth = grid.clientWidth;
        if (!packWidth) {
            return;
        }

        grid.querySelectorAll('[data-filler]').forEach(function (el) {
            el.remove();
        });

        const availableAds = getAvailableSponsoredItems(grid);
        if (!availableAds.length) {
            grid.style.minHeight = '';
            return;
        }

        const maxMobileSponsored = packWidth >= 400 ? 6 : 4;
        const mainCards = Array.from(grid.querySelectorAll('.ad-card:not([data-filler])'));
        let sponsorIndex = 0;

        mainCards.forEach(function (mainCard, index) {
            if (sponsorIndex >= maxMobileSponsored || sponsorIndex >= availableAds.length) {
                return;
            }

            if ((index + 1) % 2 !== 0) {
                return;
            }

            const item = availableAds[sponsorIndex];
            if (!item || !isFillerItemAvailable(item, grid)) {
                return;
            }

            mainCard.insertAdjacentElement('afterend', createMobileSponsoredCard(item, packWidth, grid));
            sponsorIndex++;
        });

        while (sponsorIndex < availableAds.length && sponsorIndex < maxMobileSponsored) {
            const item = availableAds[sponsorIndex];
            if (!item || !isFillerItemAvailable(item, grid)) {
                sponsorIndex++;
                continue;
            }

            grid.appendChild(createMobileSponsoredCard(item, packWidth, grid));
            sponsorIndex++;
        }

        grid.style.minHeight = '';
    }

    function applyFlexMobileLayout(grid) {
        const gridWidth = grid.clientWidth || (grid.parentElement ? grid.parentElement.clientWidth : 0) || window.innerWidth;

        grid.classList.add('is-mobile-layout');
        grid.style.height = 'auto';
        grid.style.minHeight = '';

        grid.querySelectorAll('[data-filler]').forEach(function (el) {
            el.remove();
        });
        resetUsedFillers();

        grid.querySelectorAll('.ad-card:not([data-filler])').forEach(function (card) {
            card.style.position = '';
            card.style.left = '';
            card.style.top = '';
            card.style.width = '';
            card.style.height = '';
            card.style.zIndex = '';
            card.dataset.adsPacked = '0';

            applyMobileCardScale(card, gridWidth);

            const imgBox = card.querySelector('.ad-image');
            if (imgBox) {
                imgBox.style.width = '';
                imgBox.style.height = '';
                imgBox.style.minHeight = '';
            }
        });

        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                insertMobileSponsoredCards(grid);
            });
        });
    }

    function getMobileLayoutMetrics(packWidth) {
        const numCols = packWidth >= 400 ? 2 : 1;
        const columnWidth = Math.max(1, Math.floor((packWidth - (GAP * Math.max(0, numCols - 1))) / numCols));
        const scale = Math.min(1, packWidth / 1200);

        return { numCols: numCols, columnWidth: columnWidth, scale: scale };
    }

    function getActiveBlankSizes(packWidth) {
        if (!isStackedLayout()) {
            return BLANK_SIZES;
        }

        const metrics = getMobileLayoutMetrics(packWidth);
        const seen = {};

        return BLANK_SIZES.map(function (size) {
            const w = Math.min(metrics.columnWidth, Math.max(80, Math.round(Number(size.w) * metrics.scale)));
            const h = Math.max(50, Math.round(Number(size.h) * (w / Number(size.w))));

            return {
                size_key: size.size_key,
                name: size.name,
                w: w,
                h: h,
            };
        }).filter(function (size) {
            const key = size.w + 'x' + size.h;
            if (seen[key]) {
                return false;
            }
            seen[key] = true;
            return size.w > 0 && size.h > 0;
        }).sort(function (a, b) {
            return (Number(b.w) * Number(b.h)) - (Number(a.w) * Number(a.h));
        });
    }

    function getPackedCardDims(card, packWidth) {
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

        return { w: cardW, h: cardH };
    }

    function scaleSizeForMobile(width, height, packWidth) {
        if (!isStackedLayout()) {
            return { w: Number(width), h: Number(height) };
        }

        const metrics = getMobileLayoutMetrics(packWidth);
        const w = Math.min(metrics.columnWidth, Math.max(80, Math.round(Number(width) * metrics.scale)));
        const h = Math.max(50, Math.round(Number(height) * (w / Number(width))));

        return { w: w, h: h };
    }

    function preserveUsedFillerKeysFromGrid(grid) {
        const layout = grid.closest('[id$="Layout"]') || grid;
        layout.querySelectorAll('[data-filler][data-sponsored-ad-id]').forEach(function (el) {
            instance.usedFillerKeys.add('id:' + el.dataset.sponsoredAdId);
        });
    }

    function readPlacedCard(card) {
        const dims = getDims(card);
        const cardW = parseInt(card.style.width, 10) || dims.w;
        const cardH = parseInt(card.style.height, 10) || dims.h;
        const left = parseInt(card.style.left, 10) || 0;
        const top = parseInt(card.style.top, 10) || 0;

        return {
            el: card,
            left: left,
            right: left + cardW,
            top: top,
            bottom: top + cardH,
            w: cardW,
            h: cardH
        };
    }

    function readFillerSlot(filler) {
        return {
            left: parseInt(filler.style.left, 10) || 0,
            top: parseInt(filler.style.top, 10) || 0,
            w: parseInt(filler.style.width, 10) || 0,
            h: parseInt(filler.style.height, 10) || 0
        };
    }

    function fillerItemKey(item) {
        if (item && item.id) {
            return 'id:' + item.id;
        }
        if (item && item.url) {
            return 'url:' + item.url;
        }
        return 'image:' + (item && item.image ? item.image : '');
    }

    function getMainGridAdKeys(grid) {
        const keys = new Set();
        if (!grid) {
            return keys;
        }

        const layout = grid.closest('[id$="Layout"]') || grid;
        layout.querySelectorAll('.ad-card:not([data-filler])').forEach(function (card) {
            if (card.dataset.adId) {
                keys.add('id:' + card.dataset.adId);
            }
            if (card.dataset.adUrl) {
                keys.add('url:' + card.dataset.adUrl);
            }
        });

        return keys;
    }

    function isFillerItemAvailable(item, grid) {
        const key = fillerItemKey(item);
        if (instance.usedFillerKeys.has(key)) {
            return false;
        }

        const mainGridKeys = getMainGridAdKeys(grid);
        if (item.id && mainGridKeys.has('id:' + item.id)) {
            return false;
        }
        if (item.url && mainGridKeys.has('url:' + item.url)) {
            return false;
        }

        return true;
    }

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

    function buildFillerCard(width, height, item, options) {
        options = options || {};
        const isGapFill = !!options.gapFill;
        const isMobileInline = !!options.mobileInline;

        const card = document.createElement('article');
        card.className = 'ad-card filler'
            + (isGapFill ? ' ad-card--gap-fill' : '')
            + (isMobileInline ? ' ad-card--mobile-sponsored' : '');
        card.dataset.filler = '1';
        card.dataset.adW = String(width);
        card.dataset.adH = String(height);
        card.style.setProperty('--ad-w', String(width));
        card.style.setProperty('--ad-h', String(height));
        card.style.setProperty('--ad-image-h', String(Math.max(1, height - (isGapFill || isMobileInline ? 0 : 34))));

        if (!isMobileInline) {
            card.style.width = width + 'px';
            card.style.height = height + 'px';
        }

        if (Math.abs(width - height) <= 40 && width >= 380 && width <= 520) {
            card.classList.add('ad-square');
        }

        if (item.sizeKey) {
            card.dataset.sizeKey = item.sizeKey;
        }

        const imageHtml = item.image
            ? '<img src="' + item.image + '" alt="' + (item.title || item.label || 'Sponsored') + '" loading="lazy" onerror="this.style.display=\'none\'; this.parentElement.innerHTML=\'<div class=&quot;filler-placeholder&quot;></div>\';">'
            : '<div class="filler-placeholder"></div>';

        const imageBlock = imageHtml;

        if (isGapFill || isMobileInline) {
            card.innerHTML =
                '<span class="sponsored-gap-badge">Sponsored</span>' +
                '<div class="ad-image">' + imageBlock + '</div>';
        } else {
            const adTitleHtml = item.title
                ? '<span class="filler-title">' + item.title + '</span>'
                : '';

            card.innerHTML =
                '<span class="filler-label">' + (item.label || 'Sponsored') + '</span>' +
                adTitleHtml +
                '<div class="ad-image">' + imageBlock + '</div>';
        }

        if (typeof window.soilnwaterApplyAdModalTrigger === 'function' && (item.id || item.url)) {
            window.soilnwaterApplyAdModalTrigger(card, item, width, height);
        }

        return card;
    }

    function createMobileSponsoredCard(item, gridWidth, grid) {
        const adW = Number(item.w) || 458;
        const adH = Number(item.h) || 229;

        instance.usedFillerKeys.add(fillerItemKey(item));

        const card = buildFillerCard(adW, adH, {
            label: item.label || 'Sponsored',
            image: item.image,
            url: item.url,
            title: item.title,
            sizeKey: item.size_key || item.sizeKey || null,
        }, { mobileInline: true });

        if (item.id) {
            card.dataset.sponsoredAdId = String(item.id);
        }

        card.dataset.adMobileTier = resolveMobileSizeTier(card, adW, adH);
        applyMobileCardScale(card, gridWidth);

        return card;
    }

    function mountFillerCard(grid, left, top, width, height, card) {
        card.style.position = 'absolute';
        card.style.width = width + 'px';
        card.style.height = height + 'px';
        card.style.left = left + 'px';
        card.style.top = top + 'px';

        if (isStackedLayout()) {
            card.dataset.gapFiller = '1';
        }

        grid.appendChild(card);
        return { left: left, top: top, w: width, h: height };
    }

    function findBlankSizeMeta(width, height, sizeList) {
        const list = sizeList || BLANK_SIZES;

        return list.find(function (size) {
            return Number(size.w) === Number(width) && Number(size.h) === Number(height);
        }) || null;
    }

    function pickRandomStaticImage() {
        const images = instance.staticImages || [];
        if (!images.length) {
            return null;
        }

        return images[Math.floor(Math.random() * images.length)];
    }

    function mountFillerWithItem(grid, left, top, width, height, item, options) {
        options = options || {};
        if (!options.skipUsedKey) {
            instance.usedFillerKeys.add(fillerItemKey(item));
        }
        const gapFill = options.gapFill !== undefined ? !!options.gapFill : true;
        const card = buildFillerCard(width, height, {
            label: item.label || 'Sponsored',
            image: item.image,
            url: item.url,
            title: item.title,
            sizeKey: item.size_key || item.sizeKey || null,
        }, { gapFill: gapFill });
        if (item.id) {
            card.dataset.sponsoredAdId = String(item.id);
        }
        return mountFillerCard(grid, left, top, width, height, card);
    }

    function mountFlexGapFiller(grid, left, top, width, height, item) {
        return mountFillerWithItem(grid, left, top, width, height, item, {
            gapFill: true,
            skipUsedKey: true,
        });
    }

    function mountBlankFiller(grid, left, top, width, height, sizeMeta) {
        if (isStackedLayout()) {
            return mountFillerCard(
                grid,
                left,
                top,
                Number(width),
                Number(height),
                buildFillerCard(Number(width), Number(height), {
                    label: 'Sponsored',
                    image: pickRandomStaticImage(),
                    url: null,
                    title: null,
                    sizeKey: (sizeMeta && sizeMeta.size_key) || 'mobile_gap',
                }, { gapFill: true })
            );
        }

        sizeMeta = sizeMeta || findBlankSizeMeta(width, height);
        const fillW = Number(width);
        const fillH = Number(height);

        if (!fillW || !fillH) {
            return null;
        }

        return mountFillerCard(
            grid,
            left,
            top,
            fillW,
            fillH,
            buildFillerCard(fillW, fillH, {
                label: 'Sponsored',
                image: pickRandomStaticImage(),
                url: null,
                title: null,
                sizeKey: (sizeMeta && sizeMeta.size_key) || 'desktop_gap',
            }, { gapFill: true })
        );
    }

    function gapKey(left, top, width, height) {
        return left + ':' + top + ':' + width + ':' + height;
    }

    function getAvailableSponsoredItems(grid) {
        return FILLER_POOL.filter(function (item) {
            return item.image && isFillerItemAvailable(item, grid);
        });
    }

    function getFlexSponsoredItems(grid) {
        const mainGridKeys = getMainGridAdKeys(grid);

        return FILLER_POOL.filter(function (item) {
            if (!item.image) {
                return false;
            }
            if (item.id && mainGridKeys.has('id:' + item.id)) {
                return false;
            }
            if (item.url && mainGridKeys.has('url:' + item.url)) {
                return false;
            }

            return true;
        });
    }

    function collectGapCandidates(placedAds, packWidth, contentHeight, obstacles, usedGapKeys, sizeList) {
        const candidates = [];
        const bottomLimit = Math.max(0, contentHeight - 40);

        if (bottomLimit <= 0 || packWidth < 200 || !placedAds.length) {
            return candidates;
        }

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

        for (let yi = 0; yi < uniqY.length; yi++) {
            const top = uniqY[yi];
            if (top >= bottomLimit) continue;

            for (let xi = 0; xi < uniqX.length; xi++) {
                const left = uniqX[xi];

                let bestSize = null;

                for (let si = 0; si < sizeList.length; si++) {
                    const size = sizeList[si];
                    if (left + size.w > packWidth) continue;
                    if (top + size.h > bottomLimit) continue;
                    if (!isFree(obstacles, left, top, size.w, size.h, packWidth)) continue;
                    if (!hasRealAdAfter({ top: top, h: size.h }, placedAds)) continue;

                    const key = gapKey(left, top, size.w, size.h);
                    if (usedGapKeys[key]) continue;

                    if (!bestSize || (size.w * size.h) > (bestSize.w * bestSize.h)) {
                        bestSize = size;
                    }
                }

                if (bestSize) {
                    candidates.push({
                        left: left,
                        top: top,
                        w: bestSize.w,
                        h: bestSize.h,
                        key: gapKey(left, top, bestSize.w, bestSize.h)
                    });
                }
            }
        }

        candidates.sort(function (a, b) {
            return a.top - b.top || a.left - b.left;
        });

        return candidates;
    }

    function measureFreeRect(obstacles, left, top, packWidth, bottomLimit) {
        let maxW = 0;

        for (let w = packWidth - left; w >= 60; w--) {
            if (isFree(obstacles, left, top, w, 40, packWidth)) {
                maxW = w;
                break;
            }
        }

        if (!maxW) {
            return null;
        }

        let maxH = 0;

        for (let h = bottomLimit - top; h >= 40; h--) {
            if (isFree(obstacles, left, top, maxW, h, packWidth)) {
                maxH = h;
                break;
            }
        }

        if (!maxH) {
            return null;
        }

        return { w: maxW, h: maxH };
    }

    function collectMobileGapCandidates(placedAds, packWidth, contentHeight, obstacles, usedGapKeys) {
        const candidates = [];
        const bottomLimit = Math.max(0, contentHeight - 40);

        if (bottomLimit <= 0 || packWidth < 200 || !placedAds.length) {
            return candidates;
        }

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

        for (let yi = 0; yi < uniqY.length; yi++) {
            const top = uniqY[yi];
            if (top >= bottomLimit) {
                continue;
            }

            for (let xi = 0; xi < uniqX.length; xi++) {
                const left = uniqX[xi];
                const rect = measureFreeRect(obstacles, left, top, packWidth, bottomLimit);

                if (!rect || rect.w < 60 || rect.h < 40) {
                    continue;
                }

                if (!hasRealAdAfter({ top: top, h: rect.h }, placedAds)) {
                    continue;
                }

                const key = gapKey(left, top, rect.w, rect.h);
                if (usedGapKeys[key]) {
                    continue;
                }

                candidates.push({
                    left: left,
                    top: top,
                    w: rect.w,
                    h: rect.h,
                    key: key,
                });
            }
        }

        candidates.sort(function (a, b) {
            return (b.w * b.h) - (a.w * a.h) || a.top - b.top || a.left - b.left;
        });

        return candidates;
    }

    function collectArbitraryGapCandidates(placedAds, packWidth, contentHeight, obstacles, usedGapKeys, minWidth, minHeight) {
        const candidates = [];
        const bottomLimit = Math.max(0, contentHeight);
        minWidth = minWidth || 100;
        minHeight = minHeight || 80;

        if (bottomLimit <= 0 || packWidth < 200 || !placedAds.length) {
            return candidates;
        }

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

        for (let yi = 0; yi < uniqY.length; yi++) {
            const top = uniqY[yi];
            if (top >= bottomLimit) {
                continue;
            }

            for (let xi = 0; xi < uniqX.length; xi++) {
                const left = uniqX[xi];
                const rect = measureFreeRect(obstacles, left, top, packWidth, bottomLimit);

                if (!rect || rect.w < minWidth || rect.h < minHeight) {
                    continue;
                }

                const key = gapKey(left, top, rect.w, rect.h);
                if (usedGapKeys[key]) {
                    continue;
                }

                candidates.push({
                    left: left,
                    top: top,
                    w: rect.w,
                    h: rect.h,
                    key: key,
                });
            }
        }

        candidates.sort(function (a, b) {
            return (b.w * b.h) - (a.w * a.h) || a.top - b.top || a.left - b.left;
        });

        return candidates;
    }

    function pickFlexSponsoredItem(grid, index) {
        const available = getFlexSponsoredItems(grid);
        if (available.length) {
            return available[index % available.length];
        }

        const staticImg = pickRandomStaticImage();
        if (!staticImg) {
            return null;
        }

        return {
            label: 'Sponsored',
            image: staticImg,
            url: null,
            title: null,
        };
    }

    function fillArbitraryGaps(grid, placedAds, packWidth, contentHeight, obstacles, usedGapKeys) {
        const fillers = [];
        const minGapW = 100;
        const minGapH = 80;
        let flexIndex = 0;
        const maxFlexFillers = Math.max(12, getFlexSponsoredItems(grid).length * 3, (instance.staticImages || []).length * 2);

        while (flexIndex < maxFlexFillers) {
            const candidates = collectArbitraryGapCandidates(
                placedAds,
                packWidth,
                contentHeight,
                obstacles,
                usedGapKeys,
                minGapW,
                minGapH
            );

            if (!candidates.length) {
                break;
            }

            const gap = candidates[0];
            const item = pickFlexSponsoredItem(grid, flexIndex);
            if (!item || !item.image) {
                break;
            }

            const slot = mountFlexGapFiller(grid, gap.left, gap.top, gap.w, gap.h, item);
            fillers.push(slot);
            obstacles.push(toRect(slot));
            usedGapKeys[gap.key] = true;
            flexIndex++;
        }

        return fillers;
    }

    function createFiller(width, height, grid) {
        const matchingItems = FILLER_POOL.filter(function (item) {
            return Number(item.w) === Number(width)
                && Number(item.h) === Number(height)
                && item.image
                && isFillerItemAvailable(item, grid);
        });

        const item = matchingItems.length
            ? matchingItems[0]
            : { label: 'Sponsored', image: pickRandomStaticImage(), url: null, title: null };

        if (matchingItems.length) {
            instance.usedFillerKeys.add(fillerItemKey(item));
        }

        return buildFillerCard(width, height, item);
    }

    function mountFiller(grid, left, top, width, height) {
        const filler = createFiller(width, height, grid);
        return mountFillerCard(grid, left, top, width, height, filler);
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
            const titleOffset = card.hasAttribute('data-filler') ? FILLER_TITLE_H : TITLE_H;
            imgBox.style.height = titleOffset > 0 ? ('calc(100% - ' + titleOffset + 'px)') : '100%';
            imgBox.style.minHeight = '0';
        }

        placed.push({
            el: card,
            left: pos.left,
            right: pos.left + cardW,
            top: pos.top,
            bottom: pos.top + cardH
        });
        card.dataset.adsPacked = '1';
    }

    function hasRealAdAfter(slot, placedAds) {
        if (isStackedLayout()) {
            return true;
        }

        const slotBottom = slot.top + (slot.h || (slot.bottom - slot.top));
        for (let i = 0; i < placedAds.length; i++) {
            if (placedAds[i].top >= slotBottom - 1) {
                return true;
            }
        }
        return false;
    }

    function addSponsoredGaps(grid, placedAds, packWidth, contentHeight, obstacles, usedGapKeys, activeBlankSizes) {
        const fillers = [];
        const blankSizes = activeBlankSizes || BLANK_SIZES;
        const bottomLimit = Math.max(0, contentHeight - 40);
        const useMobileGaps = isStackedLayout();

        if (bottomLimit <= 0 || packWidth < 200 || !placedAds.length) {
            return fillers;
        }

        const availableAds = getAvailableSponsoredItems(grid);

        if (useMobileGaps) {
            return fillers;
        }

        availableAds.forEach(function (item) {
            const adSize = scaleSizeForMobile(item.w, item.h, packWidth);
            const gapCandidates = collectGapCandidates(
                placedAds,
                packWidth,
                contentHeight,
                obstacles,
                usedGapKeys,
                [adSize]
            );

            if (!gapCandidates.length) {
                return;
            }

            const gap = gapCandidates[0];
            const slot = mountFillerWithItem(grid, gap.left, gap.top, gap.w, gap.h, item);
            fillers.push(slot);
            obstacles.push(toRect(slot));
            usedGapKeys[gap.key] = true;
        });

        while (blankSizes.length) {
            const gapCandidates = collectGapCandidates(
                placedAds,
                packWidth,
                contentHeight,
                obstacles,
                usedGapKeys,
                blankSizes
            );

            if (!gapCandidates.length) {
                break;
            }

            let placedBlank = false;
            for (let i = 0; i < gapCandidates.length; i++) {
                const gap = gapCandidates[i];
                if (usedGapKeys[gap.key]) {
                    continue;
                }
                if (!isFree(obstacles, gap.left, gap.top, gap.w, gap.h, packWidth)) {
                    continue;
                }

                const sizeMeta = findBlankSizeMeta(gap.w, gap.h, blankSizes);
                if (!sizeMeta) {
                    continue;
                }

                const slot = mountBlankFiller(grid, gap.left, gap.top, gap.w, gap.h, sizeMeta);
                if (!slot) {
                    continue;
                }

                fillers.push(slot);
                obstacles.push(toRect(slot));
                usedGapKeys[gap.key] = true;
                placedBlank = true;
                break;
            }

            if (!placedBlank) {
                break;
            }
        }

        if (!useMobileGaps) {
            const arbitraryFillers = fillArbitraryGaps(
                grid,
                placedAds,
                packWidth,
                contentHeight,
                obstacles,
                usedGapKeys
            );
            fillers.push.apply(fillers, arbitraryFillers);
        }

        return fillers;
    }

    function fillSponsoredGaps(grid, placedAds, packWidth, contentHeight, pinnedFillers) {
        const activeBlankSizes = getActiveBlankSizes(packWidth);
        const obstacles = placedAds
            .map(function (item) {
                return { left: item.left, right: item.right, top: item.top, bottom: item.bottom };
            });

        const usedGapKeys = {};
        const pinnedSlots = (pinnedFillers || []).filter(function (slot) {
            return hasRealAdAfter(slot, placedAds);
        });

        pinnedSlots.forEach(function (slot) {
            obstacles.push(toRect(slot));
            usedGapKeys[gapKey(slot.left, slot.top, slot.w, slot.h)] = true;
        });

        const fillers = addSponsoredGaps(grid, placedAds, packWidth, contentHeight, obstacles, usedGapKeys, activeBlankSizes);

        pinnedSlots.forEach(function (slot) {
            const alreadyFilled = fillers.some(function (fillerSlot) {
                return Math.abs(fillerSlot.left - slot.left) < 1 && Math.abs(fillerSlot.top - slot.top) < 1;
            });

            if (alreadyFilled) {
                return;
            }

            const sizeMeta = findBlankSizeMeta(slot.w, slot.h, activeBlankSizes);
            if (!sizeMeta) {
                return;
            }

            const fillerSlot = mountBlankFiller(grid, slot.left, slot.top, slot.w, slot.h, sizeMeta);
            if (!fillerSlot) {
                return;
            }

            fillers.push(fillerSlot);
            obstacles.push(toRect(fillerSlot));
        });

        return fillers;
    }

    // Always re-pack every card currently in this grid so append never stacks on top of old ads.
    function packGrid(grid, keepPinnedFillers) {
        const packWidth = grid.clientWidth;
        if (!packWidth) return;

        if (isStackedLayout()) {
            applyFlexMobileLayout(grid);
            return;
        }

        grid.classList.remove('is-mobile-layout');
        const state = getGridState(grid);

        if (keepPinnedFillers) {
            preserveUsedFillerKeysFromGrid(grid);

            const placed = [];
            const obstacles = [];
            const existingFillerSlots = [];
            const activeBlankSizes = getActiveBlankSizes(packWidth);

            grid.querySelectorAll('.ad-card:not([data-filler])').forEach(function (card) {
                if (card.dataset.adsPacked !== '1') {
                    return;
                }

                const packed = readPlacedCard(card);
                placed.push(packed);
                obstacles.push({
                    left: packed.left,
                    right: packed.right,
                    top: packed.top,
                    bottom: packed.bottom
                });
            });

            grid.querySelectorAll('[data-filler]').forEach(function (filler) {
                const slot = readFillerSlot(filler);
                existingFillerSlots.push(slot);
                obstacles.push(toRect(slot));
            });

            const newCards = Array.from(grid.querySelectorAll('.ad-card:not([data-filler])'))
                .filter(function (card) { return card.dataset.adsPacked !== '1'; });

            newCards.forEach(function (card) {
                const sized = getPackedCardDims(card, packWidth);
                const pos = findPlace(obstacles, sized.w, sized.h, packWidth);
                placeCard(card, pos, sized.w, sized.h, placed);
                obstacles.push({
                    left: pos.left,
                    right: pos.left + sized.w,
                    top: pos.top,
                    bottom: pos.top + sized.h
                });
            });

            const contentHeight = placed.length
                ? Math.max.apply(null, placed.map(function (item) { return item.bottom; }))
                : 0;

            const usedGapKeys = {};
            existingFillerSlots.forEach(function (slot) {
                usedGapKeys[gapKey(slot.left, slot.top, slot.w, slot.h)] = true;
            });

            const newFillers = addSponsoredGaps(grid, placed, packWidth, contentHeight, obstacles, usedGapKeys, activeBlankSizes);
            state.fillerPositions = existingFillerSlots.concat(newFillers);

            let maxBottom = contentHeight;
            grid.querySelectorAll('.ad-card').forEach(function (card) {
                const bottom = card.offsetTop + card.offsetHeight;
                if (bottom > maxBottom) maxBottom = bottom;
            });

            state.contentHeight = maxBottom;
            grid.style.height = maxBottom + 'px';
            return;
        }

        const cards = Array.from(grid.querySelectorAll('.ad-card:not([data-filler])'));

        grid.querySelectorAll('[data-filler]').forEach(function (el) { el.remove(); });
        resetUsedFillers();
        cards.forEach(function (card) {
            card.dataset.adsPacked = '0';
        });

        function packCards(reservedFillers) {
            const placed = [];
            const obstacles = reservedFillers.map(toRect);

            cards.forEach(function (card) {
                const sized = getPackedCardDims(card, packWidth);
                const pos = findPlace(obstacles, sized.w, sized.h, packWidth);
                placeCard(card, pos, sized.w, sized.h, placed);
                obstacles.push({
                    left: pos.left,
                    right: pos.left + sized.w,
                    top: pos.top,
                    bottom: pos.top + sized.h
                });
            });

            return placed;
        }

        let placed = packCards([]);

        const contentHeight = placed.length
            ? Math.max.apply(null, placed.map(function (item) { return item.bottom; }))
            : 0;

        state.fillerPositions = fillSponsoredGaps(
            grid,
            placed,
            packWidth,
            contentHeight,
            []
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
            instance.usedFillerKeys = new Set();

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
                    window.renderAdsMarketCards(entry.gridId, entry.fillerPool, {
                        resetFillers: true,
                        blankSizes: entry.blankSizes || [],
                        staticImages: entry.staticImages || []
                    });
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
        window.renderAdsMarketCards(@json($gridId), @json($sponsoredFillers), {
            blankSizes: @json($sponsoredBlankSizes),
            staticImages: @json($staticSponsoredImages)
        });
    }
});
</script>
@endif
