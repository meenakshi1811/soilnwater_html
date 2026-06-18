@php
    $orderedStoryMeta = \App\Support\CommunityPostFormFields::orderedStoryMetaForDisplay($post);
    $storyMetaLabels = \App\Support\CommunityPostFormFields::storyDetailMetaOrder();
    $storyGallery = collect(data_get($post->meta, 'story_gallery', []))
        ->filter(fn ($image) => filled(data_get($image, 'url')))
        ->values();
    $hasContent = $orderedStoryMeta->isNotEmpty() || $storyGallery->isNotEmpty();
@endphp

@if($hasContent)
    <div class="about-box mt-4">
        <h4>{{ $heading ?? 'Story details' }}</h4>

        @if($orderedStoryMeta->isNotEmpty())
            <div class="row g-3 {{ $storyGallery->isNotEmpty() ? 'mb-3' : '' }}">
                @foreach($orderedStoryMeta as $key => $value)
                    <div class="{{ in_array($key, ['story_main_characters', 'story_target_audience', 'story_themes'], true) ? 'col-12' : 'col-md-6' }}">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong class="d-block mb-1">{{ $storyMetaLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</strong>
                            @if(in_array($key, ['story_target_audience', 'story_themes'], true))
                                <div class="story-meta-pills">
                                    @foreach(array_filter(array_map('trim', explode(',', (string) $value))) as $item)
                                        <span class="badge bg-white text-dark border story-meta-pill {{ $key === 'story_target_audience' ? 'story-meta-pill--audience' : 'story-meta-pill--theme' }}">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($storyGallery->isNotEmpty())
            <h5 class="h6 mb-3">Story gallery</h5>
            <div class="story-gallery-grid">
                @foreach($storyGallery as $index => $image)
                    <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="story-gallery-grid__item">
                        <img src="{{ data_get($image, 'url') }}" alt="{{ $post->title }} — gallery image {{ $index + 1 }}" loading="lazy">
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endif
