@forelse ($posts as $post)
    @php
        $engagement = $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
        $authorDisplayName = $post->authorDisplayName();
        $authorInitials = $post->authorInitials();
        $authorAvatarUrl = $post->authorAvatarUrl();
        $reportStatus = $post->reportStatus();
        $reportTrustScore = $post->isReportContent() ? $post->reportTrustScore() : null;
        $categoryLabel = filled(data_get($post->meta, 'report_type'))
            ? data_get($post->meta, 'report_type', $post->category)
            : $post->category;
        $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 160);
        $locationLabel = $post->location ?? data_get($post->meta, 'location');
        $promotionLabels = $post->adminPromotionLabels();
        $scoreBadges = $post->articleScoreBadges();
        $isSaved = auth()->check() && in_array($post->id, $engagement['saved_post_ids'] ?? [], true);
    @endphp
    <div class="col">
        <article class="community-post-card h-100 {{ $post->is_highlighted ? 'community-post-card--highlighted' : '' }}">
            <a href="{{ route('community.show', $post) }}" class="community-post-card__media-link" aria-label="Read {{ $post->title }}">
                @if ($post->featuredImageUrl())
                    <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="community-post-card__image" loading="lazy">
                @else
                    <div class="community-post-card__placeholder" aria-hidden="true">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                @endif
                <div class="community-post-card__media-overlay"></div>
                @if ($categoryLabel || $reportStatus || $reportTrustScore !== null || $promotionLabels !== [] || $scoreBadges !== [])
                    <div class="community-post-card__badges">
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
            </a>

            <div class="community-post-card__body">
                <h2 class="community-post-card__title">
                    <a href="{{ route('community.show', $post) }}">{{ $post->title }}</a>
                </h2>

                @if ($excerpt)
                    <p class="community-post-card__excerpt">{{ $excerpt }}</p>
                @endif

                @if (filled($locationLabel))
                    <p class="community-post-card__location">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        {{ \Illuminate\Support\Str::limit($locationLabel, 60) }}
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
