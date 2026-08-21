@php
    $post = $post ?? null;
    if (! $post?->isCommunityIssuesPost()) {
        return;
    }

    $statusTracker = data_get($post->meta, 'community_issue_status_tracker');
    $resolutionTimeline = $post->communityIssueResolutionTimelineEntries();
    $statusSteps = \App\Support\CommunityContentTaxonomy::communityIssueStatusSteps();
    $currentStatusIndex = filled($statusTracker) ? array_search($statusTracker, $statusSteps, true) : false;
@endphp

<div class="community-news-sidebar__community-issues-extras" aria-label="Community issue sidebar details">
    @include('community.partials.community-issues-overview', [
        'post' => $post,
        'sidebarLayout' => true,
    ])

    @include('community.partials.community-issues-meta-details', [
        'post' => $post,
        'sidebarLayout' => true,
        'splitStatusSection' => true,
    ])

    @if(filled($statusTracker) || $resolutionTimeline !== [])
        <div class="community-news-sidebar__card community-news-sidebar__card--community-issues-status">
            <p class="community-news-sidebar__label">Status tracking</p>
            @if(filled($statusTracker))
                <div class="ci-status-stepper ci-status-stepper--sidebar" aria-label="Issue status steps">
                    @foreach($statusSteps as $index => $step)
                        @php
                            $isCurrent = $step === $statusTracker;
                            $isComplete = $currentStatusIndex !== false && $index < $currentStatusIndex;
                        @endphp
                        <div class="ci-status-step {{ $isCurrent ? 'is-current' : ($isComplete ? 'is-complete' : '') }}">
                            <span class="ci-status-step__label">{{ $step }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            @if($resolutionTimeline !== [])
                <div class="community-issues-sidebar-timeline">
                    <div class="community-issues-sidebar-timeline__label">Resolution tracker</div>
                    <ul class="list-unstyled mb-0">
                        @foreach($resolutionTimeline as $entry)
                            <li class="community-issues-sidebar-timeline__item">
                                <i class="fa-solid fa-circle-dot" aria-hidden="true"></i>
                                <span>{{ $entry }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</div>
