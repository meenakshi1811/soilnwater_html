@php
    $post = $post ?? null;
    if (! $post?->isCreativeCornerPost()) {
        return;
    }

    $postType = $post->creativeCornerPostTypeLabel();
    $category = $post->creativeCornerCategoryLabel();
    $creationType = data_get($post->meta, 'creative_corner_creation_type');
    $difficulty = data_get($post->meta, 'creative_corner_difficulty_level');
    $overviewFields = collect([
        'Post type' => $postType,
        'Category' => $category,
        'Creation type' => $creationType,
        'Difficulty' => $difficulty,
    ])->filter(fn (mixed $value): bool => filled($value));
@endphp

@if($overviewFields->isNotEmpty())
    <div class="community-news-sidebar__card community-news-sidebar__card--creative-corner-overview">
        <p class="community-news-sidebar__label">Creative overview</p>
        <div class="news-sidebar-meta-grid">
            @foreach($overviewFields as $label => $value)
                <div class="news-sidebar-meta-grid__item">
                    <div class="border rounded p-3 h-100 bg-light">
                        <strong class="d-block mb-1">{{ $label }}</strong>
                        <span>{{ $value }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
