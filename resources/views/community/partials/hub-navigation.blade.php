@php
    $hubSections = $hubSections ?? \App\Support\CommunityContentTaxonomy::hubSections();
    $types = $types ?? \App\Support\CommunityContentTaxonomy::formTypes();
    $activeHub = $activeHub ?? null;
    $activeType = $activeType ?? '';
    $activeCategory = $activeCategory ?? '';
    $engagement = $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
    $sectionRoute = $sectionRoute ?? 'community.index';
    $sectionRouteParams = $sectionRouteParams ?? [];
    $pillColors = \App\Support\CommunityContentTaxonomy::pillColors();
    $pillFallback = \App\Support\CommunityContentTaxonomy::pillColorFallback();
    $currentHub = ($activeHub && isset($hubSections[$activeHub])) ? $hubSections[$activeHub] : null;
    $currentType = ($activeType && isset($types[$activeType])) ? $types[$activeType] : null;
    $currentTypeColor = $pillColors[$activeType] ?? $pillFallback;
@endphp

<nav class="community-hub-nav" aria-label="Community sections">
    <ol class="community-hub-breadcrumb">
        <li>
            <a href="{{ route($sectionRoute, $sectionRouteParams) }}">All sections</a>
        </li>
        @if ($currentHub)
            <li>
                @if ($currentType)
                    <a href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $activeHub])) }}">
                        {{ $currentHub['label'] }}
                    </a>
                @else
                    <span class="is-current">{{ $currentHub['label'] }}</span>
                @endif
            </li>
        @endif
        @if ($currentType)
            <li>
                @if ($activeCategory)
                    <a href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $activeHub, 'type' => $activeType])) }}">
                        {{ $currentType['label'] }}
                    </a>
                @else
                    <span class="is-current">{{ $currentType['label'] }}</span>
                @endif
            </li>
        @endif
        @if ($activeCategory)
            <li><span class="is-current">{{ $activeCategory }}</span></li>
        @endif
    </ol>

    @if ($currentHub || $currentType)
        <div class="community-hub-sections community-hub-sections--compact" aria-label="Switch main section">
            @foreach ($hubSections as $hubKey => $hub)
                <a
                    href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $hubKey])) }}"
                    class="community-hub-section-card community-hub-section-card--compact {{ $activeHub === $hubKey ? 'is-active' : '' }}"
                    style="--hub-accent: {{ $hub['accent'] }};"
                    title="{{ $hub['label'] }}"
                >
                    <span class="community-hub-section-card__icon" aria-hidden="true">
                        <i class="fa-solid {{ $hub['icon'] }}"></i>
                    </span>
                    <span class="community-hub-section-card__label">{{ $hub['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    @if (! $currentHub && ! $currentType)
        <div class="community-hub-nav__intro">
            <h2 class="community-hub-nav__title">Where would you like to post or explore?</h2>
            <p class="community-hub-nav__subtitle">Choose one of eight main sections, then pick a subsection that fits your content.</p>
        </div>

        <div class="community-hub-sections">
            @foreach ($hubSections as $hubKey => $hub)
                <a
                    href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $hubKey])) }}"
                    class="community-hub-section-card"
                    style="--hub-accent: {{ $hub['accent'] }};"
                >
                    <span class="community-hub-section-card__icon" aria-hidden="true">
                        <i class="fa-solid {{ $hub['icon'] }}"></i>
                    </span>
                    <h3 class="community-hub-section-card__label">{{ $hub['label'] }}</h3>
                    <p class="community-hub-section-card__tagline">{{ $hub['tagline'] }}</p>
                    <span class="community-hub-section-card__count">{{ count($hub['types']) }} subsections</span>
                </a>
            @endforeach
        </div>
    @else
        @if ($currentHub)
            <div class="community-hub-panel" style="--hub-accent: {{ $currentHub['accent'] }};">
                <div class="community-hub-panel__head">
                    <div class="community-hub-panel__head-inner">
                        <span class="community-hub-panel__icon" aria-hidden="true">
                            <i class="fa-solid {{ $currentHub['icon'] }}"></i>
                        </span>
                        <div>
                            <h2 class="community-hub-panel__title">{{ $currentHub['label'] }}</h2>
                            <p class="community-hub-panel__description">{{ $currentHub['description'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="community-hub-panel__body">
                    @if (! $currentType)
                        <p class="community-hub-panel__section-label">Choose a subsection</p>
                        <div class="community-hub-subsections">
                            @foreach ($currentHub['types'] as $typeKey)
                                @continue(! isset($types[$typeKey]))
                                @php $typeColor = $pillColors[$typeKey] ?? $pillFallback; @endphp
                                <div class="community-hub-subsection-card" style="--type-color: {{ $typeColor }};">
                                    <a
                                        href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $activeHub, 'type' => $typeKey])) }}"
                                        class="community-hub-subsection-card__main"
                                    >
                                        <span class="community-hub-subsection-card__label">{{ $types[$typeKey]['label'] }}</span>
                                        <p class="community-hub-subsection-card__text">{{ $types[$typeKey]['description'] }}</p>
                                    </a>
                                    <span class="community-hub-subsection-card__actions">
                                        <a href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $activeHub, 'type' => $typeKey])) }}" class="community-hub-subsection-card__browse">
                                            Browse posts <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                        @auth
                                            <a href="{{ route('community.posts.create', ['type' => $typeKey]) }}" class="community-hub-subsection-card__post">Post here</a>
                                        @endauth
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="community-hub-subsections">
                            @foreach ($currentHub['types'] as $typeKey)
                                @continue(! isset($types[$typeKey]))
                                @php $typeColor = $pillColors[$typeKey] ?? $pillFallback; @endphp
                                <div class="community-hub-subsection-card {{ $activeType === $typeKey ? 'is-active' : '' }}" style="--type-color: {{ $typeColor }};">
                                    <a
                                        href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $activeHub, 'type' => $typeKey])) }}"
                                        class="community-hub-subsection-card__main"
                                    >
                                        <span class="community-hub-subsection-card__label">{{ $types[$typeKey]['label'] }}</span>
                                        <p class="community-hub-subsection-card__text">{{ $types[$typeKey]['description'] }}</p>
                                    </a>
                                    <span class="community-hub-subsection-card__actions">
                                        <a href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $activeHub, 'type' => $typeKey])) }}" class="community-hub-subsection-card__browse">View posts</a>
                                        @auth
                                            <a href="{{ route('community.posts.create', ['type' => $typeKey]) }}" class="community-hub-subsection-card__post">Post here</a>
                                        @endauth
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        @if (! empty($currentType['categories']))
                            <div class="community-hub-categories">
                                <div class="community-hub-categories__head">
                                    <h3 class="community-hub-categories__title">Filter by category</h3>
                                    <p class="community-hub-categories__hint">Tap a category to narrow posts</p>
                                </div>
                                <div class="community-hub-category-scroll">
                                    <a
                                        href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $activeHub, 'type' => $activeType])) }}"
                                        class="community-hub-category-link {{ $activeCategory ? '' : 'is-active' }}"
                                        style="--type-color: {{ $currentTypeColor }};"
                                    >All categories</a>
                                    @foreach ($currentType['categories'] as $categoryName)
                                        @php
                                            $isSubscribed = auth()->check() && collect($engagement['subscribed_categories'] ?? [])->contains(
                                                fn (array $subscription): bool => ($subscription['content_type'] ?? null) === $activeType
                                                    && ($subscription['category'] ?? null) === $categoryName
                                            );
                                        @endphp
                                        <span class="community-hub-category-chip {{ $activeCategory === $categoryName ? 'is-active' : '' }}" style="--type-color: {{ $currentTypeColor }};">
                                            <a
                                                href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $activeHub, 'type' => $activeType, 'category' => $categoryName])) }}"
                                                class="community-hub-category-link {{ $activeCategory === $categoryName ? 'is-active' : '' }}"
                                            >{{ $categoryName }}</a>
                                            @auth
                                                <button
                                                    type="button"
                                                    class="btn btn-sm {{ $isSubscribed ? 'btn-success' : 'btn-outline-success' }} js-community-subscribe-category {{ $isSubscribed ? 'is-subscribed' : '' }}"
                                                    data-url="{{ route('community.subscriptions.category.toggle') }}"
                                                    data-content-type="{{ $activeType }}"
                                                    data-category="{{ $categoryName }}"
                                                    data-label-subscribed="Subscribed"
                                                    data-label-unsubscribed="Subscribe"
                                                >{{ $isSubscribed ? 'Subscribed' : 'Subscribe' }}</button>
                                            @endauth
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        @if (! $currentHub && $currentType)
            <div class="community-hub-panel" style="--hub-accent: {{ $currentTypeColor }};">
                <div class="community-hub-panel__head">
                    <div class="community-hub-panel__head-inner">
                        <span class="community-hub-panel__icon" aria-hidden="true"><i class="fa-solid fa-folder-open"></i></span>
                        <div>
                            <h2 class="community-hub-panel__title">{{ $currentType['label'] }}</h2>
                            <p class="community-hub-panel__description">{{ $currentType['description'] }}</p>
                        </div>
                    </div>
                </div>
                @if (! empty($currentType['categories']))
                    <div class="community-hub-panel__body">
                        <div class="community-hub-category-scroll">
                            <a
                                href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['type' => $activeType])) }}"
                                class="community-hub-category-link {{ $activeCategory ? '' : 'is-active' }}"
                                style="--type-color: {{ $currentTypeColor }};"
                            >All categories</a>
                            @foreach ($currentType['categories'] as $categoryName)
                                <a
                                    href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['type' => $activeType, 'category' => $categoryName])) }}"
                                    class="community-hub-category-link {{ $activeCategory === $categoryName ? 'is-active' : '' }}"
                                    style="--type-color: {{ $currentTypeColor }};"
                                >{{ $categoryName }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif
</nav>
