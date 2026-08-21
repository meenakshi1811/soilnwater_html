@php
    $post = $post ?? null;
    if (! $post?->isCommunityIssuesPost()) {
        return;
    }

    $railLayout = $railLayout ?? false;
    $solution = data_get($post->meta, 'community_issue_suggested_solution');
@endphp

@if(filled($solution))
    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-lightbulb"></i></span>
                <div>
                    <h4 class="community-detail-card__title">{{ $heading ?? 'Suggested solution' }}</h4>
                </div>
            </div>
            <p class="mb-0">{!! nl2br(e($solution)) !!}</p>
        </div>
    @else
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-lightbulb text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">{{ $heading ?? 'Suggested solution' }}</h4>
            </div>
            <p class="mb-0">{!! nl2br(e($solution)) !!}</p>
        </div>
    @endif
@endif
