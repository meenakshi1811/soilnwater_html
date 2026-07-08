@forelse ($posts as $post)
    @php
        $engagement = $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
        $authorDisplayName = $post->authorDisplayName();
        $authorInitials = $post->authorInitials();
        $authorAvatarUrl = $post->authorAvatarUrl();
        $reportStatus = $post->reportStatus();
        $reportTrustScore = $post->isReportContent() ? $post->reportTrustScore() : null;
        $sectionLabel = $post->typeLabel();
        $categoryLabel = match (true) {
            $post->isMyAreaPost() && filled($post->listingCategoryLabel()) => $post->listingCategoryLabel(),
            $post->isAwarenessPost() && filled($post->awarenessCategoryLabel()) => $post->awarenessCategoryLabel(),
            $post->isBusinessPost() && filled($post->businessCategoryLabel()) => $post->businessCategoryLabel(),
            filled(data_get($post->meta, 'report_type')) => data_get($post->meta, 'report_type', $post->category),
            filled($post->category) => $post->category,
            default => null,
        };
        if ($categoryLabel === $sectionLabel) {
            $categoryLabel = null;
        }
        $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160);
        $locationLabel = $post->location ?? data_get($post->meta, 'location');
        $promotionLabels = $post->adminPromotionLabels();
        $scoreBadges = $post->articleScoreBadges();
        $isSaved = auth()->check() && in_array($post->id, $engagement['saved_post_ids'] ?? [], true);
        $isPoetry = $post->content_type === 'poetry';
        $isBusiness = $post->isBusinessPost();
        $poetryThemes = $isPoetry ? array_slice((array) data_get($post->meta, 'poetry_themes', []), 0, 2) : [];
        $businessThemes = $isBusiness ? array_slice((array) data_get($post->meta, 'business_themes', []), 0, 2) : [];
        $poetryType = $isPoetry ? data_get($post->meta, 'poetry_type') : null;
        $businessContentType = $isBusiness ? data_get($post->meta, 'business_content_type') : null;
        $businessStage = $isBusiness ? data_get($post->meta, 'business_stage') : null;
        $poetryRating = $isPoetry ? $post->averageStarRating() : null;
        $hasPoetryAudio = $isPoetry && $post->poetryAudioUrl();
        $isMyArea = $post->isMyAreaPost();
        $myAreaActivity = $isMyArea ? $post->myAreaActivityType() : null;
        $myAreaStatus = $isMyArea ? data_get($post->meta, 'my_area_status_tracker') : null;
        $myAreaImpact = $isMyArea ? data_get($post->meta, 'my_area_impact_level') : null;
        $myAreaLocation = $isMyArea ? trim(implode(', ', array_filter([
            data_get($post->meta, 'location_locality'),
            data_get($post->meta, 'location_city'),
            data_get($post->meta, 'location_district'),
        ]))) : null;
        $typeColor = \App\Support\CommunityContentTaxonomy::pillColorFor($post->content_type);
    @endphp
    <div class="col">
        <article class="community-post-card h-100 {{ $post->is_highlighted ? 'community-post-card--highlighted' : '' }}" style="--type-color: {{ $typeColor }};">
            <a href="{{ route('community.show', $post) }}" class="community-post-card__media-link" aria-label="Read {{ $post->title }}">
                @if ($post->featuredImageUrl())
                    <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="community-post-card__image" loading="lazy">
                @else
                    <div class="community-post-card__placeholder" aria-hidden="true">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                @endif
                <div class="community-post-card__media-overlay"></div>
                @if ($sectionLabel || $categoryLabel || $reportStatus || $reportTrustScore !== null || $promotionLabels !== [] || $scoreBadges !== [])
                    <div class="community-post-card__badges">
                        @if ($sectionLabel)
                            <span class="community-post-card__badge community-post-card__badge--section">{{ $sectionLabel }}</span>
                        @endif
                        @foreach($scoreBadges as $badge)
                            <span class="community-post-card__badge community-post-card__badge--score {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        @endforeach
                        @foreach($promotionLabels as $promotionLabel)
                            <span class="community-post-card__badge community-post-card__badge--promotion">{{ $promotionLabel }}</span>
                        @endforeach
                        @if ($reportTrustScore !== null)
                            <span class="community-post-card__badge community-post-card__badge--trust-score">Trust {{ $reportTrustScore }}%</span>
                        @endif
                        @if ($reportStatus)
                            <span class="community-post-card__badge community-post-card__badge--report-status">{{ $reportStatus }}</span>
                        @endif
                        @if ($categoryLabel)
                            <span class="community-post-card__badge">{{ $categoryLabel }}</span>
                        @endif
                    </div>
                @endif
                @if ($post->hasVideo())
                    <span class="community-post-card__video-badge" title="Includes video">
                        <i class="fa-solid fa-circle-play"></i>
                    </span>
                @endif
                @if ($hasPoetryAudio)
                    <span class="community-post-card__video-badge community-post-card__audio-badge" title="Includes audio recitation">
                        <i class="fa-solid fa-volume-high"></i>
                    </span>
                @endif
            </a>

            <div class="community-post-card__body">
                @if($isPoetry && ($poetryType || $poetryThemes !== [] || $poetryRating))
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @if($poetryType)
                            <span class="community-post-card__tag">{{ $poetryType }}</span>
                        @endif
                        @foreach($poetryThemes as $theme)
                            <span class="community-post-card__tag">{{ $theme }}</span>
                        @endforeach
                        @if($poetryRating)
                            <span class="community-post-card__tag community-post-card__tag--rating">
                                <i class="fa-solid fa-star" aria-hidden="true"></i>
                                {{ number_format($poetryRating, 1) }}
                            </span>
                        @endif
                    </div>
                @endif
                @if($isBusiness && ($businessContentType || $businessStage || $businessThemes !== []))
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @if($businessContentType)
                            <span class="community-post-card__tag community-post-card__tag--emphasis">{{ $businessContentType }}</span>
                        @endif
                        @if($businessStage)
                            <span class="community-post-card__tag">{{ $businessStage }}</span>
                        @endif
                        @foreach($businessThemes as $theme)
                            <span class="community-post-card__tag">{{ $theme }}</span>
                        @endforeach
                    </div>
                @endif
                @if($isMyArea && ($myAreaActivity || $myAreaStatus || $myAreaImpact))
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @if($myAreaActivity)
                            <span class="community-post-card__tag community-post-card__tag--solid">{{ $myAreaActivity }}</span>
                        @endif
                        @if($myAreaStatus)
                            <span class="community-post-card__tag">{{ $myAreaStatus }}</span>
                        @endif
                        @if($myAreaImpact)
                            <span class="community-post-card__tag community-post-card__tag--emphasis">{{ $myAreaImpact }} impact</span>
                        @endif
                    </div>
                @endif
                <h2 class="community-post-card__title">
                    <a href="{{ route('community.show', $post) }}">{{ $post->title }}</a>
                </h2>

                @if ($excerpt)
                    <p class="community-post-card__excerpt">{{ $excerpt }}</p>
                @endif

                @if (filled($locationLabel) || filled($myAreaLocation))
                    <p class="community-post-card__location">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        {{ \Illuminate\Support\Str::limit(filled($myAreaLocation) ? $myAreaLocation : $locationLabel, 60) }}
                    </p>
                @endif

                <div class="community-post-card__footer">
                    <div class="community-post-card__author">
                        @include('community.partials.author-avatar', [
                            'avatarUrl' => $authorAvatarUrl,
                            'initials' => $authorInitials ?: 'CA',
                            'alt' => $authorDisplayName,
                            'sizeClass' => 'community-post-card__avatar',
                        ])
                        <div class="community-post-card__author-meta">
                            @if ($post->showsAuthorProfileLink())
                                <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}" class="community-post-card__author-name">{{ $authorDisplayName }}</a>
                            @else
                                <span class="community-post-card__author-name">{{ $authorDisplayName }}</span>
                            @endif
                            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('M d, Y') ?? 'Draft' }}</time>
                        </div>
                    </div>

                    <div class="community-post-card__stats">
                        @auth
                            <button type="button"
                                class="community-post-card__save js-community-save-post {{ $isSaved ? 'is-saved' : '' }}"
                                data-url="{{ route('community.save.toggle', $post) }}"
                                data-title-saved="Saved"
                                data-title-unsaved="Save post"
                                title="{{ $isSaved ? 'Saved' : 'Save post' }}">
                                <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark" aria-hidden="true"></i>
                            </button>
                        @endauth
                        @if ($post->allowsSharing())
                            @include('community.partials.share-panel', [
                                'post' => $post,
                                'showCardTrigger' => true,
                            ])
                        @endif
                        @if (($post->reactions_count ?? 0) > 0)
                            <span title="Reactions"><i class="fa-solid fa-heart" aria-hidden="true"></i> {{ $post->reactions_count }}</span>
                        @endif
                        @if (($post->comments_count ?? 0) > 0)
                            <span title="Comments"><i class="fa-solid fa-comment" aria-hidden="true"></i> {{ $post->comments_count }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    </div>
@empty
    <div class="col-12">
        <div class="community-empty-state">
            <div class="community-empty-state__icon" aria-hidden="true">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <h3 class="community-empty-state__title">No posts yet</h3>
                <p class="community-empty-state__text mb-0">
                    {{ $emptyMessage ?? 'No posts found for this section yet. Try another category or be the first to publish.' }}
                </p>
            </div>
        </div>
    </div>
@endforelse
