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
    $featuredHubKey = ($activeHub && isset($hubSections[$activeHub])) ? $activeHub : array_key_first($hubSections);
    $currentHub = $featuredHubKey ? ($hubSections[$featuredHubKey] ?? null) : null;
    $currentType = ($activeType && isset($types[$activeType])) ? $types[$activeType] : null;
    $currentTypeColor = $pillColors[$activeType] ?? $pillFallback;
    $hubAllPostsUrl = route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $featuredHubKey]));
    $isAllPostsView = \App\Support\CommunityContentTaxonomy::isAllPostsListing();
    $hubAllPostsParams = array_filter(array_merge($sectionRouteParams, ['hub' => $featuredHubKey]));
@endphp

<nav class="community-hub-nav" aria-label="Community sections">
    @unless($hideSectionCards ?? false)
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
    @endunless

    @if ($currentHub)
        <div class="community-hub-panel" style="--hub-accent: {{ $currentHub['accent'] }};">
            <div class="community-hub-panel__head">
                <div class="community-hub-panel__head-inner">
                    <span class="community-hub-panel__icon" aria-hidden="true">
                        <i class="fa-solid {{ $currentHub['icon'] }}"></i>
                    </span>
                    <div class="community-hub-panel__copy">
                        <h2 class="community-hub-panel__title">{{ $currentHub['label'] }}</h2>
                        <p class="community-hub-panel__description">{{ $currentHub['description'] }}</p>
                    </div>
                    @if (! $isAllPostsView)
                    <a href="{{ $sectionRoute === 'community.authors.show' ? $hubAllPostsUrl : route('community.all', $hubAllPostsParams) }}" class="community-hub-panel__view-all">
                        View all posts <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    @endif
                </div>
            </div>

            <div class="community-hub-panel__body">
                <div class="community-hub-subsections">
                    @foreach ($currentHub['types'] as $typeKey)
                        @continue(! isset($types[$typeKey]))
                        @php $typeColor = $pillColors[$typeKey] ?? $pillFallback; @endphp
                        <div class="community-hub-subsection-card {{ $activeType === $typeKey ? 'is-active' : '' }}" style="--type-color: {{ $typeColor }};">
                            <span class="community-hub-subsection-card__deco" aria-hidden="true">
                                <i class="fa-solid {{ \App\Support\CommunityContentTaxonomy::typeIcon($typeKey) }}"></i>
                            </span>
                            <a
                                href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $featuredHubKey, 'type' => $typeKey])) }}"
                                class="community-hub-subsection-card__main"
                            >
                                <span class="community-hub-subsection-card__icon" aria-hidden="true">
                                    <i class="fa-solid {{ \App\Support\CommunityContentTaxonomy::typeIcon($typeKey) }}"></i>
                                </span>
                                <span class="community-hub-subsection-card__label">{{ $types[$typeKey]['label'] }}</span>
                                <p class="community-hub-subsection-card__text">{{ $types[$typeKey]['description'] }}</p>
                            </a>
                            <span class="community-hub-subsection-card__actions">
                                <a href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $featuredHubKey, 'type' => $typeKey])) }}" class="community-hub-subsection-card__browse">
                                    Browse posts <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                @auth
                                    <a href="{{ route('community.posts.create', ['type' => $typeKey]) }}" class="community-hub-subsection-card__post">Post here</a>
                                @else
                                    <a href="{{ route('login') }}" class="community-hub-subsection-card__post">Post here</a>
                                @endauth
                            </span>
                        </div>
                    @endforeach
                </div>

                @if ($currentType && ! empty($currentType['categories']))
                    <div class="community-hub-categories">
                        <div class="community-hub-categories__head">
                            <h3 class="community-hub-categories__title">Filter by category</h3>
                            <p class="community-hub-categories__hint">Tap a category to narrow posts</p>
                        </div>
                        <div class="community-hub-category-scroll">
                            <a
                                href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $featuredHubKey, 'type' => $activeType])) }}"
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
                                        href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $featuredHubKey, 'type' => $activeType, 'category' => $categoryName])) }}"
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
            </div>
        </div>
    @endif
</nav>
