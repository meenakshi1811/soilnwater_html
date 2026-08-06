@php
    $metaItems = \App\Support\CommunityPostMetaChips::forDisplay($post);
    $galleryUrls = $post->featuredImageUrls();
    $coverUrl = $post->featuredImageUrl();
    $inlineGallery = $coverUrl
        ? array_values(array_filter($galleryUrls, fn ($url) => $url !== $coverUrl))
        : $galleryUrls;
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

    @if($inlineGallery !== [])
        <div class="community-featured-gallery community-featured-gallery--article {{ count($inlineGallery) === 1 ? 'community-featured-gallery--single' : '' }} px-2 pt-2">
            @foreach($inlineGallery as $index => $imageUrl)
                <div class="community-featured-gallery-item">
                    <img src="{{ $imageUrl }}" alt="{{ $post->title }} — image {{ $index + 1 }}" class="img-fluid rounded community-article-cover">
                </div>
            @endforeach
        </div>
    @endif

    <div class="community-article-reading">
