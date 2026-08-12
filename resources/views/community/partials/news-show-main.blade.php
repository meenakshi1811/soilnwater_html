@php
    $galleryUrls = $post->featuredImageUrls();
    $coverUrl = $post->featuredImageUrl();
    $thumbUrls = $coverUrl
        ? array_values(array_filter($galleryUrls, fn ($url) => $url !== $coverUrl))
        : array_slice($galleryUrls, 1);
    $metaItems = \App\Support\CommunityPostMetaChips::forDisplay($post);
    $narrativeKeys = \App\Support\CommunityPostFormFields::narrativeNewsMetaKeys();
    $narrativeLabels = \App\Support\CommunityPostFormFields::newsDetailMetaOrder();
    $highlights = collect($narrativeKeys)
        ->mapWithKeys(fn (string $key): array => [$key => data_get($post->meta, $key)])
        ->filter(fn ($value) => filled($value))
        ->take(5);
    $quoteText = data_get($post->meta, 'news_why_important');
    $quoteAttribution = data_get($post->meta, 'quote_attribution') ?: data_get($post->meta, 'news_related_authority');
    $tags = collect($post->tags ?? [])->filter()->values();
    $shareUrl = $post->shareUrl();
    $editorLanguage = data_get($post->meta, 'editor_language', 'en');
    $editorHtmlLang = \App\Support\CommunityContentTaxonomy::editorLanguageHtmlLang($editorLanguage);
@endphp

<div class="community-article-shell mb-3">
    <div class="community-article-shell__top">
        @if($metaItems !== [])
            <div class="community-article-meta" aria-label="Post details">
                @foreach($metaItems as $item)
                    <span class="community-article-meta__chip" title="{{ $item['title'] }}">
                        <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $item['value'] }}</span>
                    </span>
                @endforeach
            </div>
        @endif

        <div class="community-article-font-size" data-article-font-controls role="group" aria-label="Text size">
            <i class="fa-solid fa-text-height community-article-font-size__icon" aria-hidden="true"></i>
            <button type="button" class="community-article-font-size__btn" data-article-font-action="decrease" aria-label="Decrease text size" title="Decrease text size">A−</button>
            <button type="button" class="community-article-font-size__btn is-active" data-article-font-action="reset" aria-label="Default text size" title="Default text size" aria-pressed="true">A</button>
            <button type="button" class="community-article-font-size__btn" data-article-font-action="increase" aria-label="Increase text size" title="Increase text size">A+</button>
        </div>
    </div>

    @if($thumbUrls !== [])
        <div class="community-featured-gallery community-featured-gallery--article {{ count($thumbUrls) === 1 ? 'community-featured-gallery--single' : '' }} px-2 pt-2">
            @foreach(array_slice($thumbUrls, 0, 4) as $index => $imageUrl)
                <div class="community-featured-gallery-item">
                    <img src="{{ $imageUrl }}" alt="{{ $post->title }} — image {{ $index + 1 }}" class="img-fluid rounded community-article-cover">
                </div>
            @endforeach
        </div>
    @endif

    <div class="community-article-reading">
        @if($post->hasVideo())
            <div class="community-post-video mb-4">
                @if($post->youtubeEmbedUrl())
                    <div class="ratio ratio-16x9 rounded overflow-hidden">
                        <iframe src="{{ $post->youtubeEmbedUrl() }}" title="Video for {{ $post->title }}" allowfullscreen></iframe>
                    </div>
                @elseif($post->videoFileUrl())
                    <video controls class="w-100 rounded" preload="metadata">
                        <source src="{{ $post->videoFileUrl() }}">
                    </video>
                @endif
            </div>
        @endif

        <div class="community-post-body community-post-body--article community-post-body--scalable"
            data-community-font-target
            data-community-body-protected
            lang="{{ $editorHtmlLang }}"
            @if($editorLanguage === 'ur') dir="rtl" @endif>{!! $post->body !!}</div>

        @if($highlights->isNotEmpty())
            <div class="news-detail-highlights">
                <h4>Key Highlights</h4>
                <ul>
                    @foreach($highlights as $key => $value)
                        <li><strong>{{ $narrativeLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}:</strong> {{ \App\Support\CommunityPostFormFields::formatNewsMetaValue($key, $value) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(filled($quoteText))
            <div class="news-detail-quote">
                <blockquote>"{{ $quoteText }}"</blockquote>
                @if(filled($quoteAttribution))
                    <cite>— {{ $quoteAttribution }}</cite>
                @endif
            </div>
        @endif

        @if($tags->isNotEmpty())
            <div class="news-detail-tags" aria-label="Post tags">
                <ul class="news-detail-tags__list">
                    @foreach($tags as $tag)
                        @php
                            $normalizedTag = \App\Models\CommunityTopicFollow::normalizeTopic((string) $tag);
                            $isFollowingTopic = auth()->check() && in_array($normalizedTag, $followedTopics ?? [], true);
                        @endphp
                        <li class="news-detail-tags__item">
                            <span class="news-detail-tags__chip" title="{{ $tag }}">
                                <i class="fa-solid fa-hashtag" aria-hidden="true"></i>
                                <span class="news-detail-tags__label">{{ \Illuminate\Support\Str::limit($tag, 42) }}</span>
                            </span>
                            @auth
                                @if($post->isPubliclyVisible())
                                    <button type="button"
                                        class="btn btn-sm news-detail-tags__follow {{ $isFollowingTopic ? 'btn-success' : 'btn-outline-success' }} js-community-follow-topic {{ $isFollowingTopic ? 'is-following' : '' }}"
                                        data-url="{{ route('community.subscriptions.topic.toggle') }}"
                                        data-topic="{{ $tag }}">
                                        {{ $isFollowingTopic ? 'Following' : 'Follow' }}
                                    </button>
                                @endif
                            @endauth
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($post->allowsSharing())
            <div class="news-detail-share">
                <span class="news-detail-share__label">Share:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="news-detail-share__btn"><i class="fa-brands fa-facebook-f"></i> Facebook</a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="news-detail-share__btn"><i class="fa-brands fa-x-twitter"></i> X</a>
                <a href="https://wa.me/?text={{ urlencode($post->title.' '.$shareUrl) }}" target="_blank" rel="noopener" class="news-detail-share__btn"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="news-detail-share__btn"><i class="fa-brands fa-linkedin-in"></i> LinkedIn</a>
                <button type="button" class="news-detail-share__btn border-0" data-bs-toggle="modal" data-bs-target="#communityShareModal"><i class="fa-solid fa-link"></i> Copy link</button>
                @auth
                    @if($post->isPubliclyVisible())
                        <button type="button"
                            class="news-detail-share__btn border-0 js-community-save-post {{ $isSaved ? 'is-saved' : '' }}"
                            data-url="{{ route('community.save.toggle', $post) }}"
                            data-label-saved="Saved"
                            data-label-unsaved="Save">
                            <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark"></i> {{ $isSaved ? 'Saved' : 'Save' }}
                        </button>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</div>
