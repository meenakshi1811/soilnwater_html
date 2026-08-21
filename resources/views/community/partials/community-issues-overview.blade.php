@php
    $post = $post ?? null;
    if (! $post?->isCommunityIssuesPost()) {
        return;
    }

    $issueCategory = data_get($post->meta, 'community_issue_category') ?: $post->category;
    $issueType = data_get($post->meta, 'community_issue_type');
    $severity = data_get($post->meta, 'community_issue_severity');
    $statusTracker = data_get($post->meta, 'community_issue_status_tracker');
    $publishAsLabel = \App\Support\CommunityContentTaxonomy::communityIssuePublishAsOptions()[$post->resolvedPublishAs()]
        ?? $post->publishAsLabel();
    $severityTone = match ($severity) {
        'Emergency', 'Critical' => 'emergency',
        'High' => 'high',
        'Medium' => 'medium',
        default => 'low',
    };
    $sidebarLayout = $sidebarLayout ?? false;
    $hasChips = filled($issueCategory) || filled($issueType) || filled($severity) || filled($statusTracker)
        || (filled($publishAsLabel) && $post->resolvedPublishAs() !== 'public_profile');
@endphp

@if($hasChips)
    @if($sidebarLayout)
        <div class="community-news-sidebar__card community-news-sidebar__card--community-issues-overview">
            <p class="community-news-sidebar__label">Issue overview</p>
            <div class="ci-show-overview__chips ci-show-overview__chips--sidebar">
                @if(filled($issueCategory))
                    <span class="ci-show-chip">{{ $issueCategory }}</span>
                @endif
                @if(filled($issueType))
                    <span class="ci-show-chip">{{ $issueType }}</span>
                @endif
                @if(filled($severity))
                    <span class="ci-show-chip ci-show-chip--severity-{{ $severityTone }}">{{ $severity }}</span>
                @endif
                @if(filled($statusTracker))
                    <span class="ci-show-chip">{{ $statusTracker }}</span>
                @endif
                @if(filled($publishAsLabel) && $post->resolvedPublishAs() !== 'public_profile')
                    <span class="ci-show-chip">{{ $publishAsLabel }}</span>
                @endif
            </div>
        </div>
    @else
        <div class="ci-show-overview">
            <div class="ci-show-overview__kicker">Community Issues · Civic reporting</div>
            <div class="ci-show-overview__title">Structured civic issue with location, evidence, and resolution tracking</div>
            <div class="ci-show-overview__chips">
                @if(filled($issueCategory))
                    <span class="ci-show-chip">{{ $issueCategory }}</span>
                @endif
                @if(filled($issueType))
                    <span class="ci-show-chip">{{ $issueType }}</span>
                @endif
                @if(filled($severity))
                    <span class="ci-show-chip ci-show-chip--severity-{{ $severityTone }}">{{ $severity }} severity</span>
                @endif
                @if(filled($statusTracker))
                    <span class="ci-show-chip">{{ $statusTracker }}</span>
                @endif
                @if(filled($publishAsLabel) && $post->resolvedPublishAs() !== 'public_profile')
                    <span class="ci-show-chip">{{ $publishAsLabel }}</span>
                @endif
            </div>
        </div>
    @endif
@endif
