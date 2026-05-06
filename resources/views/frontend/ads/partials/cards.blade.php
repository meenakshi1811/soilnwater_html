<style>
.ads-layout {
    width: 100%;
}

.normal-ads-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    align-items: flex-start;
    margin-bottom: 18px;
}

.ad-card {
    background: #fff;
    border-radius: 18px;
    padding: 14px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2eaf5;
}

.ad-card.db-sized {
    width: calc(var(--ad-w) * 1px);
}

.ad-card.db-sized .ad-image {
    width: calc(var(--ad-w) * 1px);
    height: calc(var(--ad-h) * 1px);
    aspect-ratio: auto;
}

 .ad-card h3 {
    display: none;
}

 .ad-card.filler h3 {
    display: block;
    font-size: 13px;
    color: #7f8da0;
    margin-bottom: 8px;
}

.ad-card:not(.filler) {
    padding: 0;
}

.ad-image {
    width: 100%;
    overflow: hidden;
    border-radius: 14px;
    background: #f3f6fb;
}

.ad-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.portrait { width: 265px; }
.portrait .ad-image { aspect-ratio: 229 / 458; }
.landscape { width: 520px; }
.landscape .ad-image { aspect-ratio: 458 / 229; }
.square { width: 520px; }
.square .ad-image { aspect-ratio: 458 / 458; }
.banner, .hero { width: 100%; margin-bottom: 18px; }
.banner .ad-image { aspect-ratio: 1191 / 229; }
.hero .ad-image { aspect-ratio: 1191 / 458; }

.ad-card.db-sized.banner,
.ad-card.db-sized.hero {
    width: calc(var(--ad-w) * 1px);
}

@media (max-width: 992px) {
    .portrait, .landscape, .square { width: calc(50% - 9px); }
}

@media (max-width: 576px) {
    .portrait, .landscape, .square, .banner, .hero, .ad-card.db-sized { width: 100%; }
    .ad-card { padding: 12px; }
    .ad-card.db-sized .ad-image { width: 100%; height: auto; aspect-ratio: var(--ad-w) / var(--ad-h); }
}
</style>

@php
    $adSizes = \App\Support\AdSizes::all();
@endphp

<div id="adsSource" class="d-none">
    @forelse ($ads as $ad)
        @php
            $rawSizeType = (string) ($ad->size_type ?? '');
            $normalizedSizeType = \Illuminate\Support\Str::of($rawSizeType)->lower()->replace([' ', '-'], '_')->value();

            $sizeConfig = $adSizes[$rawSizeType]
                ?? $adSizes[$normalizedSizeType]
                ?? collect($adSizes)->first(function ($config, $key) use ($normalizedSizeType) {
                    return \Illuminate\Support\Str::of($key)->lower()->replace([' ', '-'], '_')->value() === $normalizedSizeType;
                });

            $adWidth = (int) ($sizeConfig['w'] ?? 458);
            $adHeight = (int) ($sizeConfig['h'] ?? 229);

            $shapeClass = 'landscape';
            if ($adWidth >= 1100 && $adHeight >= 420) {
                $shapeClass = 'hero';
            } elseif ($adWidth >= 1100) {
                $shapeClass = 'banner';
            } elseif ($adWidth <= 280 && $adHeight >= 400) {
                $shapeClass = 'portrait';
            } elseif (abs($adWidth - $adHeight) <= 40) {
                $shapeClass = 'square';
            }
        @endphp

        <article
            class="ad-card db-sized {{ $shapeClass }} js-ad-modal-trigger"
            style="--ad-w: {{ $adWidth }}; --ad-h: {{ $adHeight }};"
            role="button"
            tabindex="0"
            data-ad-title="{{ $ad->title }}"
            data-ad-meta="{{ $ad->category?->name ?? 'Uncategorized' }}"
            data-ad-image="{{ $ad->final_image ? asset($ad->final_image) : '' }}"
            data-ad-url="{{ route('frontend.ads.show', $ad) }}"
            data-ad-id="{{ $ad->id }}"
        >
            <h3>{{ $ad->title }}</h3>
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

<div id="adsLayout" class="ads-layout"></div>

