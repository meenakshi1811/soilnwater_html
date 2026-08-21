@php
    $post = $post ?? null;
    if (! $post?->isCommunityIssuesPost()) {
        return;
    }

    $railLayout = $railLayout ?? false;
    $supportRequests = array_values(array_filter((array) data_get($post->meta, 'community_issue_support_requests', [])));
@endphp

@if($supportRequests !== [])
    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-handshake"></i></span>
                <div>
                    <h4 class="community-detail-card__title">{{ $heading ?? 'Community support request' }}</h4>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($supportRequests as $request)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $request }}</span>
                @endforeach
            </div>
        </div>
    @else
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-handshake text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">{{ $heading ?? 'Community support request' }}</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($supportRequests as $request)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $request }}</span>
                @endforeach
            </div>
        </div>
    @endif
@endif
