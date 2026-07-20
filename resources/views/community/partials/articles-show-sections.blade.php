@if($post->content_type === 'articles')
    @php
        $articleType = data_get($post->meta, 'article_type');
        $articleLanguage = data_get($post->meta, 'editor_language', 'en');
        $languageLabels = [
            'en' => 'English',
            'hi' => 'Hindi',
            'ur' => 'Urdu',
            'pa' => 'Punjabi',
            'bn' => 'Bengali',
            'mr' => 'Marathi',
            'gu' => 'Gujarati',
            'ta' => 'Tamil',
            'te' => 'Telugu',
        ];
        $wordCount = str_word_count(trim(strip_tags((string) $post->body)));
        $readingMinutes = max(1, (int) ceil($wordCount / 220));
        $metaItems = array_values(array_filter([
            filled($articleType) ? ['label' => 'Article type', 'value' => $articleType] : null,
            filled($post->category) ? ['label' => 'Category', 'value' => $post->category] : null,
            ['label' => 'Reading time', 'value' => $readingMinutes . ' min read'],
            filled($post->published_at) ? ['label' => 'Published', 'value' => $post->published_at->format('M j, Y')] : null,
            isset($languageLabels[$articleLanguage]) ? ['label' => 'Language', 'value' => $languageLabels[$articleLanguage]] : null,
        ]));
        $scoreBadges = $post->articleScoreBadges();
        $tags = collect($post->tags ?? [])->filter()->values();
        $authorName = $post->authorDisplayName();
        $authorBio = data_get($post->meta, 'author_bio');
        $authorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authorName, 0, 1));
    @endphp

    <div class="community-article-shell mb-4">
        @if($metaItems !== [])
            <div class="community-article-meta">
                @foreach(array_slice($metaItems, 0, 4) as $item)
                    <div class="community-article-meta__item">
                        <span class="community-article-meta__label">{{ $item['label'] }}</span>
                        <span class="community-article-meta__value">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @php
            $galleryUrls = $post->featuredImageUrls();
            $coverUrl = $post->featuredImageUrl();
            // Cover is used in the hero; only show remaining gallery images in-body.
            $inlineGallery = $coverUrl
                ? array_values(array_filter($galleryUrls, fn ($url) => $url !== $coverUrl))
                : $galleryUrls;
        @endphp

        @if($inlineGallery !== [])
            <div class="community-featured-gallery community-featured-gallery--article {{ count($inlineGallery) === 1 ? 'community-featured-gallery--single' : '' }} px-3 pt-3">
                @foreach($inlineGallery as $index => $imageUrl)
                    <div class="community-featured-gallery-item">
                        <img src="{{ $imageUrl }}" alt="{{ $post->title }} — image {{ $index + 1 }}" class="img-fluid rounded community-article-cover">
                    </div>
                @endforeach
            </div>
        @endif

        <div class="community-article-reading">
            @if($scoreBadges !== [] || $post->adminPromotionLabels() !== [])
                <div class="community-article-score-row">
                    @foreach($scoreBadges as $badge)
                        <span class="badge bg-light text-dark community-score-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                    @endforeach
                    @foreach($post->adminPromotionLabels() as $promotionLabel)
                        <span class="badge bg-warning text-dark">{{ $promotionLabel }}</span>
                    @endforeach
                </div>
            @endif

            @if($post->hasVideo())
                <div class="community-post-video mb-4">
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden">
                            <iframe
                                src="{{ $post->youtubeEmbedUrl() }}"
                                title="Video for {{ $post->title }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded" preload="metadata">
                            <source src="{{ $post->videoFileUrl() }}">
                            Your browser does not support embedded video playback.
                        </video>
                    @endif
                </div>
            @endif

            @php
                $editorLanguage = data_get($post->meta, 'editor_language', 'en');
            @endphp
            <div
                class="community-post-body community-post-body--article"
                data-community-body-protected
                lang="{{ $editorLanguage }}"
                @if($editorLanguage === 'ur') dir="rtl" @endif
            >{!! $post->body !!}</div>

            @if($tags->isNotEmpty())
                <div class="community-article-tags" aria-label="Article tags">
                    @foreach($tags as $tag)
                        <span class="community-article-tag">#{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            <div class="community-article-author-card">
                @if(filled($post->authorAvatarUrl()))
                    <img src="{{ $post->authorAvatarUrl() }}" alt="" class="community-article-author-card__avatar">
                @else
                    <span class="community-article-author-card__initials" aria-hidden="true">{{ $authorInitial }}</span>
                @endif
                <div>
                    <span class="community-article-author-card__label">Written by</span>
                    <p class="community-article-author-card__name">
                        @if($post->showsAuthorProfileLink())
                            <a href="{{ route('community.authors.show', $post->user->authorUniqueName()) }}">{{ $authorName }}</a>
                        @else
                            {{ $authorName }}
                        @endif
                    </p>
                    @if(filled($authorBio))
                        <p class="community-article-author-card__bio">{{ \Illuminate\Support\Str::limit($authorBio, 140) }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