<script>
(function () {
    const GAP = 18;
    const FILLER_POOL = [
        { type: 'portrait', width: 265, label: 'Sponsored', image: 'https://picsum.photos/229/458?random=81' },
        { type: 'landscape', width: 520, label: 'Sponsored', image: 'https://picsum.photos/458/229?random=82' },
        { type: 'square', width: 520, label: 'Sponsored', image: 'https://picsum.photos/458/458?random=83' }
    ];
    let fillerIdx = 0;

    function makeFillerCard() {
        const selected = FILLER_POOL[fillerIdx % FILLER_POOL.length];
        fillerIdx += 1;
        const wrap = document.createElement('article');
        wrap.className = `ad-card filler ${selected.type}`;
        wrap.innerHTML = `<h3>${selected.label}</h3><div class="ad-image"><img src="${selected.image}" alt="Sponsored" loading="lazy"></div>`;
        return { el: wrap, width: selected.width };
    }


    function makeFillerBlock(width, height) {
        const selected = FILLER_POOL[fillerIdx % FILLER_POOL.length];
        fillerIdx += 1;
        const wrap = document.createElement('article');
        wrap.className = 'ad-card filler';
        wrap.style.width = `${Math.max(120, Math.floor(width))}px`;
        wrap.innerHTML = `<h3>Sponsored</h3><div class="ad-image" style="aspect-ratio:auto;height:${Math.max(120, Math.floor(height - 22))}px;"><img src="${selected.image}" alt="Sponsored" loading="lazy"></div>`;
        return wrap;
    }

    function fillGridGaps(grid) {
        const containerW = grid.clientWidth;
        if (!containerW) return;

        const $grid = window.jQuery ? window.jQuery(grid) : null;
        const baseCards = Array.from(grid.children)
            .filter((c) => !c.classList.contains('filler'))
            .map((c) => ({
                node: c,
                width: Math.ceil(c.getBoundingClientRect().width),
                height: Math.ceil(c.getBoundingClientRect().height)
            }));
        if (!baseCards.length) return;

        // Rebuild row-by-row so fillers are inserted exactly where blank space appears.
        grid.innerHTML = '';

        const appendFillers = (rowCards) => {
            if (!rowCards.length) return;
            const rowHeight = Math.max(...rowCards.map((r) => r.height));
            const rowRight = Math.max(...rowCards.map((r) => r.node.offsetLeft + r.node.offsetWidth));
            const freeSpace = containerW - rowRight;
            const blockWidth = freeSpace - 2;
            if (blockWidth < 160) return;
            const block = makeFillerBlock(blockWidth, rowHeight);
            if ($grid) {
                $grid.append(block);
            } else {
                grid.appendChild(block);
            }
        };

        let row = [];
        let used = 0;

        baseCards.forEach((card) => {
            const width = card.width;
            const nextUsed = row.length ? used + GAP + width : width;

            if (row.length && nextUsed > containerW) {
                row.forEach((r) => grid.appendChild(r.node));
                appendFillers(row);
                row = [card];
                used = width;
            } else {
                row.push(card);
                used = nextUsed;
            }
        });

        if (row.length) {
            row.forEach((r) => grid.appendChild(r.node));
            appendFillers(row);
        }
    }

    function buildAdsLayout() {
        const source = document.getElementById('adsSource');
        const layout = document.getElementById('adsLayout');
        if (!source || !layout) return;

        layout.innerHTML = '';
        let normalGrid = null;

        const createGrid = () => {
            normalGrid = document.createElement('div');
            normalGrid.className = 'normal-ads-grid';
            layout.appendChild(normalGrid);
        };

        Array.from(source.querySelectorAll('.ad-card')).forEach((card) => {
            const ad = card.cloneNode(true);
            if (ad.classList.contains('banner') || ad.classList.contains('hero')) {
                if (normalGrid) {
                    fillGridGaps(normalGrid);
                    normalGrid = null;
                }
                layout.appendChild(ad);
                return;
            }
            if (!normalGrid) createGrid();
            normalGrid.appendChild(ad);
        });

        if (normalGrid) fillGridGaps(normalGrid);
    }

    document.addEventListener('DOMContentLoaded', function () {
        requestAnimationFrame(function () {
            buildAdsLayout();
            requestAnimationFrame(buildAdsLayout);
        });

        let timer;
        window.addEventListener('resize', function () {
            clearTimeout(timer);
            timer = setTimeout(buildAdsLayout, 200);
        });
    });
})();
</script>
