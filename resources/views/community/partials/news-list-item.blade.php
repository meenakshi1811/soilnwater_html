@php
    $portalType = $portalType ?? 'news';
    $itemPortalType = $post->content_type ?? $portalType;
    $portalCopy = \App\Support\CommunityContentTaxonomy::portalCopy($itemPortalType);
    $types = \App\Support\CommunityContentTaxonomy::formTypes();
    $portalLabel = $types[$itemPortalType]['label'] ?? 'News';
    $engagement = $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []];
    $authorDisplayName = $post->authorDisplayName();
    $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 180);
    $categoryLabel = filled($post->category) ? $post->category : $portalLabel;
    $isSaved = auth()->check() && in_array($post->id, $engagement['saved_post_ids'] ?? [], true);
    $viewsLabel = $post->views_count >= 1000
        ? number_format($post->views_count / 1000, 1).'K'
        : number_format((int) $post->views_count);
    $publishedLabel = $post->published_at?->diffForHumans() ?? 'Recently';
    $categorySlug = \Illuminate\Support\Str::slug($categoryLabel);
@endphp
<article class="community-news-list-item">
    <a href="{{ route('community.show', $post) }}" class="community-news-list-item__thumb-link">
        @if ($post->featuredImageUrl())
            <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="community-news-list-item__thumb" loading="lazy">
        @else
            <div class="community-news-list-item__thumb community-news-list-item__thumb--placeholder" aria-hidden="true">
                <i class="fa-solid {{ $portalCopy['featured_icon'] }}"></i>
            </div>
        @endif
    </a>
    <div class="community-news-list-item__body">
        <div class="community-news-list-item__top">
            <span class="community-news-list-item__category community-news-list-item__category--{{ $categorySlug }}">{{ strtoupper($categoryLabel) }}</span>
            <div class="community-news-list-item__actions">
                @auth
                    <button type="button"
                        class="community-news-list-item__icon-btn js-community-save-post {{ $isSaved ? 'is-saved' : '' }}"
                        data-url="{{ route('community.save.toggle', $post) }}"
                        title="{{ $isSaved ? 'Saved' : 'Save post' }}">
                        <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark"></i>
                    </button>
                @endauth
                @if ($post->allowsSharing())
                    @include('community.partials.share-panel', [
                        'post' => $post,
                        'showTrigger' => true,
                        'iconOnly' => true,
                    ])
                @endif
            </div>
        </div>
        <h3 class="community-news-list-item__title">
            <a href="{{ route('community.show', $post) }}">{{ $post->title }}</a>
        </h3>
        @if ($excerpt)
            <p class="community-news-list-item__excerpt">{{ $excerpt }}</p>
        @endif
        <div class="community-news-list-item__meta">
            <span><i class="fa-regular fa-user me-1"></i>{{ $authorDisplayName }}</span>
            <span><i class="fa-regular fa-clock me-1"></i>{{ $publishedLabel }}</span>
            <span><i class="fa-regular fa-eye me-1"></i>{{ $viewsLabel }} Views</span>
        </div>
    </div>
</article>
