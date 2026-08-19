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
    $editorLanguage = data_get($post->meta, 'editor_language', 'en');
    $editorHtmlLang = \App\Support\CommunityContentTaxonomy::editorLanguageHtmlLang($editorLanguage);
    $usesBookReader = $post->usesBookLayout() && $post->bookPages() !== [];
    $isPoetryPost = $post->content_type === 'poetry';
    $isChildrensPoemPost = $post->isChildrensCornerPost() && $post->childrensCornerContentMode() === 'poem';
    $usesPoetryBodyLayout = $isPoetryPost || $isChildrensPoemPost;
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

        @if($usesBookReader)
            @php $bookPageCount = count($post->bookPages()); @endphp
            @if($bookPageCount > 0)
                <div class="news-detail-highlights mb-4">
                    <h4>{{ $post->usesChapterLayoutForDisplay() ? 'Chapters to read' : 'Pages to read' }}</h4>
                    <p class="mb-0">Turn through {{ $bookPageCount }} {{ $post->usesChapterLayoutForDisplay() ? 'chapter' : 'page' }}{{ $bookPageCount === 1 ? '' : 's' }} below.</p>
                </div>
            @endif
            @include('community.partials.book-reader', ['post' => $post])
        @elseif($usesPoetryBodyLayout)
            <div class="poetry-reading-card mb-4">
                <div class="poetry-reading-card__kicker">Poem</div>
                <div class="community-post-body community-post-body--article community-post-body--poetry community-post-body--scalable"
                    data-community-font-target
                    data-community-body-protected
                    lang="{{ $editorHtmlLang }}"
                    @if($editorLanguage === 'ur') dir="rtl" @endif>{!! $post->body !!}</div>
            </div>
        @else
        <div class="community-post-body community-post-body--article community-post-body--scalable"
            data-community-font-target
            data-community-body-protected
            lang="{{ $editorHtmlLang }}"
            @if($editorLanguage === 'ur') dir="rtl" @endif>{!! $post->body !!}</div>
        @endif

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
    </div>
</div>
