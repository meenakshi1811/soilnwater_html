@php
    $hubSections = $hubSections ?? \App\Support\CommunityContentTaxonomy::hubSections();
    $activeHub = $activeHub ?? null;
    $sectionRoute = $sectionRoute ?? 'community.index';
    $sectionRouteParams = $sectionRouteParams ?? [];
    $featuredHubKey = ($activeHub && isset($hubSections[$activeHub])) ? $activeHub : array_key_first($hubSections);
@endphp

<div class="community-hub-sections-bar">
    <nav class="community-hub-nav community-hub-nav--sections-only" aria-label="Community sections">
        <div class="community-hub-nav__intro">
            <h2 class="community-hub-nav__title">Browse by Sections</h2>
        </div>

        <div class="community-hub-sections">
            @foreach ($hubSections as $hubKey => $hub)
                <a
                    href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $hubKey])) }}"
                    class="community-hub-section-card {{ $featuredHubKey === $hubKey ? 'is-active' : '' }}"
                    style="--hub-accent: {{ $hub['accent'] }};"
                >
                    <span class="community-hub-section-card__icon" aria-hidden="true">
                        <i class="fa-solid {{ $hub['icon'] }}"></i>
                    </span>
                    <span class="community-hub-section-card__copy">
                        <span class="community-hub-section-card__label">{{ $hub['label'] }}</span>
                        <span class="community-hub-section-card__tagline">{{ $hub['tagline'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </nav>
</div>
